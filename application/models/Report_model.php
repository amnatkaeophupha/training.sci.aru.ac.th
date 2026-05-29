<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model
{
    public function tables_ready()
    {
        return $this->db->table_exists('training_registrations')
            && $this->db->table_exists('training_batches')
            && $this->db->table_exists('training_courses');
    }

    public function get_overview()
    {
        $overview = array(
            'registrations' => 0,
            'pending_registrations' => 0,
            'participants' => 0,
            'revenue_uploaded' => 0,
            'pending_slips' => 0
        );

        if (!$this->tables_ready()) {
            return $overview;
        }

        $overview['registrations'] = (int) $this->db->count_all_results('training_registrations');
        $overview['pending_registrations'] = (int) $this->db
            ->where('status', 1)
            ->count_all_results('training_registrations');

        if ($this->db->table_exists('training_registration_participants')) {
            $overview['participants'] = (int) $this->db
                ->where('status', 1)
                ->count_all_results('training_registration_participants');
        }

        if ($this->db->table_exists('training_payments')) {
            $payment = $this->db
                ->select('COALESCE(SUM(amount), 0) AS total', FALSE)
                ->where_in('status', array(2, 3))
                ->get('training_payments')
                ->row();

            $overview['revenue_uploaded'] = $payment ? (float) $payment->total : 0;
            $overview['pending_slips'] = (int) $this->db
                ->where('status', 2)
                ->count_all_results('training_payments');
        }

        return $overview;
    }

    public function get_course_report()
    {
        if (!$this->tables_ready()) {
            return array();
        }

        $participant_count = $this->db->table_exists('training_registration_participants')
            ? 'COUNT(DISTINCT training_registration_participants.id) AS participant_count'
            : 'COUNT(DISTINCT training_registrations.id) AS participant_count';
        $payment_total = $this->db->table_exists('training_payments')
            ? 'COALESCE(SUM(CASE WHEN training_payments.status IN (2, 3) THEN training_payments.amount ELSE 0 END), 0) AS uploaded_amount'
            : '0 AS uploaded_amount';

        $this->db
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title AS course_title')
            ->select('training_categories.name AS category_name')
            ->select('COUNT(DISTINCT training_registrations.id) AS registration_count', FALSE)
            ->select($participant_count, FALSE)
            ->select($payment_total, FALSE)
            ->from('training_courses')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->join('training_batches', 'training_batches.course_id = training_courses.id', 'left')
            ->join('training_registrations', 'training_registrations.batch_id = training_batches.id', 'left');

        if ($this->db->table_exists('training_registration_participants')) {
            $this->db->join('training_registration_participants', 'training_registration_participants.registration_id = training_registrations.id AND training_registration_participants.status = 1', 'left');
        }

        if ($this->db->table_exists('training_payments')) {
            $this->db->join('training_payments', 'training_payments.registration_id = training_registrations.id', 'left');
        }

        return $this->db
            ->group_by('training_courses.id')
            ->order_by('registration_count', 'DESC')
            ->order_by('training_courses.title', 'ASC')
            ->get()
            ->result();
    }

    public function get_payment_report()
    {
        if (!$this->db->table_exists('training_payments')) {
            return array();
        }

        return $this->db
            ->select('training_payments.status')
            ->select('COUNT(*) AS payment_count', FALSE)
            ->select('COALESCE(SUM(training_payments.amount), 0) AS total_amount', FALSE)
            ->from('training_payments')
            ->group_by('training_payments.status')
            ->order_by('training_payments.status', 'ASC')
            ->get()
            ->result();
    }

    public function get_recent_registrations($limit = 10)
    {
        if (!$this->tables_ready()) {
            return array();
        }

        $participant_count = $this->db->table_exists('training_registration_participants')
            ? '(SELECT COUNT(*) FROM training_registration_participants WHERE training_registration_participants.registration_id = training_registrations.id AND training_registration_participants.status = 1) AS participant_count'
            : '1 AS participant_count';
        $uploaded_amount = $this->db->table_exists('training_payments')
            ? '(SELECT COALESCE(SUM(amount), 0) FROM training_payments WHERE training_payments.registration_id = training_registrations.id AND training_payments.status IN (2, 3)) AS uploaded_amount'
            : '0 AS uploaded_amount';

        return $this->db
            ->select('training_registrations.id, training_registrations.registration_code, training_registrations.status')
            ->select('training_registrations.created_at')
            ->select('members.email, members.first_name, members.last_name, members.title_name')
            ->select('training_courses.title AS course_title')
            ->select('training_batches.batch_no')
            ->select($participant_count, FALSE)
            ->select($uploaded_amount, FALSE)
            ->from('training_registrations')
            ->join('members', 'members.id = training_registrations.member_id', 'left')
            ->join('training_batches', 'training_batches.id = training_registrations.batch_id', 'inner')
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->order_by('training_registrations.created_at', 'DESC')
            ->order_by('training_registrations.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }
}
