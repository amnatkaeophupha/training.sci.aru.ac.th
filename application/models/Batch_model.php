<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Batch_model extends CI_Model
{
    private $table = 'training_batches';

    public function get_all()
    {
        return $this->db
            ->select('training_batches.*, training_courses.title AS course_title, training_categories.name AS category_name')
            ->select('(SELECT a.id FROM training_survey_assignments a WHERE a.batch_id=training_batches.id ORDER BY a.id DESC LIMIT 1) AS evaluation_id', FALSE)
            ->select('(SELECT a.open_at FROM training_survey_assignments a WHERE a.batch_id=training_batches.id ORDER BY a.id DESC LIMIT 1) AS evaluation_open_at', FALSE)
            ->select('(SELECT a.close_at FROM training_survey_assignments a WHERE a.batch_id=training_batches.id ORDER BY a.id DESC LIMIT 1) AS evaluation_close_at', FALSE)
            ->select('(SELECT a.status FROM training_survey_assignments a WHERE a.batch_id=training_batches.id ORDER BY a.id DESC LIMIT 1) AS evaluation_status', FALSE)
            ->select('(SELECT COUNT(*) FROM training_certificate_templates t WHERE t.batch_id=training_batches.id) AS certificate_template_count', FALSE)
            ->select('(SELECT COUNT(*) FROM training_certificates ct WHERE ct.batch_id=training_batches.id AND ct.status=1) AS certificate_issued_count', FALSE)
            ->select('(SELECT COUNT(*) FROM training_registration_participants p INNER JOIN training_registrations r ON r.id=p.registration_id WHERE r.batch_id=training_batches.id AND r.status<>4 AND p.status=1) AS participant_count', FALSE)
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'left')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->order_by('training_batches.start_date', 'DESC')
            ->order_by('training_batches.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_frontend_programs($limit = 6)
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return array();
        }

        if ($this->db->table_exists('training_registrations') && $this->db->table_exists('training_registration_participants')) {
            $registration_count_select = '(
                SELECT COUNT(*)
                FROM training_registration_participants
                INNER JOIN training_registrations
                    ON training_registrations.id = training_registration_participants.registration_id
                WHERE training_registrations.batch_id = training_batches.id
                    AND training_registrations.status <> 4
                    AND training_registration_participants.status = 1
            ) AS registered_count';
        } elseif ($this->db->table_exists('training_registrations')) {
            $registration_count_select = '(SELECT COUNT(*) FROM training_registrations WHERE training_registrations.batch_id = training_batches.id AND training_registrations.status <> 4) AS registered_count';
        } else {
            $registration_count_select = '0 AS registered_count';
        }

        return $this->db
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.registration_start, training_batches.registration_end')
            ->select('training_batches.status AS batch_status')
            ->select('IF(training_batches.capacity > 0, training_batches.capacity, training_courses.capacity) AS capacity', FALSE)
            ->select($registration_count_select, FALSE)
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug')
            ->select('training_courses.short_description, training_courses.description')
            ->select('training_courses.cover_image, training_courses.level')
            ->select('training_courses.training_type, training_courses.location, training_courses.duration_text')
            ->select('training_courses.fee, training_courses.status AS course_status')
            ->select('training_courses.is_featured, training_courses.published_at')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_courses.status', 2)
            ->where_in('training_batches.status', array(1, 2, 3))
            ->order_by('FIELD(training_batches.status, 1, 3, 2)', '', FALSE)
            ->order_by('IFNULL(training_batches.start_date, "9999-12-31")', 'ASC', FALSE)
            ->order_by('training_batches.id', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    public function get_frontend_calendar()
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return array();
        }

        return $this->db
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.registration_start, training_batches.registration_end')
            ->select('training_batches.status AS batch_status')
            ->select('IF(training_batches.capacity > 0, training_batches.capacity, training_courses.capacity) AS capacity', FALSE)
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug')
            ->select('training_courses.short_description, training_courses.description')
            ->select('training_courses.cover_image, training_courses.level')
            ->select('training_courses.training_type, training_courses.location, training_courses.duration_text')
            ->select('training_courses.fee, training_courses.status AS course_status')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_courses.status', 2)
            ->where_in('training_batches.status', array(1, 2, 3, 4))
            ->order_by('FIELD(training_batches.status, 1, 3, 2, 4)', '', FALSE)
            ->order_by('IFNULL(training_batches.start_date, "9999-12-31")', 'ASC', FALSE)
            ->order_by('training_batches.id', 'ASC')
            ->get()
            ->result();
    }

    public function get_registered_by_member($member_id)
    {
        if (!$this->db->table_exists('training_registrations') || !$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return array();
        }

        if ($this->db->table_exists('training_registration_participants')) {
            $registration_count_select = '(
                SELECT COUNT(*)
                FROM training_registration_participants
                INNER JOIN training_registrations AS counted_registrations
                    ON counted_registrations.id = training_registration_participants.registration_id
                WHERE counted_registrations.batch_id = training_batches.id
                    AND counted_registrations.status <> 4
                    AND training_registration_participants.status = 1
            ) AS registered_count';
        } else {
            $registration_count_select = '(
                SELECT COUNT(*)
                FROM training_registrations AS counted_registrations
                WHERE counted_registrations.batch_id = training_batches.id
                    AND counted_registrations.status <> 4
            ) AS registered_count';
        }

        $registered_courses = $this->db
            ->select('training_registrations.id AS registration_id')
            ->select('training_registrations.registration_code, training_registrations.status AS registration_status')
            ->select('training_registrations.created_at')
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.status AS batch_status')
            ->select('IF(training_batches.capacity > 0, training_batches.capacity, training_courses.capacity) AS capacity', FALSE)
            ->select($registration_count_select, FALSE)
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug, training_courses.cover_image')
            ->select('training_courses.training_type, training_courses.location, training_courses.fee')
            ->select('training_categories.name AS category_name')
            ->from('training_registrations')
            ->join($this->table, 'training_batches.id = training_registrations.batch_id', 'inner')
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_registrations.member_id', (int) $member_id)
            ->order_by('training_registrations.created_at', 'DESC')
            ->order_by('training_registrations.id', 'DESC')
            ->get()
            ->result();

        return $this->append_registration_payment_summary($registered_courses);
    }

    private function append_registration_payment_summary($courses)
    {
        if (empty($courses)) {
            return $courses;
        }

        foreach ($courses as $course) {
            $participant_count = 1;

            if ($this->participants_table_exists() && !empty($course->registration_id)) {
                $participant_count = (int) $this->db
                    ->where('registration_id', (int) $course->registration_id)
                    ->where('status', 1)
                    ->count_all_results('training_registration_participants');
                $participant_count = max(1, $participant_count);
            }

            $course->participant_count = $participant_count;
            $course->fee_per_person = isset($course->fee) ? (float) $course->fee : 0;
            $course->payment_total = $course->fee_per_person * $participant_count;
            $course->payment_id = 0;
            $course->payment_code = '';
            $course->payment_status = 0;
            $course->payment_slip = '';
            $course->payment_slips = array();
            $course->payment_submitted_amount = 0;
            $course->payment_due_amount = $course->payment_total;
            $course->payment_refund_amount = 0;

            if ($course->payment_total > 0 && !empty($course->registration_id)) {
                $ensure_pending_payment = !isset($course->registration_status) || (int) $course->registration_status !== 4;
                $payment_summary = $this->get_registration_payment_summary($course->registration_id, $course->payment_total, $ensure_pending_payment);

                if ($payment_summary) {
                    $course->payment_id = (int) $payment_summary->payment_id;
                    $course->payment_code = $payment_summary->payment_code;
                    $course->payment_status = (int) $payment_summary->payment_status;
                    $course->payment_slip = $payment_summary->payment_slip;
                    $course->payment_slips = $payment_summary->payment_slips;
                    $course->payment_submitted_amount = (float) $payment_summary->submitted_amount;
                    $course->payment_due_amount = (float) $payment_summary->due_amount;
                    $course->payment_refund_amount = (float) $payment_summary->refund_amount;
                }
            }
        }

        return $courses;
    }

    public function payments_table_exists()
    {
        return $this->db->table_exists('training_payments');
    }

    public function get_registration_payment($registration_id)
    {
        if (!$this->payments_table_exists()) {
            return NULL;
        }

        return $this->db
            ->where('registration_id', (int) $registration_id)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('training_payments')
            ->row();
    }

    private function get_pending_registration_payment($registration_id)
    {
        if (!$this->payments_table_exists()) {
            return NULL;
        }

        return $this->db
            ->where('registration_id', (int) $registration_id)
            ->where('status', 1)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('training_payments')
            ->row();
    }

    private function create_registration_payment($registration_id, $amount)
    {
        $now = date('Y-m-d H:i:s');
        $payment_code = 'PAY-'.date('ymdHis').'-'.(int) $registration_id;

        $this->db->insert('training_payments', array(
            'registration_id' => (int) $registration_id,
            'payment_code' => $payment_code,
            'amount' => (float) $amount,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ));

        return $this->get_pending_registration_payment($registration_id);
    }

    public function get_registration_payment_summary($registration_id, $amount, $ensure_pending = FALSE)
    {
        if (!$this->payments_table_exists() || $amount <= 0) {
            return NULL;
        }

        $registration_id = (int) $registration_id;
        $target_amount = (float) $amount;
        $payments = $this->db
            ->where('registration_id', $registration_id)
            ->order_by('id', 'ASC')
            ->get('training_payments')
            ->result();

        $submitted_amount = 0;
        $latest_submitted = NULL;
        $latest_slip = NULL;
        $payment_slips = array();

        foreach ($payments as $payment) {
            $status = (int) $payment->status;

            if (in_array($status, array(2, 3), TRUE)) {
                $submitted_amount += (float) $payment->amount;
                $latest_submitted = $payment;
            }

            if (!empty($payment->payment_slip) && $status !== 1) {
                $latest_slip = $payment;
                $payment_slips[] = (object) array(
                    'id' => (int) $payment->id,
                    'payment_code' => $payment->payment_code,
                    'amount' => (float) $payment->amount,
                    'status' => $status,
                    'payment_slip' => $payment->payment_slip,
                    'paid_at' => isset($payment->paid_at) ? $payment->paid_at : ''
                );
            }
        }

        $due_amount = max(0, $target_amount - $submitted_amount);
        $refund_amount = max(0, $submitted_amount - $target_amount);
        $pending_payment = $this->get_pending_registration_payment($registration_id);

        if ($ensure_pending) {
            if ($due_amount > 0) {
                if ($pending_payment) {
                    if (abs((float) $pending_payment->amount - $due_amount) >= 0.01) {
                        $this->db
                            ->where('id', (int) $pending_payment->id)
                            ->update('training_payments', array(
                                'amount' => $due_amount,
                                'updated_at' => date('Y-m-d H:i:s')
                            ));
                        $pending_payment = $this->get_pending_registration_payment($registration_id);
                    }
                } else {
                    $pending_payment = $this->create_registration_payment($registration_id, $due_amount);
                }
            } elseif ($pending_payment) {
                $this->db
                    ->where('registration_id', $registration_id)
                    ->where('status', 1)
                    ->group_start()
                    ->where('payment_slip IS NULL', NULL, FALSE)
                    ->or_where('payment_slip', '')
                    ->group_end()
                    ->delete('training_payments');
                $pending_payment = NULL;
            }
        }

        $display_payment = $due_amount > 0 ? $pending_payment : $latest_submitted;

        if (!$display_payment && $latest_slip) {
            $display_payment = $latest_slip;
        }

        $summary = new stdClass();
        $summary->target_amount = $target_amount;
        $summary->submitted_amount = $submitted_amount;
        $summary->due_amount = $due_amount;
        $summary->refund_amount = $refund_amount;
        $summary->payment_id = $display_payment ? (int) $display_payment->id : 0;
        $summary->payment_code = $display_payment ? $display_payment->payment_code : '';
        $summary->payment_status = $due_amount > 0 ? 1 : ($latest_submitted ? (int) $latest_submitted->status : 0);
        $summary->payment_slip = $latest_slip ? $latest_slip->payment_slip : '';
        $summary->payment_slips = $payment_slips;

        return $summary;
    }

    public function ensure_registration_payment($registration_id, $amount)
    {
        if (!$this->payments_table_exists() || $amount <= 0) {
            return NULL;
        }

        $summary = $this->get_registration_payment_summary($registration_id, $amount, TRUE);

        if (!$summary || $summary->due_amount <= 0) {
            return $this->get_registration_payment($registration_id);
        }

        return $this->get_pending_registration_payment($registration_id);
    }

    public function update_payment_slip($registration_id, $slip_path)
    {
        if (!$this->payments_table_exists()) {
            return FALSE;
        }

        $payment = $this->get_pending_registration_payment($registration_id);

        if (!$payment) {
            return FALSE;
        }

        return $this->db
            ->where('id', (int) $payment->id)
            ->update('training_payments', array(
                'payment_slip' => $slip_path,
                'payment_method' => 'transfer',
                'paid_at' => date('Y-m-d H:i:s'),
                'status' => 2,
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function get_registration_by_member($registration_id, $member_id)
    {
        if (!$this->db->table_exists('training_registrations') || !$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return NULL;
        }

        if ($this->participants_table_exists()) {
            $registration_count_select = '(
                SELECT COUNT(*)
                FROM training_registration_participants
                INNER JOIN training_registrations AS counted_registrations
                    ON counted_registrations.id = training_registration_participants.registration_id
                WHERE counted_registrations.batch_id = training_batches.id
                    AND counted_registrations.status <> 4
                    AND training_registration_participants.status = 1
            ) AS registered_count';
        } else {
            $registration_count_select = '(
                SELECT COUNT(*)
                FROM training_registrations AS counted_registrations
                WHERE counted_registrations.batch_id = training_batches.id
                    AND counted_registrations.status <> 4
            ) AS registered_count';
        }

        return $this->db
            ->select('training_registrations.id AS registration_id')
            ->select('training_registrations.registration_code, training_registrations.status AS registration_status')
            ->select('training_registrations.created_at')
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('IF(training_batches.capacity > 0, training_batches.capacity, training_courses.capacity) AS capacity', FALSE)
            ->select($registration_count_select, FALSE)
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug, training_courses.cover_image')
            ->select('training_courses.training_type, training_courses.location')
            ->select('training_categories.name AS category_name')
            ->from('training_registrations')
            ->join($this->table, 'training_batches.id = training_registrations.batch_id', 'inner')
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_registrations.id', (int) $registration_id)
            ->where('training_registrations.member_id', (int) $member_id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function cancel_registration_by_member($registration_id, $member_id)
    {
        if (!$this->registrations_table_exists()) {
            return FALSE;
        }

        $updated = $this->db
            ->where('id', (int) $registration_id)
            ->where('member_id', (int) $member_id)
            ->update('training_registrations', array(
                'status' => 4,
                'updated_at' => date('Y-m-d H:i:s')
            ));

        if ($updated && $this->payments_table_exists()) {
            $this->db
                ->where('registration_id', (int) $registration_id)
                ->where('status', 1)
                ->group_start()
                ->where('payment_slip IS NULL', NULL, FALSE)
                ->or_where('payment_slip', '')
                ->group_end()
                ->delete('training_payments');
        }

        return $updated;
    }

    public function registrations_table_exists()
    {
        return $this->db->table_exists('training_registrations');
    }

    public function participants_table_exists()
    {
        return $this->db->table_exists('training_registration_participants');
    }

    public function get_registration_participants($registration_id)
    {
        if (!$this->participants_table_exists()) {
            return array();
        }

        $this->db->where('registration_id', (int) $registration_id);

        if ($this->db->field_exists('is_main_member', 'training_registration_participants')) {
            $this->db->order_by('is_main_member', 'DESC');
        }

        return $this->db
            ->order_by('id', 'ASC')
            ->get('training_registration_participants')
            ->result();
    }

    public function create_registration_participant($registration_id, $data)
    {
        if (!$this->participants_table_exists()) {
            return FALSE;
        }

        $now = date('Y-m-d H:i:s');
        $participant = array(
            'registration_id' => (int) $registration_id,
            'title_name' => $data['title_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'student_code' => $data['student_code'],
            'school_name' => $data['school_name'],
            'phone' => $data['phone'],
            'email' => strtolower($data['email']),
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now
        );

        if ($this->db->field_exists('member_id', 'training_registration_participants')) {
            $participant['member_id'] = !empty($data['member_id']) ? (int) $data['member_id'] : NULL;
        }

        if ($this->db->field_exists('participant_type', 'training_registration_participants')) {
            $participant['participant_type'] = $data['participant_type'];
        }

        if ($this->db->field_exists('is_main_member', 'training_registration_participants')) {
            $participant['is_main_member'] = !empty($data['is_main_member']) ? 1 : 0;
        }

        return $this->db->insert('training_registration_participants', $participant);
    }

    public function ensure_member_registration_participant($registration_id, $member)
    {
        if (!$this->participants_table_exists() || empty($member) || empty($member['id'])) {
            return FALSE;
        }

        $this->db->where('registration_id', (int) $registration_id);

        if ($this->db->field_exists('member_id', 'training_registration_participants')) {
            $this->db->where('member_id', (int) $member['id']);
        } elseif (!empty($member['email'])) {
            $this->db->where('email', strtolower($member['email']));
        } else {
            return FALSE;
        }

        if ($this->db->count_all_results('training_registration_participants') > 0) {
            return TRUE;
        }

        return $this->create_registration_participant($registration_id, array(
            'title_name' => isset($member['title_name']) ? $member['title_name'] : '',
            'first_name' => isset($member['first_name']) ? $member['first_name'] : '',
            'last_name' => isset($member['last_name']) ? $member['last_name'] : '',
            'student_code' => '',
            'school_name' => isset($member['organization_name']) ? $member['organization_name'] : '',
            'phone' => isset($member['phone']) ? $member['phone'] : '',
            'email' => isset($member['email']) ? $member['email'] : '',
            'participant_type' => 'member',
            'member_id' => (int) $member['id'],
            'is_main_member' => 1
        ));
    }

    public function update_registration_participant($registration_id, $participant_id, $data)
    {
        if (!$this->participants_table_exists()) {
            return FALSE;
        }

        $participant = array(
            'title_name' => $data['title_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'student_code' => $data['student_code'],
            'school_name' => $data['school_name'],
            'phone' => $data['phone'],
            'email' => strtolower($data['email']),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($this->db->field_exists('participant_type', 'training_registration_participants')) {
            $participant['participant_type'] = $data['participant_type'];
        }

        return $this->db
            ->where('registration_id', (int) $registration_id)
            ->where('id', (int) $participant_id)
            ->update('training_registration_participants', $participant);
    }

    public function delete_registration_participant($registration_id, $participant_id)
    {
        if (!$this->participants_table_exists()) {
            return FALSE;
        }

        return $this->db
            ->where('registration_id', (int) $registration_id)
            ->where('id', (int) $participant_id)
            ->delete('training_registration_participants');
    }

    public function sync_registration_payment($registration_id)
    {
        if (!$this->payments_table_exists() || !$this->registrations_table_exists()) {
            return NULL;
        }

        $registration = $this->db
            ->select('training_courses.fee')
            ->from('training_registrations')
            ->join($this->table, 'training_batches.id = training_registrations.batch_id', 'inner')
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->where('training_registrations.id', (int) $registration_id)
            ->limit(1)
            ->get()
            ->row();

        if (!$registration) {
            return NULL;
        }

        $participant_count = 1;
        if ($this->participants_table_exists()) {
            $participant_count = (int) $this->db
                ->where('registration_id', (int) $registration_id)
                ->where('status', 1)
                ->count_all_results('training_registration_participants');
            $participant_count = max(1, $participant_count);
        }

        $total_amount = (float) $registration->fee * $participant_count;

        return $this->get_registration_payment_summary($registration_id, $total_amount, TRUE);
    }

    public function get_selectable_frontend_batch($batch_id)
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return NULL;
        }

        return $this->db
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.status AS batch_status')
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug, training_courses.cover_image')
            ->select('training_courses.training_type, training_courses.location')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_batches.id', (int) $batch_id)
            ->where('training_courses.status', 2)
            ->where_in('training_batches.status', array(1, 3))
            ->limit(1)
            ->get()
            ->row();
    }

    public function get_selected_batches($batch_ids)
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return array();
        }

        $ids = array();
        foreach ((array) $batch_ids as $batch_id) {
            $batch_id = (int) $batch_id;
            if ($batch_id > 0 && !in_array($batch_id, $ids, TRUE)) {
                $ids[] = $batch_id;
            }
        }

        if (empty($ids)) {
            return array();
        }

        return $this->db
            ->select('NULL AS registration_id', FALSE)
            ->select('"-" AS registration_code', FALSE)
            ->select('1 AS registration_status', FALSE)
            ->select('NULL AS created_at', FALSE)
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.status AS batch_status')
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug, training_courses.cover_image')
            ->select('training_courses.training_type, training_courses.location')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where_in('training_batches.id', $ids)
            ->where('training_courses.status', 2)
            ->order_by('training_batches.start_date', 'ASC')
            ->order_by('training_batches.id', 'ASC')
            ->get()
            ->result();
    }

    public function member_is_registered($member_id, $batch_id)
    {
        if (!$this->registrations_table_exists()) {
            return FALSE;
        }

        return $this->db
            ->where('member_id', (int) $member_id)
            ->where('batch_id', (int) $batch_id)
            ->count_all_results('training_registrations') > 0;
    }

    public function get_member_registration_id($member_id, $batch_id)
    {
        if (!$this->registrations_table_exists()) {
            return 0;
        }

        $registration = $this->db
            ->select('id')
            ->where('member_id', (int) $member_id)
            ->where('batch_id', (int) $batch_id)
            ->limit(1)
            ->get('training_registrations')
            ->row();

        return $registration ? (int) $registration->id : 0;
    }

    public function create_member_registration($member_id, $batch_id)
    {
        if (!$this->registrations_table_exists()) {
            return FALSE;
        }

        if ($this->member_is_registered($member_id, $batch_id)) {
            return $this->get_member_registration_id($member_id, $batch_id);
        }

        $now = date('Y-m-d H:i:s');
        $registration_code = 'SCI-'.date('ymd').'-'.(int) $member_id.'-'.(int) $batch_id;

        if ($this->db->insert('training_registrations', array(
            'batch_id' => (int) $batch_id,
            'member_id' => (int) $member_id,
            'registration_code' => $registration_code,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ))) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    public function get_by_id($id)
    {
        return $this->db
            ->select('training_batches.*,training_courses.title AS course_title')
            ->from($this->table)
            ->join('training_courses','training_courses.id=training_batches.course_id','left')
            ->where('training_batches.id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function get_open_by_course($course_id)
    {
        return $this->db
            ->where('course_id', (int) $course_id)
            ->where_in('status', array(1, 3))
            ->order_by('start_date', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function create_batch($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'course_id'           => (int) $data['course_id'],
            'batch_no'            => $data['batch_no'],
            'start_date'          => $data['start_date'],
            'end_date'            => $data['end_date'],
            'start_time'          => $data['start_time'],
            'end_time'            => $data['end_time'],
            'registration_start'  => $data['registration_start'],
            'registration_end'    => $data['registration_end'],
            'capacity'            => (int) $data['capacity'],
            'status'              => (int) $data['status'],
            'created_at'          => $now,
            'updated_at'          => $now
        ));
    }

    public function update_batch($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'course_id'           => (int) $data['course_id'],
                'batch_no'            => $data['batch_no'],
                'start_date'          => $data['start_date'],
                'end_date'            => $data['end_date'],
                'start_time'          => $data['start_time'],
                'end_time'            => $data['end_time'],
                'registration_start'  => $data['registration_start'],
                'registration_end'    => $data['registration_end'],
                'capacity'            => (int) $data['capacity'],
                'status'              => (int) $data['status'],
                'updated_at'          => date('Y-m-d H:i:s')
            ));
    }

    public function delete_batch($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function count_registrations($id)
    {
        if (!$this->db->table_exists('training_registrations')) {
            return 0;
        }

        return $this->db
            ->where('batch_id', (int) $id)
            ->count_all_results('training_registrations');
    }

    public function get_stats()
    {
        return array(
            'total' => $this->db->count_all($this->table),
            'open' => $this->db->where('status', 1)->count_all_results($this->table),
            'closed' => $this->db->where('status', 2)->count_all_results($this->table),
            'additional_open' => $this->db->where('status', 3)->count_all_results($this->table),
            'cancelled' => $this->db->where('status', 4)->count_all_results($this->table)
        );
    }
}
