<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Batch_model $Batch_model
 * @property Course_model $Course_model
 */
class Batches extends CI_Controller
{
    private $statuses = array(1, 2, 3, 4);

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Batch_model');
        $this->load->model('Course_model');
        $this->load->model('Survey_model');
        $this->load->model('Certificate_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit');
        $edit_batch = $edit_id > 0 ? $this->Batch_model->get_by_id($edit_id) : NULL;

        $this->load->view('admins/batches', array(
            'batches' => $this->Batch_model->get_all(),
            'courses' => $this->Course_model->get_all(),
            'stats' => $this->Batch_model->get_stats(),
            'edit_batch' => $edit_batch,
            'statuses' => $this->statuses
        ));
    }

    public function store()
    {
        $data = $this->get_post_data();
        $error = $this->validate_batch($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/batches');
            return;
        }

        $this->Batch_model->create_batch($data);
        $this->session->set_flashdata('success', 'เพิ่มรุ่นอบรมเรียบร้อยแล้ว');
        redirect('admin/batches');
    }

    public function update($id)
    {
        $batch = $this->Batch_model->get_by_id($id);

        if (!$batch) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลรุ่นอบรม');
            redirect('admin/batches');
            return;
        }

        $data = $this->get_post_data();
        $error = $this->validate_batch($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/batches?edit='.(int) $id);
            return;
        }

        $this->Batch_model->update_batch($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขรุ่นอบรมเรียบร้อยแล้ว');
        redirect('admin/batches');
    }

    public function delete($id)
    {
        $id = (int) $id;

        if (!$this->Batch_model->get_by_id($id)) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลรุ่นอบรม');
            redirect('admin/batches');
            return;
        }

        if ($this->Batch_model->count_registrations($id) > 0) {
            $this->session->set_flashdata('error', 'ไม่สามารถลบรุ่นอบรมที่มีผู้ลงทะเบียนอยู่ได้');
            redirect('admin/batches');
            return;
        }

        $this->Batch_model->delete_batch($id);
        $this->session->set_flashdata('success', 'ลบรุ่นอบรมเรียบร้อยแล้ว');
        redirect('admin/batches');
    }

    public function evaluation($id)
    {
        $id=(int)$id; $batch=$this->Batch_model->get_by_id($id); if(!$batch) show_404();
        $assignment=$this->Survey_model->get_batch_assignment($id);
        if($this->input->method(TRUE)==='POST') {
            $survey_id=(int)$this->input->post('survey_id',TRUE); $survey=$this->Survey_model->get($survey_id);
            $open=$this->evaluation_datetime($this->input->post('open_at',TRUE)); $close=$this->evaluation_datetime($this->input->post('close_at',TRUE));
            if(!$survey || count($this->Survey_model->get_questions($survey_id))<1) return $this->evaluation_fail('กรุณาเลือกแบบประเมินที่มีคำถามอย่างน้อย 1 ข้อ',$id);
            if(!$open||!$close||strtotime($close)<=strtotime($open)) return $this->evaluation_fail('ช่วงเวลาเปิดและปิดแบบประเมินไม่ถูกต้อง',$id);
            if($assignment) {
                if((int)$assignment->survey_id!==$survey_id && $this->Survey_model->assignment_has_responses($assignment->id)) return $this->evaluation_fail('ไม่สามารถเปลี่ยนแบบประเมินได้ เนื่องจากมีผู้ตอบแล้ว',$id);
                $this->Survey_model->update_batch_assignment($assignment->id,$survey_id,$open,$close); $assignment_id=$assignment->id;
            } else {
                $assignment_id=$this->Survey_model->create_assignment(array('survey_id'=>$survey_id,'course_id'=>$batch->course_id,'batch_id'=>$id,'open_at'=>$open,'close_at'=>$close));
                if(!$assignment_id) return $this->evaluation_fail('ไม่สามารถเปิดแบบประเมินสำหรับรุ่นนี้ได้',$id);
            }
            $this->Survey_model->sync_invitations($assignment_id); $this->session->set_flashdata('success','บันทึกการตั้งค่าแบบประเมินเรียบร้อยแล้ว');
            redirect('admin/batches/'.$id.'/evaluation'); return;
        }
        if($assignment) { $this->Survey_model->sync_invitations($assignment->id); $assignment=$this->Survey_model->get_batch_assignment($id); }
        $end_date=$batch->end_date ?: ''; $end_time=$batch->end_time ?: '23:59:00';
        $default_open=$end_date?date('Y-m-d\TH:i',strtotime($end_date.' '.$end_time)):'';
        $default_close=$default_open?date('Y-m-d\TH:i',strtotime(str_replace('T',' ',$default_open).' +7 days')):'';
        $stats=$assignment?$this->Survey_model->get_assignment_stats($assignment->id):NULL;
        $this->load->view('admins/batch_evaluation',array('batch'=>$batch,'assignment'=>$assignment,'surveys'=>$this->Survey_model->get_all(),
            'default_open'=>$default_open,'default_close'=>$default_close,'stats'=>$stats));
    }

    private function evaluation_datetime($value) { $time=strtotime(str_replace('T',' ',(string)$value)); return $time?date('Y-m-d H:i:s',$time):NULL; }
    private function evaluation_fail($message,$batch_id) { $this->session->set_flashdata('error',$message); redirect('admin/batches/'.(int)$batch_id.'/evaluation'); }

    private function get_post_data()
    {
        return array(
            'course_id' => (int) $this->input->post('course_id', TRUE),
            'batch_no' => trim((string) $this->input->post('batch_no', TRUE)),
            'start_date' => $this->normalize_date($this->input->post('start_date', TRUE)),
            'end_date' => $this->normalize_date($this->input->post('end_date', TRUE)),
            'start_time' => $this->normalize_time($this->input->post('start_time', TRUE)),
            'end_time' => $this->normalize_time($this->input->post('end_time', TRUE)),
            'registration_start' => $this->normalize_datetime($this->input->post('registration_start', TRUE)),
            'registration_end' => $this->normalize_datetime($this->input->post('registration_end', TRUE)),
            'capacity' => max(0, (int) $this->input->post('capacity', TRUE)),
            'status' => (int) $this->input->post('status', TRUE)
        );
    }

    private function validate_batch($data)
    {
        if ($data['course_id'] <= 0) {
            return 'กรุณาเลือกหลักสูตร';
        }

        if (!$this->Course_model->get_by_id($data['course_id'])) {
            return 'หลักสูตรไม่ถูกต้อง';
        }

        if (!in_array($data['status'], $this->statuses, TRUE)) {
            return 'สถานะรุ่นอบรมไม่ถูกต้อง';
        }

        if ($data['start_date'] !== NULL && $data['end_date'] !== NULL && $data['end_date'] < $data['start_date']) {
            return 'วันที่สิ้นสุดอบรมต้องไม่น้อยกว่าวันที่เริ่มอบรม';
        }

        if ($data['registration_start'] !== NULL && $data['registration_end'] !== NULL && $data['registration_end'] < $data['registration_start']) {
            return 'วันปิดรับสมัครต้องไม่น้อยกว่าวันเปิดรับสมัคร';
        }

        return '';
    }

    private function normalize_date($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return NULL;
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : NULL;
    }

    private function normalize_time($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return NULL;
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('H:i:s', $timestamp) : NULL;
    }

    private function normalize_datetime($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return NULL;
        }

        $timestamp = strtotime(str_replace('T', ' ', $value));

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : NULL;
    }
}
