<?php

namespace App\Http\Controllers;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Picqer\Barcode\Renderers\PngRenderer;
use Picqer\Barcode\Types\TypeCode128;

/**
 * توليد رموز Code128 وQR للبطاقات التعريفية.
 * المسارات محمية بتسجيل الدخول، والرمز يُمثل معرّفًا فقط دون بيانات شخصية حساسة.
 */
class BarcodeController extends Controller
{
    /** أقصى طول للرمز المسموح بترميزه */
    private const MAX_LENGTH = 48;

    public function code128(string $code): Response
    {
        $value = $this->normalize($code);

        $barcode = (new TypeCode128())->getBarcode($value);

        $renderer = new PngRenderer();
        $renderer->setForegroundColor([15, 23, 42]);
        $renderer->setBackgroundColor([255, 255, 255]);

        // العرض يجب أن يكون من مضاعفات العرض الأصلي لضمان وضوح الأشرطة عند المسح
        $png = $renderer->render($barcode, max(1, $barcode->getWidth()) * 2, 64);

        return response($png, 200, $this->headers('image/png'));
    }

    public function qr(string $code): Response
    {
        $value = $this->normalize($code);

        $qrCode = new QrCode(
            data: $value,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 320,
            margin: 4,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );

        $result = (new PngWriter())->write($qrCode);

        return response($result->getString(), 200, $this->headers($result->getMimeType()));
    }

    /** تطهير الرمز: قص الطول ومنع القيم الفارغة */
    private function normalize(string $code): string
    {
        $value = trim($code);
        $value = preg_replace('/[^\p{L}\p{N}\-_\.]/u', '', $value) ?? '';

        return $value === '' ? '0' : mb_substr($value, 0, self::MAX_LENGTH);
    }

    /** @return array<string, string> */
    private function headers(string $mime): array
    {
        return [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
