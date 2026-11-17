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
        $number=certificate_number_defaults();
        foreach ($number as $key=>$default) if (isset($certificate->$key)) $number[$key]=$certificate->$key;
        $size=max(6,min(72,(float)$number['number_font_size']));
        $height=max(5,$size*25.4/72*1.2);
        // The bundled Thai font is a Thai-only subset, without Latin letters/digits.
        $pdf->SetFont('helvetica','',$size);
        list($red,$green,$blue)=$this->hex_rgb($number['number_font_color']); $pdf->SetTextColor($red,$green,$blue);
        $pdf->setCellPaddings(0,0,0,0); $pdf->setCellHeightRatio(1.2);
        $pdf->SetXY(297*(float)$number['number_x']/100,210*(float)$number['number_y']/100);
        $align=in_array($number['number_align'],array('L','C','R'),TRUE)?$number['number_align']:'R';
        $width=297*(float)$number['number_width']/100;
        $text=certificate_number_text($certificate->certificate_no);
        if ($text===$certificate->certificate_no) {
            $pdf->Cell($width,$height,$text,0,0,$align,FALSE,'',1,FALSE,'T','T');
        } else {
            $text_width=$pdf->GetStringWidth('เลขที่ ','notosansthai','',$size)+$pdf->GetStringWidth($certificate->certificate_no,'helvetica','',$size);
            $fit_size=$size*min(1,$width/max(1,$text_width));
            $html='<span style="font-family:notosansthai;font-size:'.$fit_size.'pt">เลขที่ </span>'
                .'<span style="font-family:helvetica;font-size:'.$fit_size.'pt">'.htmlspecialchars($certificate->certificate_no,ENT_QUOTES,'UTF-8').'</span>';
            $pdf->writeHTMLCell($width,$height,'','',$html,0,0,FALSE,TRUE,$align,TRUE);
        }
        return $pdf->Output($certificate->certificate_no.'.pdf',$destination);
    }

    private function hex_rgb($hex)
    {
        $hex=ltrim((string)$hex,'#'); if(!preg_match('/^[0-9a-f]{6}$/i',$hex))$hex='172033';
        return array(hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2)));
    }
}
