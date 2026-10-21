/* منصة الزاوية — سلوك الواجهة */
(function () {
    'use strict';

    // تبديل القائمة الجانبية
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-sidebar-toggle]');
        if (toggle) {
            e.preventDefault();
            if (window.innerWidth <= 860) {
                document.body.classList.toggle('sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('zawiaSidebar', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
                } catch (err) {}
            }
        }

        var backdrop = e.target.closest('.sidebar-backdrop');
        if (backdrop) {
            document.body.classList.remove('sidebar-open');
        }
    });

    // استعادة حالة القائمة
    try {
        if (localStorage.getItem('zawiaSidebar') === '1' && window.innerWidth > 860) {
            document.body.classList.add('sidebar-collapsed');
        }
    } catch (err) {}

    // القوائم المنسدلة
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-dropdown-toggle]');
        document.querySelectorAll('.dropdown.open').forEach(function (d) {
            if (!trigger || !d.contains(trigger)) { d.classList.remove('open'); }
        });
        if (trigger) {
            e.preventDefault();
            trigger.closest('.dropdown').classList.toggle('open');
        }
    });

    // تأكيد العمليات الحساسة
    document.addEventListener('submit', function (e) {
        var form = e.target;
        var msg = form.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) {
            e.preventDefault();
        }
    });

    // تأكيد الروابط
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-confirm]');
        if (link && !window.confirm(link.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });

    // تحديد الكل في جداول التحديد الجماعي
    var selectAll = document.querySelector('[data-select-all]');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('[data-row-check]').forEach(function (c) { c.checked = selectAll.checked; });
        });
    }

    // إخفاء رسائل التنبيه تدريجياً
    setTimeout(function () {
        document.querySelectorAll('.alert[data-auto-hide]').forEach(function (a) {
            a.style.transition = 'opacity .5s';
            a.style.opacity = '0';
            setTimeout(function () { a.remove(); }, 500);
        });
    }, 6000);

    // الطباعة ثم تنظيف حالة الطباعة
    window.printMode = function (mode) {
        document.body.classList.remove('print-front', 'print-back', 'duplex');
        if (mode === 'front') { document.body.classList.add('print-front'); }
        if (mode === 'back') { document.body.classList.add('print-back'); }
        if (mode === 'both') { document.body.classList.add('duplex'); }
        window.print();
        setTimeout(function () {
            document.body.classList.remove('print-front', 'print-back', 'duplex');
        }, 1200);
    };

    window.addEventListener('afterprint', function () {
        document.body.classList.remove('print-front', 'print-back', 'duplex');
    });
})();
