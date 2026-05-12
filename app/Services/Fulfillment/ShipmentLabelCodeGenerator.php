<?php

namespace App\Services\Fulfillment;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ShipmentLabelCodeGenerator
{
    public function barcodeDataUri(string $value, float $widthFactor = 2.2, float $height = 58): string
    {
        $barcode = (new BarcodeGeneratorSVG)->getBarcode(
            $this->codeValue($value),
            BarcodeGenerator::TYPE_CODE_128,
            $widthFactor,
            $height,
        );

        return $this->svgDataUri($barcode);
    }

    public function qrDataUri(string $value): string
    {
        $result = (new Builder(
            writer: new SvgWriter,
            writerOptions: [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
            ],
            validateResult: false,
            data: $this->codeValue($value),
            encoding: new Encoding('ISO-8859-1'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 110,
            margin: 4,
            roundBlockSizeMode: RoundBlockSizeMode::None,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        ))->build();

        return $result->getDataUri();
    }

    public function imageDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $mimeType = mime_content_type($path);

        if (! is_string($mimeType) || $mimeType === '') {
            return null;
        }

        $contents = file_get_contents($path);

        if (! is_string($contents)) {
            return null;
        }

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($contents));
    }

    private function svgDataUri(string $svg): string
    {
        return sprintf('data:image/svg+xml;base64,%s', base64_encode($svg));
    }

    private function codeValue(string $value): string
    {
        $value = trim($value);

        return $value === '' || strcasecmp($value, 'pending') === 0
            ? 'PENDING'
            : $value;
    }
}
