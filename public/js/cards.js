/* ============================================================
   طباعة البطاقات التعريفية — التحكم بالوجه الأمامي والخلفي
   ============================================================ */
(function () {
    'use strict';

    var MODES = ['print-front', 'print-back', 'duplex'];

    function clearModes() {
        MODES.forEach(function (name) {
            document.body.classList.remove(name);
        });
    }

    function setMode(mode) {
        clearModes();

        if (mode === 'front') {
            document.body.classList.add('print-front');
        } else if (mode === 'back') {
            document.body.classList.add('print-back');
        } else if (mode === 'both') {
            document.body.classList.add('duplex');
        }
    }

    /**
     * طباعة البطاقات.
     * mode: front (الأمامي فقط) | back (الخلفي فقط) | both (الوجهان معًا)
     */
    window.printCards = function (mode) {
        setMode(mode);

        window.addEventListener('afterprint', clearModes, { once: true });

        window.print();

        // احتياط للمتصفحات التي لا تدعم afterprint
        window.setTimeout(clearModes, 4000);
    };

    window.addEventListener('afterprint', clearModes);
})();
