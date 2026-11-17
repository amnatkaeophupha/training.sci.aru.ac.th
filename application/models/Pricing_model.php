<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pricing_model extends CI_Model
{
    public function options($course_id,$active_only=FALSE)
    {
        $this->db->where('course_id',(int)$course_id);
        if ($active_only) $this->db->where('is_active',1);
        return $this->db->order_by('sort_order')->order_by('id')->get('training_course_fee_options')->result_array();
    }

    public function configuration_for_batch($batch_id)
    {
        $course=$this->db->select('c.*,b.batch_no')->from('training_courses c')->join('training_batches b','b.course_id=c.id')->where('b.id',(int)$batch_id)->get()->row();
        if (!$course) return NULL;
        $course->fee_options=$this->options($course->id,TRUE);
        return $course;
    }

    public function validate_configuration(&$data)
    {
        if (!in_array($data['fee_mode'],array('fixed','options'),TRUE)) return 'รูปแบบค่าสมัครไม่ถูกต้อง';
        $base=training_money_cents($data['fee']);
        if ($base===FALSE) return 'ค่าสมัครต้องเป็นจำนวนเงินตั้งแต่ 0 บาท ทศนิยมไม่เกินสองตำแหน่ง';
        if (!is_array($data['fee_options'])) return 'รายการเงื่อนไขราคาไม่ถูกต้อง';
        $normalized=array(); $labels=array(); $ids=array(); $active=array();
        foreach ($data['fee_options'] as $row) {
            if (!is_array($row) || !isset($row['label'],$row['amount']) || !is_scalar($row['label'])) return 'รายการเงื่อนไขราคาไม่ถูกต้อง';
            $label=trim((string)$row['label']); $cents=training_money_cents($row['amount']);
            $id=isset($row['id'])?filter_var($row['id'],FILTER_VALIDATE_INT,array('options'=>array('min_range'=>0))):0;
            if ($id===FALSE || ($id && isset($ids[$id]))) return 'รหัสเงื่อนไขราคาไม่ถูกต้อง';
            if ($label==='' || mb_strlen($label,'UTF-8')>255 || $cents===FALSE) return 'กรุณาระบุชื่อเงื่อนไขไม่เกิน 255 ตัวอักษร และราคาที่ถูกต้อง';
            $key=mb_strtolower($label,'UTF-8');
            if (isset($labels[$key])) return 'ชื่อเงื่อนไขราคาต้องไม่ซ้ำกัน';
            $labels[$key]=TRUE; $ids[$id]=TRUE;
            $enabled=isset($row['is_active']) && (string)$row['is_active']==='1';
            $normalized[]=array('id'=>(int)$id,'label'=>$label,'amount'=>training_money_decimal($cents),'is_active'=>$enabled?1:0,'sort_order'=>count($normalized));
            if ($enabled) $active[]=$cents;
        }
        if ($data['fee_mode']==='options' && !$active) return 'กรุณาเพิ่มเงื่อนไขราคาที่เปิดใช้งานอย่างน้อยหนึ่งรายการ';
        $data['fee_options']=$normalized;
        $data['fee']=training_money_decimal($data['fee_mode']==='options'?min($active):$base);
        return '';
    }

    /** Called inside the course save transaction. Omitted saved options are archived. */
    public function save_options($course_id,$rows)
    {
        $existing=$this->options($course_id); $owned=array_column($existing,'id');
        foreach($rows as $row) if($row['id'] && !in_array($row['id'],$owned)) return FALSE;
        if (!$this->db->where('course_id',(int)$course_id)->update('training_course_fee_options',array('is_active'=>0))) return FALSE;
        foreach($rows as $row) {
            $id=$row['id']; unset($row['id']); $row['course_id']=(int)$course_id;
            $ok=$id?$this->db->where('id',$id)->where('course_id',(int)$course_id)->update('training_course_fee_options',$row):$this->db->insert('training_course_fee_options',$row);
            if (!$ok) return FALSE;
        }
        $labels=array();
        foreach($this->options($course_id) as $row) {
            $key=mb_strtolower(trim($row['label']),'UTF-8');
            if (isset($labels[$key])) return FALSE;
            $labels[$key]=TRUE;
        }
        return TRUE;
    }

    /** Snapshot and registration are committed together by Batch_model. */
    public function snapshot_registration($registration_id,$batch_id,$option_id=NULL,$revision=NULL)
    {
        $registered=$this->db->query('SELECT unit_fee_snapshot FROM training_registrations WHERE id=? FOR UPDATE',array((int)$registration_id))->row();
        if ($registered && $registered->unit_fee_snapshot!==NULL) return TRUE;
        $course=$this->db->query('SELECT * FROM training_courses WHERE id=(SELECT course_id FROM training_batches WHERE id=?) FOR UPDATE',array((int)$batch_id))->row();
        if (!$course || !in_array($course->fee_mode,array('fixed','options'),TRUE)) return FALSE;
        $choices=$course->fee_mode==='options'?$this->options($course->id,TRUE):array(array('id'=>0,'label'=>'ราคาปกติ','amount'=>$course->fee));
        $selected=NULL;
        if ($course->fee_mode==='options' && (!is_scalar($option_id) || !is_scalar($revision) || (string)$revision!==(string)$course->pricing_revision)) return FALSE;
        foreach($choices as $row) if($course->fee_mode==='fixed' || (string)$row['id']===(string)$option_id) $selected=$row;
        if (!$selected) return FALSE;
        return $this->db->where('id',(int)$registration_id)->update('training_registrations',array(
            'fee_mode_snapshot'=>$course->fee_mode,'fee_options_snapshot'=>json_encode($choices,JSON_UNESCAPED_UNICODE),
            'fee_option_id'=>$selected['id'],'fee_label_snapshot'=>$selected['label'],'unit_fee_snapshot'=>$selected['amount']
        ));
    }

    public function is_locked($registration_id,$status=0)
    {
        if (in_array((int)$status,array(4,5),TRUE)) return TRUE;
        if ($this->db->table_exists('training_payments')) {
            $payments=$this->db->query("SELECT id FROM training_payments WHERE registration_id=? AND (COALESCE(payment_slip,'')<>'' OR status IN (2,3)) LIMIT 1",array((int)$registration_id));
            if (!$payments || $payments->num_rows()) return TRUE;
        }
        if ($this->db->table_exists('training_survey_invitations')) {
            $confirmed=$this->db->query('SELECT p.id FROM training_registration_participants p JOIN training_survey_invitations i ON i.participant_id=p.id WHERE p.registration_id=? AND (i.profile_confirmed_at IS NOT NULL OR i.completed_at IS NOT NULL) LIMIT 1',array((int)$registration_id));
            if (!$confirmed || $confirmed->num_rows()) return TRUE;
        }
        if ($this->db->table_exists('training_certificates')) {
            $issued=$this->db->query('SELECT p.id FROM training_registration_participants p JOIN training_certificates ct ON ct.participant_id=p.id AND ct.status=1 WHERE p.registration_id=? LIMIT 1',array((int)$registration_id));
            if (!$issued || $issued->num_rows()) return TRUE;
        }
        return FALSE;
    }

    public function change_selection($registration_id,$member_id,$option_id)
    {
        $debug=$this->db->db_debug; $this->db->db_debug=FALSE;
        if (!$this->db->trans_begin()) { $this->db->db_debug=$debug; return FALSE; }
        try {
            $result=$this->db->query('SELECT * FROM training_registrations WHERE id=? AND member_id=? FOR UPDATE',array((int)$registration_id,(int)$member_id));
            $registration=$result?$result->row():NULL;
            if (!$registration || $registration->fee_mode_snapshot!=='options') throw new RuntimeException('registration');
            if ($this->db->table_exists('training_payments') && !$this->db->query('SELECT id FROM training_payments WHERE registration_id=? FOR UPDATE',array((int)$registration_id))) throw new RuntimeException('payments');
            if ($this->is_locked($registration_id,$registration->status)) throw new RuntimeException('locked');
            $choices=json_decode($registration->fee_options_snapshot,TRUE); $selected=NULL;
            foreach((array)$choices as $row) if ((string)$row['id']===(string)$option_id) $selected=$row;
            if (!$selected) throw new RuntimeException('option');
            if (!$this->db->where('id',(int)$registration_id)->update('training_registrations',array('fee_option_id'=>$selected['id'],'fee_label_snapshot'=>$selected['label'],'unit_fee_snapshot'=>$selected['amount'],'updated_at'=>date('Y-m-d H:i:s')))) throw new RuntimeException('save');
            $this->load->model('Batch_model');
            $this->Batch_model->sync_registration_payment($registration_id);
            if (!$this->db->trans_status() || !$this->db->trans_commit()) throw new RuntimeException('commit');
            $this->db->db_debug=$debug; return TRUE;
        } catch (Throwable $error) {
            $this->db->trans_rollback(); $this->db->db_debug=$debug;
            return FALSE;
        }
    }
}
