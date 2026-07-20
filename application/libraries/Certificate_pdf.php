<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_pdf
{
    public function download($certificate)
    {
        $this->output($certificate,'D');
        exit;
    }

    public function inline($certificate)
    {
        $this->output($certificate,'I');
        exit;
    }

    public function content($certificate)
    {
        return $this->output($certificate,'S');
    }

    private function output($certificate,$destination)
    {
        $library=APPPATH.'third_party/tcpdf/tcpdf.php'; if(!is_file($library)) show_error('ยังไม่ได้ติดตั้งไลบรารี TCPDF',500);
        require_once $library;
        $background=FCPATH.$certificate->background_path; if(!is_file($background)) show_error('ไม่พบไฟล์แม่แบบวุฒิบัตร',500);
        $pdf=new TCPDF('L','mm','A4',TRUE,'UTF-8',FALSE); $pdf->SetCreator('Science & Technology Training Center');
        $pdf->SetTitle($certificate->certificate_no); $pdf->setPrintHeader(FALSE); $pdf->setPrintFooter(FALSE);
        $pdf->SetMargins(0,0,0); $pdf->SetAutoPageBreak(FALSE,0); $pdf->AddPage();
        $pdf->Image($background,0,0,297,210,'','','',FALSE,300,'',FALSE,FALSE,0);
        list($red,$green,$blue)=$this->hex_rgb($certificate->font_color); $pdf->SetTextColor($red,$green,$blue);
        $pdf->SetFont('notosansthai','',max(12,(float)$certificate->font_size));
        $pdf->SetXY(297*(float)$certificate->name_x/100,210*(float)$certificate->name_y/100);
        $pdf->Cell(297*(float)$certificate->name_width/100,12,$certificate->recipient_name,0,0,'C',FALSE,'',1,FALSE,'T','M');
        $pdf->SetFont('notosansthai','',9); $pdf->SetTextColor(40,40,40); $pdf->SetXY(210,196);
        $pdf->Cell(75,5,'เลขที่ '.$certificate->certificate_no,0,0,'R');
        return $pdf->Output($certificate->certificate_no.'.pdf',$destination);
    }

    private function hex_rgb($hex)
    {
        $hex=ltrim((string)$hex,'#'); if(!preg_match('/^[0-9a-f]{6}$/i',$hex))$hex='172033';
        return array(hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2)));
    }
}
