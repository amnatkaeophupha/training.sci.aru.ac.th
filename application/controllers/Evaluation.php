<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Evaluation extends CI_Controller
{
    public function __construct() { parent::__construct(); $this->load->model('Survey_model'); $this->load->model('Certificate_model'); }

    public function index($code)
    {
        $assignment=$this->Survey_model->get_assignment_by_code($code); if(!$assignment) show_404();
        $this->Survey_model->sync_invitations($assignment->id); $state=$this->state($assignment); $results=array();
        if($this->input->method(TRUE)==='POST' && $this->input->post('action')==='search' && $state!=='upcoming') $results=$this->Survey_model->search_participants($assignment->id, trim((string)$this->input->post('q',TRUE)));
        foreach($results as $r) $r->masked_email=$this->mask_email($r->email);
        $this->load->view('frontend/evaluation_landing',array('assignment'=>$assignment,'state'=>$state,'results'=>$results));
    }

    public function verify($code,$invitation_id)
    {
        $assignment=$this->Survey_model->get_assignment_by_code($code); if(!$assignment || $this->state($assignment)==='upcoming') show_404();
        $inv=$this->Survey_model->get_invitation($assignment->id,$invitation_id); if(!$inv) show_404();
        $error='';
        if($this->input->method(TRUE)==='POST') {
            if($this->Survey_model->verify_invitation($inv,$this->input->post('email',TRUE),$this->input->post('phone4',TRUE))) {
                $this->session->set_userdata('evaluation_verified_'.$assignment->id,(int)$inv->id); redirect('evaluation/'.$code.'/status'); return;
            }
            $error='ยืนยันข้อมูลไม่สำเร็จ กรุณากรอกอีเมลเต็มที่ลงทะเบียน หรือเลขโทรศัพท์ 4 ตัวท้ายให้ถูกต้อง';
        }
        $this->load->view('frontend/evaluation_verify',array('assignment'=>$assignment,'invitation'=>$inv,'error'=>$error));
    }

    public function status($code)
    {
        list($assignment,$inv)=$this->verified($code); if(!$inv)return;
        $certificate=$this->Certificate_model->get_active_by_invitation($inv->id);
        if(!$certificate && (int)$inv->registration_status===5 && !empty($inv->profile_confirmed_at) && !empty($inv->completed_at)) {
            $this->Certificate_model->issue($assignment->batch_id,$inv->participant_id,$inv->id,NULL);
            $certificate=$this->Certificate_model->get_active_by_invitation($inv->id);
        }
        $this->load->view('frontend/evaluation_status',array('assignment'=>$assignment,'invitation'=>$inv,'evaluation_state'=>$this->state($assignment),'certificate'=>$certificate));
    }

    public function profile($code)
    {
        list($assignment,$inv)=$this->verified($code); if(!$inv)return;
        if($this->input->method(TRUE)==='POST') {
            $data=array('title_name'=>trim((string)$this->input->post('title_name',TRUE)),'first_name'=>trim((string)$this->input->post('first_name',TRUE)),'last_name'=>trim((string)$this->input->post('last_name',TRUE)),'email'=>trim((string)$this->input->post('email',TRUE)),'phone'=>trim((string)$this->input->post('phone',TRUE)),'school_name'=>trim((string)$this->input->post('school_name',TRUE)));
            if($data['first_name']===''||$data['last_name']===''||!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){ $this->session->set_flashdata('error','กรุณากรอกชื่อ นามสกุล และอีเมลให้ถูกต้อง'); }
            else { $this->Survey_model->update_profile($inv,$data,$this->input->ip_address()); $this->session->set_flashdata('success','แก้ไขข้อมูลเรียบร้อยแล้ว'); }
            redirect('evaluation/'.$code.'/status'); return;
        }
        $this->load->view('frontend/evaluation_profile',array('assignment'=>$assignment,'invitation'=>$inv));
    }

    public function confirm($code)
    {
        list($assignment,$inv)=$this->verified($code); if(!$inv)return;
        $this->Survey_model->confirm_profile($inv->id); redirect('evaluation/'.$code.'/status');
    }

    public function form($code)
    {
        list($assignment,$inv)=$this->verified($code); if(!$inv)return;
        if($this->state($assignment)!=='open'){ $this->session->set_flashdata('error','แบบประเมินปิดรับคำตอบแล้ว'); redirect('evaluation/'.$code.'/status'); return; }
        if(empty($inv->profile_confirmed_at)){ $this->session->set_flashdata('error','กรุณายืนยันข้อมูลส่วนตัวก่อนทำแบบประเมิน'); redirect('evaluation/'.$code.'/status'); return; }
        if(!empty($inv->completed_at)){ redirect('evaluation/'.$code.'/status'); return; }
        $questions=$this->Survey_model->get_questions($assignment->survey_id); $error='';
        if($this->input->method(TRUE)==='POST') {
            $answers=(array)$this->input->post('answer');
            if($this->Survey_model->submit_response($assignment,$inv,$answers)){ $this->session->set_flashdata('success','ส่งแบบประเมินเรียบร้อยแล้ว ขอบคุณสำหรับความคิดเห็น'); redirect('evaluation/'.$code.'/status'); return; }
            $error='กรุณาตอบคำถามบังคับให้ครบทุกข้อ';
        }
        $this->load->view('frontend/evaluation_form',array('assignment'=>$assignment,'questions'=>$questions,'error'=>$error));
    }

    public function certificate($code)
    {
        list($assignment,$inv)=$this->verified($code); if(!$inv)return;
        if((int)$inv->registration_status!==5 || empty($inv->profile_confirmed_at) || empty($inv->completed_at)) show_404();
        $certificate=$this->Certificate_model->get_active_by_invitation($inv->id); if(!$certificate) show_404();
        $this->Certificate_model->record_download($certificate->id); $this->load->library('Certificate_pdf');
        $this->certificate_pdf->download($certificate);
    }

    private function verified($code)
    {
        $assignment=$this->Survey_model->get_assignment_by_code($code); if(!$assignment || $this->state($assignment)==='upcoming'){ show_404(); return array(NULL,NULL); }
        $id=(int)$this->session->userdata('evaluation_verified_'.$assignment->id); $inv=$id?$this->Survey_model->get_invitation($assignment->id,$id):NULL;
        if(!$inv){ redirect('evaluation/'.$code); return array($assignment,NULL); } return array($assignment,$inv);
    }
    private function state($a) { if(time()<strtotime($a->open_at))return 'upcoming'; if((int)$a->status!==1||time()>strtotime($a->close_at))return 'closed'; return 'open'; }
    private function mask_email($email) { $parts=explode('@',(string)$email); if(count($parts)!==2)return '-'; return mb_substr($parts[0],0,1,'UTF-8').'***@'.$parts[1]; }
}
