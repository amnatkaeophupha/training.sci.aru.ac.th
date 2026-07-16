<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Surveys extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Survey_model');
        if (!$this->session->userdata('admin_logged_in')) redirect('admin');
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit', TRUE);
        $this->load->view('admins/surveys', array(
            'tables_ready'=>$this->Survey_model->tables_ready(), 'surveys'=>$this->Survey_model->get_all(),
            'edit_survey'=>$edit_id ? $this->Survey_model->get($edit_id) : NULL,
            'assignments'=>$this->Survey_model->tables_ready() ? $this->Survey_model->get_assignments() : array()
        ));
    }

    public function save($id = 0)
    {
        $title = trim((string) $this->input->post('title', TRUE));
        if ($title === '') return $this->fail('กรุณากรอกชื่อแบบประเมิน', 'admin/surveys');
        $saved = $this->Survey_model->save_survey($id, array('title'=>$title,'description'=>trim((string)$this->input->post('description', TRUE)),'status'=>(int)$this->input->post('status', TRUE) ?: 1));
        if (!$saved) return $this->fail('ไม่สามารถบันทึกแบบประเมินได้', 'admin/surveys');
        $this->session->set_flashdata('success', 'บันทึกแบบประเมินเรียบร้อยแล้ว');
        redirect('admin/surveys/questions/'.($id ?: $saved));
    }

    public function questions($survey_id)
    {
        $survey = $this->Survey_model->get($survey_id); if (!$survey) show_404();
        $this->load->view('admins/survey_questions', array('survey'=>$survey,'questions'=>$this->Survey_model->get_questions($survey_id),'locked'=>$this->Survey_model->has_responses($survey_id)));
    }

    public function add_question($survey_id)
    {
        $survey = $this->Survey_model->get($survey_id); if (!$survey) show_404();
        if ($this->Survey_model->has_responses($survey_id)) return $this->fail('มีผู้ตอบแล้ว ไม่สามารถเปลี่ยนโครงสร้างคำถามได้', 'admin/surveys/questions/'.$survey_id);
        $type = (string) $this->input->post('question_type', TRUE); $text = trim((string) $this->input->post('question_text', TRUE));
        $options = preg_split('/\r\n|\r|\n/', trim((string) $this->input->post('options', TRUE)));
        $options = array_values(array_filter(array_map('trim', $options), function($v){return $v!=='';}));
        if ($text === '' || (in_array($type, array('single_choice','multiple_choice'), TRUE) && count($options) < 2)) return $this->fail('กรุณากรอกคำถาม และระบุตัวเลือกอย่างน้อย 2 ข้อสำหรับคำถามแบบเลือก', 'admin/surveys/questions/'.$survey_id);
        $this->Survey_model->add_question($survey_id, array('question_text'=>$text,'question_type'=>$type,'is_required'=>$this->input->post('is_required') ? 1 : 0,'options'=>$options));
        $this->session->set_flashdata('success', 'เพิ่มคำถามเรียบร้อยแล้ว'); redirect('admin/surveys/questions/'.$survey_id);
    }

    public function delete_question($survey_id, $question_id) { $this->Survey_model->delete_question($survey_id,$question_id); redirect('admin/surveys/questions/'.$survey_id); }
    public function move_question($survey_id, $question_id, $direction) { $this->Survey_model->move_question($survey_id,$question_id,$direction); redirect('admin/surveys/questions/'.$survey_id); }

    public function assign($survey_id)
    {
        $survey = $this->Survey_model->get($survey_id); if (!$survey) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $open = $this->datetime($this->input->post('open_at', TRUE)); $close = $this->datetime($this->input->post('close_at', TRUE));
            if (!$open || !$close || strtotime($close) <= strtotime($open)) return $this->fail('ช่วงเวลาเปิด–ปิดไม่ถูกต้อง', 'admin/surveys/assign/'.$survey_id);
            if (count($this->Survey_model->get_questions($survey_id)) < 1) return $this->fail('กรุณาเพิ่มคำถามอย่างน้อย 1 ข้อก่อนเปิดใช้งาน', 'admin/surveys/questions/'.$survey_id);
            $id = $this->Survey_model->create_assignment(array('survey_id'=>$survey_id,'course_id'=>(int)$this->input->post('course_id',TRUE),'batch_id'=>(int)$this->input->post('batch_id',TRUE),'open_at'=>$open,'close_at'=>$close));
            if (!$id) return $this->fail('ไม่สามารถเปิดแบบประเมินได้ กรุณาตรวจสอบว่ารุ่นนี้ยังไม่มีแบบประเมินที่เปิดอยู่', 'admin/surveys/assign/'.$survey_id);
            $this->session->set_flashdata('success','เปิดแบบประเมินและสร้างสิทธิ์ผู้เข้าอบรมเรียบร้อยแล้ว'); redirect('admin/surveys/assignment/'.$id); return;
        }
        $this->load->view('admins/survey_assign', array('survey'=>$survey,'courses'=>$this->Survey_model->get_courses(),'batches'=>$this->Survey_model->get_batches(),'assignments'=>$this->Survey_model->get_assignments($survey_id)));
    }

    public function assignment($id)
    {
        $assignment=$this->Survey_model->get_assignment($id); if(!$assignment) show_404();
        $list=$this->Survey_model->get_assignments($assignment->survey_id); $stats=NULL; foreach($list as $a) if((int)$a->id===(int)$id)$stats=$a;
        $this->load->view('admins/survey_assignment',array('assignment'=>$assignment,'stats'=>$stats,'roster'=>$this->Survey_model->get_invitation_roster($id)));
    }

    public function close($id) { $this->Survey_model->close_assignment($id); redirect('admin/surveys/assignment/'.$id); }
    public function regenerate($id) { $this->Survey_model->regenerate_code($id); $this->session->set_flashdata('success','สร้าง QR URL ใหม่แล้ว QR เดิมไม่สามารถใช้ได้'); redirect('admin/surveys/assignment/'.$id); }

    public function report($id)
    {
        $report=$this->Survey_model->get_report($id); if(!$report) show_404();
        $this->load->view('admins/survey_report',array('report'=>$report));
    }

    public function export($id)
    {
        $report=$this->Survey_model->get_report($id); if(!$report) show_404();
        $filename='survey-'.$id.'-'.date('Ymd-His').'.csv';
        header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="'.$filename.'"');
        $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF"); fputcsv($out,array('หลักสูตร','รุ่น','คำถาม','คำตอบ'));
        foreach($report->questions as $q) foreach($q->answers as $a) fputcsv($out,array_map(array($this,'csv_cell'),array($report->course_title,$report->batch_no,$q->question_text,$a->option_text ?: ($a->rating_value ?: $a->text_value))));
        fclose($out); exit;
    }

    private function datetime($value) { $time=strtotime((string)$value); return $time ? date('Y-m-d H:i:s',$time) : NULL; }
    public function csv_cell($value) { $value=(string)$value; return preg_match('/^[=+\-@]/',$value) ? "'".$value : $value; }
    private function fail($message,$url) { $this->session->set_flashdata('error',$message); redirect($url); }
}
