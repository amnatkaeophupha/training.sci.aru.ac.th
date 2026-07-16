<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Registration_model extends CI_Model
{
    public function tables_ready()
    {
        return $this->db->table_exists('training_registrations')
            && $this->db->table_exists('training_batches')
            && $this->db->table_exists('training_courses');
    }

    public function participants_table_exists()
    {
        return $this->db->table_exists('training_registration_participants');
    }

    public function payments_table_exists()
    {
        return $this->db->table_exists('training_payments');
    }

    public function get_all($filters = array())
    {
        if (!$this->tables_ready()) {
            return array();
        }

        $participant_count_select = $this->participants_table_exists()
            ? '(SELECT COUNT(*) FROM training_registration_participants WHERE training_registration_participants.registration_id = training_registrations.id AND training_registration_participants.status = 1) AS participant_count'
            : '1 AS participant_count';
        $payment_paid_select = $this->payments_table_exists()
            ? '(SELECT COALESCE(SUM(amount), 0) FROM training_payments WHERE training_payments.registration_id = training_registrations.id AND training_payments.status IN (2, 3)) AS paid_amount'
            : '0 AS paid_amount';
        $payment_pending_select = $this->payments_table_exists()
            ? '(SELECT COALESCE(SUM(amount), 0) FROM training_payments WHERE training_payments.registration_id = training_registrations.id AND training_payments.status = 1) AS pending_amount'
            : '0 AS pending_amount';
        $payment_latest_status_select = $this->payments_table_exists()
            ? '(SELECT status FROM training_payments WHERE training_payments.registration_id = training_registrations.id ORDER BY id DESC LIMIT 1) AS payment_status'
            : '0 AS payment_status';

        $this->db
            ->select('training_registrations.id, training_registrations.registration_code')
            ->select('training_registrations.status, training_registrations.created_at')
            ->select('members.email, members.title_name, members.first_name, members.last_name, members.phone')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_courses.title AS course_title, training_courses.fee')
            ->select('training_categories.name AS category_name')
            ->select($participant_count_select, FALSE)
            ->select($payment_paid_select, FALSE)
            ->select($payment_pending_select, FALSE)
            ->select($payment_latest_status_select, FALSE)
            ->from('training_registrations')
            ->join('members', 'members.id = training_registrations.member_id', 'left')
            ->join('training_batches', 'training_batches.id = training_registrations.batch_id', 'inner')
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left');

        if (!empty($filters['status'])) {
            $this->db->where('training_registrations.status', (int) $filters['status']);
        }

        if (!empty($filters['course_id'])) {
            $this->db->where('training_courses.id', (int) $filters['course_id']);
        }

        if (!empty($filters['batch_id'])) {
            $this->db->where('training_batches.id', (int) $filters['batch_id']);
        }

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()
                ->like('training_registrations.registration_code', $q)
                ->or_like('training_courses.title', $q)
                ->or_like('members.first_name', $q)
                ->or_like('members.last_name', $q)
                ->or_like('members.email', $q)
                ->group_end();
        }

        return $this->db
            ->order_by('training_registrations.created_at', 'DESC')
            ->order_by('training_registrations.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_stats($filters = array())
    {
        $stats = array(
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'payment_checking' => 0
        );

        if (!$this->tables_ready()) {
            return $stats;
        }

        $stats['total'] = $this->count_registrations_by_filters($filters);
        $stats['pending'] = $this->count_registrations_by_filters($filters, 1);
        $stats['approved'] = $this->count_registrations_by_filters($filters, 2);

        if ($this->payments_table_exists()) {
            $this->db
                ->from('training_payments')
                ->join('training_registrations', 'training_registrations.id = training_payments.registration_id', 'inner')
                ->join('training_batches', 'training_batches.id = training_registrations.batch_id', 'inner')
                ->where('training_payments.status', 2);
            $this->apply_course_batch_filters($filters);
            $stats['payment_checking'] = (int) $this->db->count_all_results();
        }

        return $stats;
    }

    private function count_registrations_by_filters($filters, $status = NULL)
    {
        $this->db
            ->from('training_registrations')
            ->join('training_batches', 'training_batches.id = training_registrations.batch_id', 'inner');

        if ($status !== NULL) {
            $this->db->where('training_registrations.status', (int) $status);
        }

        $this->apply_course_batch_filters($filters);

        return (int) $this->db->count_all_results();
    }

    private function apply_course_batch_filters($filters)
    {
        if (!empty($filters['course_id'])) {
            $this->db->where('training_batches.course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['batch_id'])) {
            $this->db->where('training_batches.id', (int) $filters['batch_id']);
        }
    }

    public function get_filter_courses()
    {
        if (!$this->tables_ready()) {
            return array();
        }

        return $this->db
            ->select('training_courses.id, training_courses.title')
            ->select('COUNT(DISTINCT training_registrations.id) AS registration_count', FALSE)
            ->from('training_courses')
            ->join('training_batches', 'training_batches.course_id = training_courses.id', 'inner')
            ->join('training_registrations', 'training_registrations.batch_id = training_batches.id', 'inner')
            ->group_by('training_courses.id')
            ->order_by('training_courses.title', 'ASC')
            ->get()
            ->result();
    }

    public function get_filter_batches($course_id)
    {
        if (!$this->tables_ready() || (int) $course_id <= 0) {
            return array();
        }

        return $this->db
            ->select('training_batches.id, training_batches.batch_no')
            ->select('training_batches.start_date, training_batches.end_date, training_batches.capacity')
            ->select('COUNT(DISTINCT training_registrations.id) AS registration_count', FALSE)
            ->from('training_batches')
            ->join('training_registrations', 'training_registrations.batch_id = training_batches.id', 'left')
            ->where('training_batches.course_id', (int) $course_id)
            ->group_by('training_batches.id')
            ->order_by('training_batches.start_date', 'DESC')
            ->order_by('training_batches.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_filter_context($course_id, $batch_id = 0)
    {
        if (!$this->tables_ready() || (int) $course_id <= 0) {
            return NULL;
        }

        $this->db
            ->select('training_courses.id AS course_id, training_courses.title AS course_title')
            ->select('training_batches.id AS batch_id, training_batches.batch_no')
            ->select('training_batches.start_date, training_batches.end_date, training_batches.capacity')
            ->from('training_courses')
            ->join('training_batches', 'training_batches.course_id = training_courses.id', 'left')
            ->where('training_courses.id', (int) $course_id);

        if ((int) $batch_id > 0) {
            $this->db->where('training_batches.id', (int) $batch_id);
        }

        return $this->db
            ->order_by('training_batches.start_date', 'DESC')
            ->limit(1)
            ->get()
            ->row();
    }

    public function get_by_id($id)
    {
        if (!$this->tables_ready()) {
            return NULL;
        }

        return $this->db
            ->select('training_registrations.*')
            ->select('members.email, members.title_name, members.first_name, members.last_name, members.phone')
            ->select('members.organization_name, members.position_name')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time, training_batches.capacity')
            ->select('training_courses.title AS course_title, training_courses.fee, training_courses.location')
            ->select('training_courses.training_type')
            ->select('training_categories.name AS category_name')
            ->from('training_registrations')
            ->join('members', 'members.id = training_registrations.member_id', 'left')
            ->join('training_batches', 'training_batches.id = training_registrations.batch_id', 'inner')
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_registrations.id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function get_participants($registration_id)
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

    public function get_payments($registration_id)
    {
        if (!$this->payments_table_exists()) {
            return array();
        }

        return $this->db
            ->where('registration_id', (int) $registration_id)
            ->order_by('id', 'ASC')
            ->get('training_payments')
            ->result();
    }

    public function update_status($registration_id, $status)
    {
        return $this->db
            ->where('id', (int) $registration_id)
            ->update('training_registrations', array(
                'status' => (int) $status,
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function update_payment_status($payment_id, $status)
    {
        if (!$this->payments_table_exists()) {
            return FALSE;
        }

        return $this->db
            ->where('id', (int) $payment_id)
            ->update('training_payments', array(
                'status' => (int) $status,
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }
}
