<?php

namespace App\Http\Controllers;

use App\Http\Requests\TraineeRequest;
use App\Models\Document;
use App\Models\Trainee;
use App\Services\AuditService;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TraineeController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private FileService $files
    ) {
    }

    public function index(Request $request): View
    {
        $trashed = $request->boolean('trashed');

        $query = $trashed ? Trainee::onlyTrashed() : Trainee::query();

        $trainees = $query
            ->search($request->input('q'))
            ->filter($request->only([
                'specialty', 'diploma', 'level', 'gender', 'training_mode',
                'start_from', 'start_to', 'end_from', 'end_to', 'status',
            ]))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('trainees.index', [
            'trainees' => $trainees,
            'trashed' => $trashed,
            'specialties' => Trainee::query()->whereNotNull('specialty')->where('specialty', '!=', '')
                ->distinct()->orderBy('specialty')->pluck('specialty'),
            'diplomas' => Trainee::query()->whereNotNull('diploma')->where('diploma', '!=', '')
                ->distinct()->orderBy('diploma')->pluck('diploma'),
            'levels' => Trainee::query()->whereNotNull('level')->where('level', '!=', '')
                ->distinct()->orderBy('level')->pluck('level'),
        ]);
    }

    public function create(): View
    {
        return view('trainees.create', ['trainee' => new Trainee()]);
    }

    public function store(TraineeRequest $request): RedirectResponse
    {
        $data = $this->payload($request);
        $data['registered_at'] = $request->input('registered_at') ?: now();

        $trainee = Trainee::create($data);

        if ($request->hasFile('photo')) {
            $this->replacePhoto($trainee, $request);
        }

        $this->audit->log('trainee.created', $trainee, 'إضافة متربص: '.$trainee->full_name, null, $trainee->only(['reg_no', 'electronic_reg_no', 'last_name', 'first_name']));

        return redirect()->route('trainees.show', $trainee)->with('success', 'تم إضافة المتربص بنجاح.');
    }

    public function show(Trainee $trainee): View
    {
        $trainee->load(['documents' => fn ($q) => $q->latest(), 'payments' => fn ($q) => $q->latest(), 'meals' => fn ($q) => $q->latest()->limit(10)]);

        return view('trainees.show', [
            'trainee' => $trainee,
            'documentTypes' => Document::TYPES,
        ]);
    }

    public function edit(Trainee $trainee): View
    {
        return view('trainees.edit', ['trainee' => $trainee]);
    }

    public function update(TraineeRequest $request, Trainee $trainee): RedirectResponse
    {
        $old = $trainee->only(['reg_no', 'electronic_reg_no', 'last_name', 'first_name', 'specialty']);

        $trainee->fill($this->payload($request))->save();

        if ($request->hasFile('photo')) {
            $this->replacePhoto($trainee, $request);
        }

        $this->audit->log('trainee.updated', $trainee, 'تعديل متربص: '.$trainee->full_name, $old, $trainee->only(['reg_no', 'electronic_reg_no', 'last_name', 'first_name', 'specialty']));

        return redirect()->route('trainees.show', $trainee)->with('success', 'تم تحديث بيانات المتربص.');
    }

    public function destroy(Trainee $trainee): RedirectResponse
    {
        $trainee->delete();

        $this->audit->log('trainee.deleted', $trainee, 'حذف ناعم للمتربص: '.$trainee->full_name);

        return redirect()->route('trainees.index')->with('success', 'تم حذف المتربص (حذف ناعم).');
    }

    public function restore(string $trainee): RedirectResponse
    {
        $trainee = Trainee::onlyTrashed()->findOrFail($trainee);
        $trainee->restore();

        $this->audit->log('trainee.restored', $trainee, 'استعادة المتربص: '.$trainee->full_name);

        return back()->with('success', 'تم استعادة المتربص.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $this->ids($request);

        if (empty($ids)) {
            return back()->with('error', 'لم يتم تحديد أي سجل.');
        }

        Trainee::query()->whereIn('id', $ids)->delete();

        $this->audit->log('trainee.bulk_deleted', null, 'حذف ناعم جماعي لـ '.count($ids).' متربص');

        return back()->with('success', 'تم حذف '.count($ids).' سجل (حذف ناعم).');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $ids = $this->ids($request);

        if (empty($ids)) {
            return back()->with('error', 'لم يتم تحديد أي سجل.');
        }

        Trainee::onlyTrashed()->whereIn('id', $ids)->restore();

        $this->audit->log('trainee.bulk_restored', null, 'استعادة جماعية لـ '.count($ids).' متربص');

        return back()->with('success', 'تم استعادة '.count($ids).' سجل.');
    }

    public function photo(Trainee $trainee): StreamedResponse|RedirectResponse
    {
        $response = $this->files->response($trainee->photo);

        return $response ?? redirect()->route('trainees.show', $trainee);
    }

    private function payload(TraineeRequest $request): array
    {
        return [
            'reg_no' => $request->input('reg_no'),
            'electronic_reg_no' => $request->input('electronic_reg_no'),
            'last_name' => $request->input('last_name'),
            'first_name' => $request->input('first_name'),
            'last_name_fr' => $request->input('last_name_fr'),
            'first_name_fr' => $request->input('first_name_fr'),
            'gender' => $request->input('gender'),
            'birth_date' => $request->input('birth_date'),
            'birth_place' => $request->input('birth_place'),
            'birth_wilaya' => $request->input('birth_wilaya'),
            'residence_wilaya' => $request->input('residence_wilaya'),
            'address' => $request->input('address'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'father_name' => $request->input('father_name'),
            'mother_last_name' => $request->input('mother_last_name'),
            'mother_name' => $request->input('mother_name'),
            'nin' => $request->input('nin'),
            'specialty' => $request->input('specialty'),
            'diploma' => $request->input('diploma'),
            'level' => $request->input('level'),
            'training_mode' => $request->input('training_mode'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'eligible' => $request->boolean('eligible'),
            'file_accepted' => $request->boolean('file_accepted'),
            'deliberation_accepted' => $request->boolean('deliberation_accepted'),
            'account_cancelled' => $request->boolean('account_cancelled'),
            'special_needs' => $request->input('special_needs'),
            'notes' => $request->input('notes'),
        ];
    }

    private function replacePhoto(Trainee $trainee, TraineeRequest $request): void
    {
        $old = $trainee->photo;

        $path = $this->files->store(
            $request->file('photo'),
            'trainees/photos',
            $trainee->electronic_reg_no ?: $trainee->reg_no ?: 'trainee-'.$trainee->id,
            FileService::IMAGE_MIMES
        );

        $trainee->forceFill(['photo' => $path])->save();

        if ($old && $old !== $path) {
            $this->files->delete($old);
        }
    }

    private function ids(Request $request): array
    {
        $ids = $request->input('ids', []);

        return collect(is_array($ids) ? $ids : explode(',', (string) $ids))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
