<?php 
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $dompdf;

    public function __construct(){
        $this->dompdf = new DomPdf;

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont','Garamond');
        $pdfOptions->set('isHtml5ParserEnabled', true); // Ajoutez cette ligne
        $this->dompdf->setOptions($pdfOptions);
    }

    public function showPdfFile($html)
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $this->dompdf->stream("permissionsvalides.pdf", ['attachment' => false]);
    }

    public function generateBinaryPdf($html)
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $this->dompdf->output();
    }
}
