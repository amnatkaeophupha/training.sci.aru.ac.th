<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Shared rule for explicit deletion in the admin interface. */
function admin_can_delete($session)
{
    return (bool) $session->userdata('admin_logged_in')
        && $session->userdata('admin_role') !== 'staff';
}

/** Paid registrations may not be deleted once approved or attended. */
function admin_registration_delete_locked($registration_status, $payment_status)
{
    return (int) $payment_status === 3
        && in_array((int) $registration_status, array(2, 5), TRUE);
}

/** Controllers must return immediately when this guard returns FALSE. */
function admin_require_delete_permission($session, $return_url)
{
    if (admin_can_delete($session)) return TRUE;

    $session->set_flashdata('error', 'คุณไม่มีสิทธิ์ลบข้อมูล');
    redirect($session->userdata('admin_logged_in') ? $return_url : 'admin');
    return FALSE;
}
