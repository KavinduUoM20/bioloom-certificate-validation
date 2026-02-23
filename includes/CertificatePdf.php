<?php
/**
 * Generate certificate PDF from template image + recipient name - Bioloom Islands Pvt Ltd
 * Uses TCPDF: A5 landscape, template as full-page background, name in a configurable area.
 */

require_once __DIR__ . '/../vendor/autoload.php';

class CertificatePdf
{
    private string $templatePath;
    private array $config;

    public function __construct(?string $templatePath = null, ?array $config = null)
    {
        $base = dirname(__DIR__);
        $this->templatePath = $templatePath ?? $base . '/template/certificate-template.png';
        if ($config !== null) {
            $this->config = $config;
        } else {
            $configPath = $base . '/config/certificate.php';
            $this->config = is_file($configPath) ? require $configPath : $this->defaultConfig();
        }
    }

    private function defaultConfig(): array
    {
        return [
            'page_width_mm'  => 210,
            'page_height_mm' => 148,
            'orientation'    => 'L',
            'name_area'      => [
                'left_mm'   => 25,
                'top_mm'    => 75,
                'width_mm'  => 160,
                'height_mm' => 15,
            ],
            'name_font_size' => 28,
        ];
    }

    /**
     * Output PDF to browser (inline so it opens in new tab, user can download)
     */
    public function outputPdf(string $recipientName, string $filename = 'certificate.pdf'): void
    {
        $w = $this->config['page_width_mm'];
        $h = $this->config['page_height_mm'];
        $format = [$w, $h];
        $orient = $this->config['orientation'] ?? 'L';

        $pdf = new TCPDF($orient, 'mm', $format, true, 'UTF-8', false);
        $pdf->SetCreator('BioLoom Islands');
        $pdf->SetAuthor('BioLoom Islands');
        $pdf->SetTitle('Certificate - ' . $recipientName);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $pageWidth  = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        // 1) Full-page background from template image
        if (is_file($this->templatePath)) {
            $pdf->Image($this->templatePath, 0, 0, $pageWidth, $pageHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
        } else {
            $pdf->SetFillColor(245, 248, 250);
            $pdf->Rect(0, 0, $pageWidth, $pageHeight, 'F');
            $pdf->SetDrawColor(200, 210, 220);
            $pdf->Rect(20, 20, $pageWidth - 40, $pageHeight - 40, 'D');
        }

        // 2) Recipient name in the configured area (marked in config/certificate.php)
        $area = $this->config['name_area'];
        $left   = $area['left_mm'] ?? 25;
        $top    = $area['top_mm'] ?? 75;
        $width  = $area['width_mm'] ?? 160;
        $height = $area['height_mm'] ?? 15;
        $fontSize = $this->config['name_font_size'] ?? 28;

        $pdf->SetFont('helvetica', 'B', $fontSize);
        $pdf->SetTextColor(30, 40, 55);
        $pdf->SetAlpha(1);
        $pdf->SetXY($left, $top);
        $pdf->Cell($width, $height, $recipientName, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Output($filename, 'I');
    }
}
