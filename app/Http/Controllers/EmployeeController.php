<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private FileService $files
    ) {
    }

    public function index(Request $request): View
    {
        $trashed = $request->boolean('trashed');

        $employees = ($trashed ? Employee::onlyTrashed() : Employee::query())
            ->search($request->input('q'))
            ->withCount('meals')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'trashed' => $trashed,
        ]);
    }

    public function create(): View
    {
        return view('employees.create', ['employee' => new Employee()]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::create([
            'emp_no' => $request->input('emp_no') ?: Employee::generateEmpNo(),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'birth_date' => $request->input('birth_date'),
            'birth_place' => $request->input('birth_place'),
            'hire_date' => $request->input('hire_date'),
            'rank' => $request->input('rank'),
            'notes' => $request->input('notes'),
        ]);

        if ($request->hasFile('photo')) {
            $this->replacePhoto($employee, $request);
        }

        $this->audit->log('employee.created', $employee, 'إضافة موظف: '.$employee->full_name, null, $employee->only(['emp_no', 'last_name', 'first_name', 'rank']));

        return redirect()->route('employees.show', $employee)->with('success', 'تم إضافة الموظف بنجاح.');
    }

    public function show(Employee $employee): View
    {
        $employee->loadCount('meals');
        $meals = $employee->meals()->latest('day')->limit(20)->get();

        return view('employees.show', compact('employee', 'meals'));
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', ['employee' => $employee]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $old = $employee->only(['emp_no', 'last_name', 'first_name', 'rank']);

        $employee->fill([
            'emp_no' => $request->input('emp_no') ?: $employee->emp_no,
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'birth_date' => $request->input('birth_date'),
            'birth_place' => $request->input('birth_place'),
            'hire_date' => $request->input('hire_date'),
            'rank' => $request->input('rank'),
            'notes' => $request->input('notes'),
        ])->save();

        if ($request->hasFile('photo')) {
            $this->replacePhoto($employee, $request);
        }

        $this->audit->log('employee.updated', $employee, 'تعديل موظف: '.$employee->full_name, $old, $employee->only(['emp_no', 'last_name', 'first_name', 'rank']));

        return redirect()->route('employees.show', $employee)->with('success', 'تم تحديث بيانات الموظف.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        $this->audit->log('employee.deleted', $employee, 'حذف ناعم للموظف: '.$employee->full_name);

        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف (حذف ناعم).');
    }

    public function restore(string $employee): RedirectResponse
    {
        $employee = Employee::onlyTrashed()->findOrFail($employee);
        $employee->restore();

        $this->audit->log('employee.restored', $employee, 'استعادة الموظف: '.$employee->full_name);

        return back()->with('success', 'تم استعادة الموظف.');
    }

    public function photo(Employee $employee): StreamedResponse|RedirectResponse
    {
        $response = $this->files->response($employee->photo);

        return $response ?? redirect()->route('employees.show', $employee);
    }

    private function replacePhoto(Employee $employee, EmployeeRequest $request): void
    {
        $old = $employee->photo;

        $path = $this->files->store(
            $request->file('photo'),
            'employees/photos',
            $employee->emp_no ?: 'employee-'.$employee->id,
            FileService::IMAGE_MIMES
        );

        $employee->forceFill(['photo' => $path])->save();

        if ($old && $old !== $path) {
            $this->files->delete($old);
        }
    }
}
