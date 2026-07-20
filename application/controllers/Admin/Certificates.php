<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificates extends CI_Controller
{
    public function __construct()
    {
        parent::__construct(); $this->load->library('session'); $this->load->model('Certificate_model');
        if(!$this->session->userdata('admin_logged_in')) redirect('admin');
    }

    public function index()
    {
        $batch_id=(int)$this->input->get('batch_id'); $batch=$batch_id?$this->Certificate_model->get_batch($batch_id):NULL;
        $this->load->view('admins/certificates',array('batches'=>$this->Certificate_model->get_batches(),'batch'=>$batch,
            'template'=>$batch?$this->Certificate_model->get_template($batch_id):NULL,'roster'=>$batch?$this->Certificate_model->get_roster($batch_id):array()));
    }

    public function template($batch_id)
    {
        if($this->input->method(TRUE)!=='POST') show_404(); $batch=$this->Certificate_model->get_batch($batch_id); if(!$batch) show_404();
        $existing=$this->Certificate_model->get_template($batch_id); $path=$existing?$existing->background_path:'';
        if(!empty($_FILES['background']['name'])) { $error=$this->upload_background($path); if($error!=='') return $this->fail($error,$batch_id); }
        if($path==='') return $this->fail('กรุณาเลือกภาพพื้นหลังวุฒิบัตร',$batch_id);
        $data=array('background_path'=>$path,'name_x'=>$this->percent('name_x',10),'name_y'=>$this->percent('name_y',45),
            'name_width'=>$this->percent('name_width',80),'font_size'=>max(12,min(72,(float)$this->input->post('font_size'))),
            'font_color'=>preg_match('/^#[0-9a-f]{6}$/i',(string)$this->input->post('font_color'))?$this->input->post('font_color'):'#172033');
        $this->Certificate_model->save_template($batch_id,$data); $this->session->set_flashdata('success','บันทึกแม่แบบวุฒิบัตรเรียบร้อยแล้ว'); redirect('admin/certificates?batch_id='.$batch_id);
    }

    public function issue($participant_id)
    {
        if($this->input->method(TRUE)!=='POST') show_404(); $batch_id=(int)$this->input->post('batch_id'); $invitation_id=(int)$this->input->post('invitation_id');
        if(!$this->Certificate_model->issue($batch_id,$participant_id,$invitation_id,$this->session->userdata('admin_id'))) return $this->fail('ไม่สามารถออกวุฒิบัตรได้ กรุณาตรวจแม่แบบและเงื่อนไขผู้เข้าอบรม',$batch_id);
        $this->session->set_flashdata('success','ออกวุฒิบัตรเรียบร้อยแล้ว'); redirect('admin/certificates?batch_id='.$batch_id);
    }

    public function issue_batch($batch_id)
    {
        if($this->input->method(TRUE)!=='POST') show_404(); $count=$this->Certificate_model->issue_batch($batch_id,$this->session->userdata('admin_id'));
        $this->session->set_flashdata('success','ออกวุฒิบัตรใหม่ '.number_format($count).' รายการ'); redirect('admin/certificates?batch_id='.(int)$batch_id);
    }

    public function cancel($id)
    {
        if($this->input->method(TRUE)!=='POST') show_404(); $batch_id=(int)$this->input->post('batch_id'); $this->Certificate_model->cancel($id);
        $this->session->set_flashdata('success','ยกเลิกวุฒิบัตรเรียบร้อยแล้ว'); redirect('admin/certificates?batch_id='.$batch_id);
    }

    public function view($id)
    {
        $certificate=$this->Certificate_model->get_active($id); if(!$certificate) show_404();
        $this->load->library('Certificate_pdf'); $this->certificate_pdf->inline($certificate);
    }

    public function email($id)
    {
        if($this->input->method(TRUE)!=='POST') show_404();
        $certificate=$this->Certificate_model->get_active($id); if(!$certificate) show_404();
        if(!filter_var($certificate->email,FILTER_VALIDATE_EMAIL)) return $this->fail('ไม่พบอีเมลที่ถูกต้องของผู้เข้าอบรม',$certificate->batch_id);
        $this->load->library('Certificate_pdf'); $pdf=$this->certificate_pdf->content($certificate);
        $this->email->clear(TRUE); $this->email->set_mailtype('html'); $this->email->set_newline("\r\n");
        $this->email->from('amnat@aru.ac.th','ศูนย์ฝึกอบรมคณะวิทยาศาสตร์และเทคโนโลยี');
        $this->email->to($certificate->email); $this->email->subject('วุฒิบัตร '.$certificate->course_title.' '.$certificate->batch_no);
        $this->email->message('<p>เรียน '.html_escape($certificate->recipient_name).'</p><p>ขอส่งวุฒิบัตรเลขที่ '.html_escape($certificate->certificate_no).' สำหรับการอบรมหลักสูตร '.html_escape($certificate->course_title).' ตามไฟล์แนบ</p>');
        $this->email->attach($pdf,'attachment',$certificate->certificate_no.'.pdf','application/pdf');
        if(!$this->email->send(FALSE)) return $this->fail('ส่งอีเมลไม่สำเร็จ กรุณาตรวจสอบการตั้งค่า SMTP และลองใหม่',$certificate->batch_id);
        $this->session->set_flashdata('success','ส่งวุฒิบัตรไปที่ '.$certificate->email.' เรียบร้อยแล้ว'); redirect('admin/certificates?batch_id='.(int)$certificate->batch_id);
    }

    public function email_batch($batch_id)
    {
        if($this->input->method(TRUE)!=='POST') show_404();
        $batch=$this->Certificate_model->get_batch($batch_id); if(!$batch) show_404();
        $certificates=$this->Certificate_model->get_active_by_batch($batch_id); $sent=0; $failed=0; $skipped=0;
        $this->load->library('Certificate_pdf');
        foreach($certificates as $certificate) {
            if(!filter_var($certificate->email,FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
            $pdf=$this->certificate_pdf->content($certificate);
            $this->email->clear(TRUE); $this->email->set_mailtype('html'); $this->email->set_newline("\r\n");
            $this->email->from('amnat@aru.ac.th','ศูนย์ฝึกอบรมคณะวิทยาศาสตร์และเทคโนโลยี');
            $this->email->to($certificate->email); $this->email->subject('วุฒิบัตร '.$certificate->course_title.' '.$certificate->batch_no);
            $this->email->message('<p>เรียน '.html_escape($certificate->recipient_name).'</p><p>ขอส่งวุฒิบัตรเลขที่ '.html_escape($certificate->certificate_no).' สำหรับการอบรมหลักสูตร '.html_escape($certificate->course_title).' ตามไฟล์แนบ</p>');
            $this->email->attach($pdf,'attachment',$certificate->certificate_no.'.pdf','application/pdf');
            $this->email->send(FALSE)?$sent++:$failed++;
        }
        if(!$certificates) return $this->fail('ยังไม่มีวุฒิบัตรที่ออกแล้วสำหรับรุ่นนี้',$batch_id);
        $message='ส่งอีเมลสำเร็จ '.number_format($sent).' รายการ';
        if($failed>0||$skipped>0) $message.=' ส่งไม่สำเร็จ '.number_format($failed).' และไม่มีอีเมลที่ถูกต้อง '.number_format($skipped).' รายการ';
        if($sent===0) { $this->session->set_flashdata('error',$message.' กรุณาตรวจสอบการตั้งค่า SMTP'); }
        else { $this->session->set_flashdata('success',$message); }
        redirect('admin/certificates?batch_id='.(int)$batch_id);
    }

    private function percent($key,$default) { $v=$this->input->post($key); return $v===NULL?$default:max(0,min(100,(float)$v)); }
    private function fail($message,$batch_id) { $this->session->set_flashdata('error',$message); redirect('admin/certificates?batch_id='.(int)$batch_id); }
    private function upload_background(&$path)
    {
        $dir=FCPATH.'uploads/certificates/'; if(!is_dir($dir)&&!mkdir($dir,0755,TRUE)) return 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้';
        $size=@getimagesize($_FILES['background']['tmp_name']); if(!$size||!in_array($size[2],array(IMAGETYPE_JPEG,IMAGETYPE_PNG),TRUE)) return 'รองรับเฉพาะภาพ JPG หรือ PNG';
        $ratio=$size[0]/$size[1]; if(abs($ratio-(297/210))>0.03) return 'ภาพต้องเป็นสัดส่วน A4 แนวนอน (297:210)';
        $this->load->library('upload'); $this->upload->initialize(array('upload_path'=>$dir,'allowed_types'=>'jpg|jpeg|png','max_size'=>10240,'encrypt_name'=>TRUE));
        if(!$this->upload->do_upload('background')) return strip_tags($this->upload->display_errors('',''));
        $old=$path; $path='uploads/certificates/'.$this->upload->data('file_name');
        if($old&&strpos($old,'uploads/certificates/')===0&&is_file(FCPATH.$old)) @unlink(FCPATH.$old); return '';
    }
}
