/* منصة الزاوية — ماسح الباركود/QR في منصة المطعم */
(function () {
    'use strict';

    var form = document.getElementById('scan-form');
    if (!form) { return; }

    var input = document.getElementById('scan-input');
    var video = document.getElementById('camera-video');
    var wrap = document.getElementById('camera-wrap');
    var status = document.getElementById('camera-status');
    var startBtn = document.getElementById('camera-btn');
    var stopBtn = document.getElementById('camera-stop');
    var message = document.getElementById('scan-message');

    var stream = null;
    var rafId = null;
    var detector = null;
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d', { willReadFrequently: true });
    var lastCode = '';
    var lastAt = 0;

    var hasBarcodeDetector = 'BarcodeDetector' in window;
    var hasJsQr = typeof window.jsQR === 'function';

    function setMessage(text) {
        if (message) { message.textContent = text || ''; }
    }

    function submitCode(code) {
        code = (code || '').trim();
        if (!code) { return; }

        var now = Date.now();
        if (code === lastCode && now - lastAt < 2500) { return; }
        lastCode = code;
        lastAt = now;

        if (navigator.vibrate) { navigator.vibrate(60); }

        input.value = code;
        stopCamera();
        form.submit();
    }

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setMessage('الكاميرا غير مدعومة في هذا المتصفح. استخدم الإدخال اليدوي.');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false
            });
        } catch (err) {
            setMessage('تعذّر الوصول إلى الكاميرا. تأكد من صلاحيات المتصفح ومن اتصال HTTPS.');
            return;
        }

        wrap.style.display = 'block';
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-flex';
        video.srcObject = stream;
        await video.play();

        if (hasBarcodeDetector) {
            detector = new window.BarcodeDetector({
                formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'codabar', 'itf']
            });
            status.textContent = 'الكاميرا تعمل (BarcodeDetector). وجّه الرمز نحو الكاميرا.';
            scanBarcodeDetector();
        } else if (hasJsQr) {
            status.textContent = 'الكاميرا تعمل (jsQR). وجّه الرمز نحو الكاميرا.';
            scanJsQr();
        } else {
            status.textContent = 'الكاميرا تعمل، لكن قارئ الرموز غير متوفر في هذا المتصفح. استخدم الإدخال اليدوي أو قارئ USB.';
        }
    }

    function stopCamera() {
        if (rafId) { cancelAnimationFrame(rafId); rafId = null; }

        if (stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
            stream = null;
        }

        if (wrap) { wrap.style.display = 'none'; }
        if (startBtn) { startBtn.style.display = 'inline-flex'; }
        if (stopBtn) { stopBtn.style.display = 'none'; }
    }

    async function scanBarcodeDetector() {
        if (!stream) { return; }

        try {
            var codes = await detector.detect(video);
            if (codes && codes.length > 0 && codes[0].rawValue) {
                submitCode(codes[0].rawValue);
                return;
            }
        } catch (err) { /* تجاهل أخطاء الإطار الواحد */ }

        rafId = requestAnimationFrame(scanBarcodeDetector);
    }

    function scanJsQr() {
        if (!stream) { return; }

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var result = window.jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert'
            });

            if (result && result.data) {
                submitCode(result.data);
                return;
            }
        }

        rafId = requestAnimationFrame(scanJsQr);
    }

    if (startBtn) { startBtn.addEventListener('click', startCamera); }
    if (stopBtn) { stopBtn.addEventListener('click', stopCamera); }

    window.addEventListener('beforeunload', stopCamera);
})();
