<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Survey_model extends CI_Model
{
    private $types = array('rating', 'single_choice', 'multiple_choice', 'short_text', 'long_text');

    public function tables_ready()
    {
        return $this->db->table_exists('training_surveys')
            && $this->db->table_exists('training_survey_assignments')
            && $this->db->table_exists('training_survey_invitations');
    }

    public function get_all()
    {
        if (!$this->tables_ready()) return array();
        return $this->db->select('training_surveys.*')
            ->select('(SELECT COUNT(*) FROM training_survey_questions q WHERE q.survey_id = training_surveys.id) AS question_count', FALSE)
            ->select('(SELECT COUNT(*) FROM training_survey_assignments a WHERE a.survey_id = training_surveys.id) AS assignment_count', FALSE)
            ->order_by('id', 'DESC')->get('training_surveys')->result();
    }

    public function get($id)
    {
        if (!$this->tables_ready()) return NULL;
        return $this->db->where('id', (int) $id)->get('training_surveys')->row();
    }

    public function save_survey($id, $data)
    {
        $now = date('Y-m-d H:i:s');
        $row = array('title' => $data['title'], 'description' => $data['description'], 'status' => (int) $data['status'], 'updated_at' => $now);
        if ((int) $id > 0) return $this->db->where('id', (int) $id)->update('training_surveys', $row);
        $row['created_at'] = $now;
        return $this->db->insert('training_surveys', $row) ? $this->db->insert_id() : FALSE;
    }

    public function get_questions($survey_id)
    {
        if (!$this->tables_ready()) return array();
        $questions = $this->db->where('survey_id', (int) $survey_id)->order_by('sort_order')->order_by('id')->get('training_survey_questions')->result();
        foreach ($questions as $question) {
            $question->options = $this->db->where('question_id', $question->id)->order_by('sort_order')->order_by('id')->get('training_survey_options')->result();
        }
        return $questions;
    }

    public function has_responses($survey_id)
    {
        return (int) $this->db->from('training_survey_responses r')->join('training_survey_invitations i', 'i.id=r.invitation_id')->join('training_survey_assignments a', 'a.id=i.assignment_id')->where('a.survey_id', (int) $survey_id)->count_all_results() > 0;
    }

    public function add_question($survey_id, $data)
    {
        if (!in_array($data['question_type'], $this->types, TRUE)) return FALSE;
        $max = $this->db->select_max('sort_order')->where('survey_id', (int) $survey_id)->get('training_survey_questions')->row();
        $this->db->trans_start();
        $this->db->insert('training_survey_questions', array(
            'survey_id' => (int) $survey_id, 'question_text' => $data['question_text'], 'question_type' => $data['question_type'],
            'is_required' => (int) $data['is_required'], 'sort_order' => ((int) $max->sort_order) + 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ));
        $question_id = $this->db->insert_id();
        foreach ($data['options'] as $index => $option) {
            if ($option !== '') $this->db->insert('training_survey_options', array('question_id' => $question_id, 'option_text' => $option, 'sort_order' => $index + 1));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_question($survey_id, $question_id)
    {
        if ($this->has_responses($survey_id)) return FALSE;
        return $this->db->where('survey_id', (int) $survey_id)->where('id', (int) $question_id)->delete('training_survey_questions');
    }

    public function move_question($survey_id, $question_id, $direction)
    {
        if ($this->has_responses($survey_id)) return FALSE;
        $items = $this->db->select('id')->where('survey_id', (int) $survey_id)->order_by('sort_order')->order_by('id')->get('training_survey_questions')->result();
        $ids = array_map(function ($row) { return (int) $row->id; }, $items);
        $index = array_search((int) $question_id, $ids, TRUE);
        if ($index === FALSE) return FALSE;
        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        if (!isset($ids[$swap])) return TRUE;
        $tmp = $ids[$index]; $ids[$index] = $ids[$swap]; $ids[$swap] = $tmp;
        foreach ($ids as $order => $id) $this->db->where('id', $id)->update('training_survey_questions', array('sort_order' => $order + 1));
        return TRUE;
    }

    public function get_courses()
    {
        return $this->db->select('id,title')->order_by('title')->get('training_courses')->result();
    }

    public function get_batches($course_id = 0)
    {
        $this->db->select('b.id,b.course_id,b.batch_no,b.start_date,b.end_date,c.title AS course_title')->from('training_batches b')->join('training_courses c', 'c.id=b.course_id');
        if ((int) $course_id > 0) $this->db->where('b.course_id', (int) $course_id);
        return $this->db->order_by('b.start_date', 'DESC')->get()->result();
    }

    public function get_assignments($survey_id = 0)
    {
        if (!$this->tables_ready()) return array();
        $this->db->select('a.*,s.title AS survey_title,c.title AS course_title,b.batch_no,b.start_date,b.end_date')
            ->select('COUNT(DISTINCT i.id) AS eligible_count', FALSE)
            ->select('COUNT(DISTINCT CASE WHEN i.completed_at IS NOT NULL THEN i.id END) AS completed_count', FALSE)
            ->from('training_survey_assignments a')->join('training_surveys s', 's.id=a.survey_id')->join('training_courses c', 'c.id=a.course_id')->join('training_batches b', 'b.id=a.batch_id')->join('training_survey_invitations i', 'i.assignment_id=a.id', 'left');
        if ((int) $survey_id > 0) $this->db->where('a.survey_id', (int) $survey_id);
        return $this->db->group_by('a.id')->order_by('a.id', 'DESC')->get()->result();
    }

    public function get_assignment($id)
    {
        return $this->db->select('a.*,s.title AS survey_title,s.description,c.title AS course_title,b.batch_no,b.start_date,b.end_date')
            ->from('training_survey_assignments a')->join('training_surveys s', 's.id=a.survey_id')->join('training_courses c', 'c.id=a.course_id')->join('training_batches b', 'b.id=a.batch_id')->where('a.id', (int) $id)->get()->row();
    }

    public function get_batch_assignment($batch_id)
    {
        return $this->db->select('a.*,s.title AS survey_title,c.title AS course_title,b.batch_no,b.start_date,b.end_date,b.end_time')
            ->from('training_survey_assignments a')->join('training_surveys s','s.id=a.survey_id')
            ->join('training_courses c','c.id=a.course_id')->join('training_batches b','b.id=a.batch_id')
            ->where('a.batch_id',(int)$batch_id)->order_by('a.id','DESC')->get()->row();
    }

    public function assignment_has_responses($assignment_id)
    {
        return $this->db->where('assignment_id',(int)$assignment_id)->where('completed_at IS NOT NULL',NULL,FALSE)
            ->count_all_results('training_survey_invitations') > 0;
    }

    public function update_batch_assignment($id, $survey_id, $open_at, $close_at)
    {
        return $this->db->where('id',(int)$id)->update('training_survey_assignments',array(
            'survey_id'=>(int)$survey_id,'open_at'=>$open_at,'close_at'=>$close_at,'status'=>1,'updated_at'=>date('Y-m-d H:i:s')
        ));
    }

    public function sync_invitations($assignment_id)
    {
        $assignment=$this->db->where('id',(int)$assignment_id)->get('training_survey_assignments')->row(); if(!$assignment)return 0;
        $lock='survey_sync_'.(int)$assignment_id; $locked=$this->db->query('SELECT GET_LOCK(?,5) AS acquired',array($lock))->row();
        if(!$locked || (int)$locked->acquired!==1)return 0; $now=date('Y-m-d H:i:s');
        $this->db->query('INSERT INTO training_survey_invitations (assignment_id,participant_id,created_at,updated_at)
            SELECT ?,p.id,?,? FROM training_registration_participants p
            INNER JOIN training_registrations r ON r.id=p.registration_id
            WHERE r.batch_id=? AND r.status!=4 AND p.status=1
            AND NOT EXISTS (SELECT 1 FROM training_survey_invitations i WHERE i.assignment_id=? AND i.participant_id=p.id)',
            array((int)$assignment_id,$now,$now,(int)$assignment->batch_id,(int)$assignment_id));
        $affected=(int)$this->db->affected_rows(); $this->db->query('SELECT RELEASE_LOCK(?)',array($lock)); return $affected;
    }

    public function get_assignment_stats($id)
    {
        return $this->db->select('COUNT(*) AS eligible_count', FALSE)
            ->select('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed_count', FALSE)
            ->where('assignment_id', (int) $id)
            ->get('training_survey_invitations')->row();
    }

    public function get_assignment_by_code($code)
    {
        return $this->db->select('a.*,s.title AS survey_title,s.description,c.title AS course_title,b.batch_no,b.start_date,b.end_date')
            ->from('training_survey_assignments a')->join('training_surveys s', 's.id=a.survey_id')->join('training_courses c', 'c.id=a.course_id')->join('training_batches b', 'b.id=a.batch_id')->where('a.public_code', $code)->get()->row();
    }

    public function active_assignment_exists($batch_id, $exclude_id = 0)
    {
        $this->db->where('batch_id', (int) $batch_id)->where('status', 1);
        if ($exclude_id) $this->db->where('id !=', (int) $exclude_id);
        return $this->db->count_all_results('training_survey_assignments') > 0;
    }

    public function create_assignment($data)
    {
        $batch = $this->db->where('id', (int) $data['batch_id'])->where('course_id', (int) $data['course_id'])->get('training_batches')->row();
        if (!$batch || $this->active_assignment_exists($data['batch_id'])) return FALSE;
        $this->db->trans_start();
        $this->db->insert('training_survey_assignments', array(
            'survey_id' => (int) $data['survey_id'], 'course_id' => (int) $data['course_id'], 'batch_id' => (int) $data['batch_id'],
            'public_code' => bin2hex(random_bytes(32)), 'open_at' => $data['open_at'], 'close_at' => $data['close_at'], 'status' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ));
        $assignment_id = $this->db->insert_id();
        // Build the complete roster in one query; a per-participant INSERT loop
        // becomes very slow for large training batches.
        $now = date('Y-m-d H:i:s');
        $this->db->query(
            'INSERT INTO training_survey_invitations (assignment_id, participant_id, created_at, updated_at)
             SELECT ?, p.id, ?, ?
             FROM training_registration_participants p
             INNER JOIN training_registrations r ON r.id = p.registration_id
             WHERE r.batch_id = ? AND r.status != 4 AND p.status = 1',
            array($assignment_id, $now, $now, (int) $data['batch_id'])
        );
        $this->db->where('id', (int) $data['survey_id'])->update('training_surveys', array('status' => 2, 'updated_at' => date('Y-m-d H:i:s')));
        $this->db->trans_complete();
        return $this->db->trans_status() ? $assignment_id : FALSE;
    }

    public function close_assignment($id) { return $this->db->where('id', (int) $id)->update('training_survey_assignments', array('status' => 2, 'updated_at' => date('Y-m-d H:i:s'))); }
    public function regenerate_code($id) { $code = bin2hex(random_bytes(32)); return $this->db->where('id', (int) $id)->update('training_survey_assignments', array('public_code' => $code, 'updated_at' => date('Y-m-d H:i:s'))) ? $code : FALSE; }

    public function search_participants($assignment_id, $query)
    {
        $query = trim($query); if (mb_strlen($query, 'UTF-8') < 2) return array();
        return $this->db->select('i.id AS invitation_id,p.first_name,p.last_name,p.email')
            ->from('training_survey_invitations i')->join('training_registration_participants p', 'p.id=i.participant_id')->where('i.assignment_id', (int) $assignment_id)
            ->group_start()->like('p.first_name', $query)->or_like('p.last_name', $query)->or_like('p.email', $query)->group_end()->limit(10)->get()->result();
    }

    public function get_invitation($assignment_id, $invitation_id)
    {
        return $this->db->select('p.*,i.*,r.status AS registration_status')->from('training_survey_invitations i')->join('training_registration_participants p', 'p.id=i.participant_id')->join('training_registrations r', 'r.id=p.registration_id')->where('i.assignment_id', (int) $assignment_id)->where('i.id', (int) $invitation_id)->get()->row();
    }

    public function get_invitation_roster($assignment_id)
    {
        return $this->db->select('i.completed_at,i.profile_confirmed_at,p.first_name,p.last_name,p.email')
            ->from('training_survey_invitations i')
            ->join('training_registration_participants p', 'p.id=i.participant_id')
            ->where('i.assignment_id', (int) $assignment_id)
            ->order_by('i.completed_at IS NULL', 'DESC', FALSE)
            ->order_by('p.first_name', 'ASC')
            ->get()->result();
    }

    public function verify_invitation($invitation, $email, $phone4)
    {
        if (!empty($invitation->verify_locked_until) && strtotime($invitation->verify_locked_until) > time()) return FALSE;
        $email = trim((string) $email);
        $phone4 = preg_replace('/\D+/', '', (string) $phone4);
        $phone = preg_replace('/\D+/', '', (string) $invitation->phone);
        $email_valid = $email !== '' && trim((string) $invitation->email) !== ''
            && strtolower($email) === strtolower(trim($invitation->email));
        $phone_valid = strlen($phone4) === 4 && strlen($phone) >= 4
            && substr($phone, -4) === $phone4;
        $valid = $email_valid || $phone_valid;
        if ($valid) { $this->db->where('id', $invitation->id)->update('training_survey_invitations', array('verify_attempts' => 0, 'verify_locked_until' => NULL)); return TRUE; }
        $attempts = (int) $invitation->verify_attempts + 1;
        $this->db->where('id', $invitation->id)->update('training_survey_invitations', array('verify_attempts' => $attempts, 'verify_locked_until' => $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : NULL));
        return FALSE;
    }

    public function update_profile($invitation, $data, $ip)
    {
        $before = array('title_name'=>$invitation->title_name,'first_name'=>$invitation->first_name,'last_name'=>$invitation->last_name,'email'=>$invitation->email,'phone'=>$invitation->phone,'school_name'=>$invitation->school_name);
        $this->db->trans_start();
        $this->db->where('id', $invitation->participant_id)->update('training_registration_participants', $data + array('updated_at' => date('Y-m-d H:i:s')));
        $this->db->where('id', $invitation->id)->update('training_survey_invitations', array('profile_confirmed_at'=>NULL,'updated_at'=>date('Y-m-d H:i:s')));
        $this->db->insert('training_survey_profile_audits', array('invitation_id'=>$invitation->id,'participant_id'=>$invitation->participant_id,'before_data'=>json_encode($before, JSON_UNESCAPED_UNICODE),'after_data'=>json_encode($data, JSON_UNESCAPED_UNICODE),'ip_address'=>$ip,'created_at'=>date('Y-m-d H:i:s')));
        $this->db->trans_complete(); return $this->db->trans_status();
    }

    public function confirm_profile($id) { return $this->db->where('id', (int) $id)->update('training_survey_invitations', array('profile_confirmed_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'))); }

    public function submit_response($assignment, $invitation, $posted)
    {
        if (!empty($invitation->completed_at)) return FALSE;
        $questions = $this->get_questions($assignment->survey_id);
        foreach ($questions as $q) {
            if ($q->is_required && (!isset($posted[$q->id]) || $posted[$q->id] === '' || $posted[$q->id] === array())) return FALSE;
            if (!isset($posted[$q->id])) continue;
            if ($q->question_type === 'rating' && (!is_scalar($posted[$q->id]) || (int)$posted[$q->id] < 1 || (int)$posted[$q->id] > 5)) return FALSE;
            if (in_array($q->question_type, array('single_choice','multiple_choice'), TRUE)) {
                $selected = is_array($posted[$q->id]) ? $posted[$q->id] : array($posted[$q->id]);
                $valid = array_map(function($o){ return (int)$o->id; }, $q->options);
                $matched = array_intersect(array_map('intval', $selected), $valid);
                if ($q->is_required && empty($matched)) return FALSE;
            }
        }
        $this->db->trans_start();
        $this->db->insert('training_survey_responses', array('invitation_id'=>$invitation->id,'submitted_at'=>date('Y-m-d H:i:s')));
        $response_id = $this->db->insert_id();
        foreach ($questions as $q) {
            if (!isset($posted[$q->id]) || $posted[$q->id] === '') continue;
            $value = $posted[$q->id]; $row = array('response_id'=>$response_id,'question_id'=>$q->id,'rating_value'=>NULL,'text_value'=>NULL);
            if ($q->question_type === 'rating') $row['rating_value'] = max(1, min(5, (int) $value));
            elseif ($q->question_type === 'short_text' || $q->question_type === 'long_text') $row['text_value'] = trim((string) $value);
            $this->db->insert('training_survey_answers', $row); $answer_id = $this->db->insert_id();
            if ($q->question_type === 'single_choice' || $q->question_type === 'multiple_choice') {
                $values = is_array($value) ? $value : array($value); $valid = array_map(function($o){return (int)$o->id;}, $q->options);
                foreach (array_unique(array_map('intval', $values)) as $option_id) if (in_array($option_id, $valid, TRUE)) $this->db->insert('training_survey_answer_options', array('answer_id'=>$answer_id,'option_id'=>$option_id));
            }
        }
        $this->db->where('id', $invitation->id)->update('training_survey_invitations', array('completed_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
        $this->db->trans_complete(); return $this->db->trans_status();
    }

    public function get_report($assignment_id)
    {
        $assignment = $this->get_assignment($assignment_id); if (!$assignment) return NULL;
        $assignment->eligible_count = (int) $this->db->where('assignment_id', $assignment_id)->count_all_results('training_survey_invitations');
        $assignment->completed_count = (int) $this->db->where('assignment_id', $assignment_id)->where('completed_at IS NOT NULL', NULL, FALSE)->count_all_results('training_survey_invitations');
        $assignment->questions = $this->get_questions($assignment->survey_id);
        foreach ($assignment->questions as $q) {
            $q->answers = $this->db->select('a.*,o.option_id,so.option_text')->from('training_survey_answers a')->join('training_survey_responses r','r.id=a.response_id')->join('training_survey_invitations i','i.id=r.invitation_id')->join('training_survey_answer_options o','o.answer_id=a.id','left')->join('training_survey_options so','so.id=o.option_id','left')->where('i.assignment_id',$assignment_id)->where('a.question_id',$q->id)->get()->result();
        }
        return $assignment;
    }
}
