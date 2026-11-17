<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
    }

    private function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS training_certificate_templates (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            batch_id INT UNSIGNED NOT NULL,
            background_path VARCHAR(255) NOT NULL,
            name_x DECIMAL(6,3) NOT NULL DEFAULT 10.000,
            name_y DECIMAL(6,3) NOT NULL DEFAULT 45.000,
            name_width DECIMAL(6,3) NOT NULL DEFAULT 80.000,
            font_size DECIMAL(6,2) NOT NULL DEFAULT 30.00,
            font_color VARCHAR(7) NOT NULL DEFAULT '#172033',
            created_at DATETIME NULL, updated_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY uq_certificate_template_batch (batch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->query("CREATE TABLE IF NOT EXISTS training_certificates (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            batch_id INT UNSIGNED NOT NULL,
            participant_id INT UNSIGNED NOT NULL,
            invitation_id INT UNSIGNED NOT NULL,
            certificate_no VARCHAR(30) NULL,
            active_key VARCHAR(60) NULL,
            recipient_name VARCHAR(255) NOT NULL,
            issued_by INT UNSIGNED NULL,
            issued_at DATETIME NOT NULL,
            status TINYINT NOT NULL DEFAULT 1,
            cancelled_at DATETIME NULL,
            download_count INT UNSIGNED NOT NULL DEFAULT 0,
            last_downloaded_at DATETIME NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY uq_certificate_no (certificate_no), UNIQUE KEY uq_certificate_active (active_key),
            KEY idx_certificate_batch_participant (batch_id,participant_id), KEY idx_certificate_invitation (invitation_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if(!$this->db->field_exists('active_key','training_certificates'))
            $this->db->query("ALTER TABLE training_certificates ADD active_key VARCHAR(60) NULL AFTER certificate_no, ADD UNIQUE KEY uq_certificate_active (active_key)");
        $columns = array(
            'number_x'=>"DECIMAL(6,3) NOT NULL DEFAULT 70.707",
            'number_y'=>"DECIMAL(6,3) NOT NULL DEFAULT 93.333",
            'number_width'=>"DECIMAL(6,3) NOT NULL DEFAULT 25.253",
            'number_font_size'=>"DECIMAL(6,2) NOT NULL DEFAULT 9.00",
            'number_font_color'=>"VARCHAR(7) NOT NULL DEFAULT '#282828'",
            'number_align'=>"CHAR(1) NOT NULL DEFAULT 'R'"
        );
        foreach ($columns as $column=>$definition) {
            if (!$this->db->field_exists($column, 'training_certificate_templates'))
                $this->db->query('ALTER TABLE training_certificate_templates ADD COLUMN IF NOT EXISTS '.$column.' '.$definition);
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS training_certificate_sequences (
            certificate_year SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
            last_sequence INT UNSIGNED NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function get_batches()
    {
        return $this->db->select('b.id,b.batch_no,b.start_date,b.end_date,c.title AS course_title')
            ->from('training_batches b')->join('training_courses c','c.id=b.course_id')
            ->order_by('b.start_date','DESC')->order_by('b.id','DESC')->get()->result();
    }

    public function get_batch($id)
    {
        return $this->db->select('b.*,c.title AS course_title')->from('training_batches b')
            ->join('training_courses c','c.id=b.course_id')->where('b.id',(int)$id)->get()->row();
    }

    public function get_template($batch_id)
    {
        return $this->db->where('batch_id',(int)$batch_id)->get('training_certificate_templates')->row();
    }

    public function save_template($batch_id, $data)
    {
        $existing=$this->get_template($batch_id); $now=date('Y-m-d H:i:s');
        $row=array('background_path'=>$data['background_path'],'name_x'=>$data['name_x'],'name_y'=>$data['name_y'],
            'name_width'=>$data['name_width'],'font_size'=>$data['font_size'],'font_color'=>$data['font_color'],'updated_at'=>$now);
        foreach (certificate_number_defaults() as $key=>$default) $row[$key]=isset($data[$key])?$data[$key]:$default;
        if($existing) return $this->db->where('id',$existing->id)->update('training_certificate_templates',$row);
        $row['batch_id']=(int)$batch_id; $row['created_at']=$now;
        return $this->db->insert('training_certificate_templates',$row);
    }

    public function get_roster($batch_id)
    {
        return $this->db->select('p.id AS participant_id,p.title_name,p.first_name,p.last_name,p.email,r.status AS registration_status')
            ->select('i.id AS invitation_id,i.profile_confirmed_at,i.completed_at')
            ->select('ct.id AS certificate_id,ct.certificate_no,ct.recipient_name,ct.status AS certificate_status')
            ->from('training_registration_participants p')->join('training_registrations r','r.id=p.registration_id')
            ->join('training_survey_invitations i','i.id=(SELECT MAX(i2.id) FROM training_survey_invitations i2 INNER JOIN training_survey_assignments sa2 ON sa2.id=i2.assignment_id WHERE i2.participant_id=p.id AND sa2.batch_id=r.batch_id)','left',FALSE)
            ->join('training_certificates ct','ct.batch_id=r.batch_id AND ct.participant_id=p.id AND ct.status=1','left')
            ->where('r.batch_id',(int)$batch_id)->where('r.status !=',4)->where('p.status',1)
            ->order_by('p.first_name')->order_by('p.last_name')->get()->result();
    }

    public function get_eligible($batch_id, $participant_id, $invitation_id)
    {
        return $this->db->select('p.id AS participant_id,p.title_name,p.first_name,p.last_name,i.id AS invitation_id,r.status AS registration_status')
            ->select('i.profile_confirmed_at,i.completed_at')->from('training_registration_participants p')
            ->join('training_registrations r','r.id=p.registration_id')->join('training_survey_invitations i','i.participant_id=p.id')
            ->join('training_survey_assignments sa','sa.id=i.assignment_id AND sa.batch_id=r.batch_id')
            ->where('r.batch_id',(int)$batch_id)->where('p.id',(int)$participant_id)->where('i.id',(int)$invitation_id)
            ->where('r.status',5)->where('i.profile_confirmed_at IS NOT NULL',NULL,FALSE)
            ->where('i.completed_at IS NOT NULL',NULL,FALSE)->get()->row();
    }

    public $last_issue_error = '';

    protected function issue_time()
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
    }

    public function issue($batch_id, $participant_id, $invitation_id, $admin_id)
    {
        $this->last_issue_error = '';
        if(!$this->get_template($batch_id)) return FALSE;
        $person=$this->get_eligible($batch_id,$participant_id,$invitation_id); if(!$person) return FALSE;
        $time=$this->issue_time()->setTimezone(new DateTimeZone('Asia/Bangkok'));
        $year=(int)$time->format('Y')+543; $now=$time->format('Y-m-d H:i:s');
        $debug=$this->db->db_debug; $this->db->db_debug=FALSE;
        if (!$this->db->trans_begin()) { $this->db->db_debug=$debug; return FALSE; }
        try {
            // A single row per year serializes both single and bulk issuance.
            if (!$this->db->query('INSERT INTO training_certificate_sequences (certificate_year,last_sequence) VALUES (?,0) ON DUPLICATE KEY UPDATE certificate_year=VALUES(certificate_year)',array($year))) throw new RuntimeException('counter');
            $counter=$this->db->query('SELECT last_sequence FROM training_certificate_sequences WHERE certificate_year=? FOR UPDATE',array($year));
            if (!$counter || !$counter->num_rows()) throw new RuntimeException('counter lock');
            $last=(int)$counter->row()->last_sequence;
            $existing=$this->db->query('SELECT id FROM training_certificates WHERE batch_id=? AND participant_id=? AND status=1 FOR UPDATE',array((int)$batch_id,(int)$participant_id));
            if (!$existing) throw new RuntimeException('existing certificate');
            if ($existing->num_rows()) {
                $this->db->trans_rollback(); $this->db->db_debug=$debug; return TRUE;
            }
            if ($last===0) {
                $seed=$this->db->query('SELECT COALESCE(MAX(CAST(RIGHT(certificate_no,6) AS UNSIGNED)),0) AS last_sequence FROM training_certificates WHERE certificate_no REGEXP ?',array('^SCI-'.$year.'-[0-9]{6}$'));
                if (!$seed) throw new RuntimeException('counter seed');
                $last=(int)$seed->row()->last_sequence;
            }
            if ($last>=999999) {
                $this->last_issue_error='เลขวุฒิบัตรประจำปีเต็ม 999,999 รายการแล้ว';
                throw new RuntimeException('sequence exhausted');
            }
            $next=$last+1; $number='SCI-'.$year.'-'.str_pad((string)$next,6,'0',STR_PAD_LEFT);
            if (!$this->db->where('certificate_year',$year)->update('training_certificate_sequences',array('last_sequence'=>$next))) throw new RuntimeException('counter update');
            $saved=$this->db->insert('training_certificates',array('batch_id'=>(int)$batch_id,'participant_id'=>(int)$participant_id,
                'invitation_id'=>(int)$invitation_id,'active_key'=>(int)$batch_id.'-'.(int)$participant_id,'certificate_no'=>$number,
                'recipient_name'=>trim($person->title_name.$person->first_name.' '.$person->last_name),
                'issued_by'=>$admin_id===NULL?NULL:(int)$admin_id,'issued_at'=>$now,'status'=>1,'created_at'=>$now,'updated_at'=>$now));
            if (!$saved || !$this->db->trans_status() || !$this->db->trans_commit()) throw new RuntimeException('certificate commit');
            $this->db->db_debug=$debug; return TRUE;
        } catch (Exception $error) {
            $this->db->trans_rollback(); $this->db->db_debug=$debug;
            log_message('error','Certificate issuance failed: '.$error->getMessage());
            return FALSE;
        }
    }

    public function issue_batch($batch_id, $admin_id)
    {
        $this->last_issue_error='';
        $issued=0; foreach($this->get_roster($batch_id) as $row) {
            if((int)$row->registration_status===5 && $row->profile_confirmed_at && $row->completed_at && !$row->certificate_id
                && $this->issue($batch_id,$row->participant_id,$row->invitation_id,$admin_id)) $issued++;
            if ($this->last_issue_error) break;
        } return $issued;
    }

    public function cancel($id)
    {
        return $this->db->where('id',(int)$id)->where('status',1)->update('training_certificates',array('status'=>2,'active_key'=>NULL,'cancelled_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
    }

    public function get_active_by_invitation($invitation_id)
    {
        return $this->db->select('ct.*,t.background_path,t.name_x,t.name_y,t.name_width,t.font_size,t.font_color,t.number_x,t.number_y,t.number_width,t.number_font_size,t.number_font_color,t.number_align')
            ->from('training_certificates ct')->join('training_certificate_templates t','t.batch_id=ct.batch_id')
            ->where('ct.invitation_id',(int)$invitation_id)->where('ct.status',1)->order_by('ct.id','DESC')->get()->row();
    }

    public function get_active($id)
    {
        return $this->db->select('ct.*,t.background_path,t.name_x,t.name_y,t.name_width,t.font_size,t.font_color,t.number_x,t.number_y,t.number_width,t.number_font_size,t.number_font_color,t.number_align')
            ->select('p.email,c.title AS course_title,b.batch_no')
            ->from('training_certificates ct')->join('training_certificate_templates t','t.batch_id=ct.batch_id')
            ->join('training_registration_participants p','p.id=ct.participant_id')
            ->join('training_batches b','b.id=ct.batch_id')->join('training_courses c','c.id=b.course_id')
            ->where('ct.id',(int)$id)->where('ct.status',1)->get()->row();
    }

    public function get_active_by_batch($batch_id)
    {
        return $this->db->select('ct.*,t.background_path,t.name_x,t.name_y,t.name_width,t.font_size,t.font_color,t.number_x,t.number_y,t.number_width,t.number_font_size,t.number_font_color,t.number_align')
            ->select('p.email,c.title AS course_title,b.batch_no')
            ->from('training_certificates ct')->join('training_certificate_templates t','t.batch_id=ct.batch_id')
            ->join('training_registration_participants p','p.id=ct.participant_id')
            ->join('training_batches b','b.id=ct.batch_id')->join('training_courses c','c.id=b.course_id')
            ->where('ct.batch_id',(int)$batch_id)->where('ct.status',1)->order_by('ct.id')->get()->result();
    }

    public function get_registration_states($registration_id, $batch_id)
    {
        $rows=$this->db->select('p.id AS participant_id,r.status AS registration_status')
            ->select('i.id AS invitation_id,i.profile_confirmed_at,i.completed_at')
            ->select('ct.id AS certificate_id,ct.certificate_no')
            ->select('CASE WHEN t.id IS NULL THEN 0 ELSE 1 END AS has_template',FALSE)
            ->from('training_registration_participants p')->join('training_registrations r','r.id=p.registration_id')
            ->join('training_survey_invitations i','i.id=(SELECT MAX(i2.id) FROM training_survey_invitations i2 INNER JOIN training_survey_assignments sa2 ON sa2.id=i2.assignment_id WHERE i2.participant_id=p.id AND sa2.batch_id=r.batch_id)','left',FALSE)
            ->join('training_certificate_templates t','t.batch_id=r.batch_id','left')
            ->join('training_certificates ct','ct.batch_id=r.batch_id AND ct.participant_id=p.id AND ct.status=1','left')
            ->where('p.registration_id',(int)$registration_id)->where('r.batch_id',(int)$batch_id)->where('p.status',1)
            ->order_by('p.id')->get()->result();
        $states=array(); foreach($rows as $row)$states[(int)$row->participant_id]=$row; return $states;
    }

    public function get_owned_certificate($registration_id, $participant_id)
    {
        return $this->db->select('ct.*,t.background_path,t.name_x,t.name_y,t.name_width,t.font_size,t.font_color,t.number_x,t.number_y,t.number_width,t.number_font_size,t.number_font_color,t.number_align')
            ->select('r.status AS registration_status,i.profile_confirmed_at,i.completed_at')
            ->from('training_certificates ct')->join('training_certificate_templates t','t.batch_id=ct.batch_id')
            ->join('training_registration_participants p','p.id=ct.participant_id')
            ->join('training_registrations r','r.id=p.registration_id AND r.batch_id=ct.batch_id')
            ->join('training_survey_invitations i','i.id=ct.invitation_id')
            ->where('r.id',(int)$registration_id)->where('p.id',(int)$participant_id)->where('ct.status',1)
            ->where('r.status',5)->where('i.profile_confirmed_at IS NOT NULL',NULL,FALSE)
            ->where('i.completed_at IS NOT NULL',NULL,FALSE)->get()->row();
    }

    public function participant_is_locked($registration_id, $participant_id)
    {
        return $this->db->from('training_registration_participants p')
            ->join('training_registrations r','r.id=p.registration_id')
            ->join('training_survey_invitations i','i.participant_id=p.id','left')
            ->join('training_certificates ct','ct.participant_id=p.id AND ct.batch_id=r.batch_id AND ct.status=1','left')
            ->where('p.registration_id',(int)$registration_id)->where('p.id',(int)$participant_id)
            ->group_start()->where('i.profile_confirmed_at IS NOT NULL',NULL,FALSE)->or_where('i.completed_at IS NOT NULL',NULL,FALSE)->or_where('ct.id IS NOT NULL',NULL,FALSE)->group_end()
            ->count_all_results()>0;
    }

    public function record_download($id)
    {
        $this->db->set('download_count','download_count+1',FALSE)->set('last_downloaded_at',date('Y-m-d H:i:s'))->where('id',(int)$id)->update('training_certificates');
    }
}
