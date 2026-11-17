<?php
// Run: php tests/registration_delete.php. Uses doubles and temporary files only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
require __DIR__.'/../application/helpers/admin_permissions_helper.php';
define('FCPATH', sys_get_temp_dir().'/training-delete-'.bin2hex(random_bytes(6)).'/');
class CI_Model {}
class CI_Controller { function __construct() {} }
class RedirectResult extends Exception {}
function redirect($url) { throw new RedirectResult($url); }
function show_404() { throw new RuntimeException('404'); }
function log_message($level, $message) { $GLOBALS['logs'][] = $message; }
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
require __DIR__.'/../application/models/Registration_model.php';
require __DIR__.'/../application/controllers/Admin/Registrations.php';
class Rows {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function num_rows() { return count($this->rows); }
    function result() { return $this->rows; }
}
class DatabaseDouble {
    public $db_debug = TRUE, $fail = '', $calls = array(), $where = array(), $shared = 0, $registration_status = 1, $payment_status = 1;
    function table_exists($table) { return TRUE; }
    function trans_begin() { $this->calls[] = 'begin'; return TRUE; }
    function trans_status() { return TRUE; }
    function trans_rollback() { $this->calls[] = 'rollback'; return TRUE; }
    function trans_commit() { $this->calls[] = 'commit'; return $this->fail !== 'commit'; }
    function query($sql, $params) {
        $this->calls[] = array($sql, $params);
        if (strpos($sql, 'WHERE payment_slip') !== FALSE) return $this->shared === FALSE ? FALSE : new Rows($this->shared ? array((object)array('id'=>99)) : array());
        if (strpos($sql, 'SELECT id') === 0) return new Rows($this->fail === 'missing' ? array() : array((object)array('id'=>$params[0], 'status'=>$this->registration_status)));
        return new Rows(array((object)array('payment_slip'=>'uploads/payments/one.pdf','status'=>3), (object)array('payment_slip'=>'uploads/payments/one.pdf','status'=>2), (object)array('payment_slip'=>'uploads/payments/two.png','status'=>$this->payment_status)));
    }
    function where($key, $value) { $this->where = array($key, $value); return $this; }
    function delete($table) { $this->calls[] = array('delete', $table, $this->where); return $this->fail !== $table; }
}
$model = new Registration_model();
foreach (array('', 'training_payments', 'training_registration_participants', 'training_registrations', 'missing', 'commit') as $failure) {
    $model->db = new DatabaseDouble(); $model->db->fail = $failure;
    $result = $model->delete_registration(12);
    check($model->db->db_debug === TRUE, 'Database debug setting not restored');
    if ($failure === '') {
        check($result === array('uploads/payments/one.pdf', 'uploads/payments/two.png'), 'All unique payment paths must be collected');
        foreach ($model->db->calls as $call) {
            if (is_array($call) && $call[0] === 'delete') {
                check($call[2][1] === 12, 'Deletion escaped selected registration');
                check(in_array($call[1], array('training_payments','training_registration_participants','training_registrations'), TRUE), 'Unrelated table deleted');
            }
        }
        check(in_array('commit', $model->db->calls, TRUE), 'Deletion did not commit');
    } else {
        check($result === FALSE && in_array('rollback', $model->db->calls, TRUE), 'Failure did not rollback: '.$failure);
    }
}
foreach (range(1,5) as $status) {
    foreach (range(0,4) as $payment_status) {
        $model->db = new DatabaseDouble();
        $model->db->registration_status = $status; $model->db->payment_status = $payment_status;
        $locked = $payment_status === 3 && in_array($status, array(2,5), TRUE);
        check(($model->delete_registration(12) === FALSE) === $locked, 'Incorrect model deletion rule');
        if ($locked) {
            foreach ($model->db->calls as $call) check(!is_array($call) || $call[0] !== 'delete', 'Locked registration reached DELETE');
            check(in_array('rollback', $model->db->calls, TRUE) && !in_array('commit', $model->db->calls, TRUE), 'Locked deletion did not rollback');
        }
    }
}
mkdir(FCPATH.'uploads/payments', 0777, TRUE);
try {
    $model->db = new DatabaseDouble();
    file_put_contents(FCPATH.'uploads/payments/one.pdf', 'fixture');
    file_put_contents(FCPATH.'outside.pdf', 'untouched');
    $model->db->shared = 1;
    check($model->delete_payment_files(array('uploads/payments/one.pdf')), 'Shared file check failed');
    check(is_file(FCPATH.'uploads/payments/one.pdf'), 'Shared file deleted');
    $model->db->shared = FALSE;
    check(!$model->delete_payment_files(array('uploads/payments/one.pdf')), 'Reference query failure ignored');
    check(is_file(FCPATH.'uploads/payments/one.pdf'), 'File deleted when reference query failed');
    $model->db->shared = 0;
    check($model->delete_payment_files(array('uploads/payments/one.pdf','uploads/payments/missing.pdf')), 'Valid/missing file handling failed');
    check(!file_exists(FCPATH.'uploads/payments/one.pdf'), 'Payment file still exists');
    check(!$model->delete_payment_files(array('uploads/payments/../../outside.pdf', 'uploads/payments/..\\outside.pdf')), 'Traversal accepted');
    mkdir(FCPATH.'uploads/payments/directory.pdf');
    check(!$model->delete_payment_files(array('uploads/payments/directory.pdf')), 'Non-file accepted');
    check(is_file(FCPATH.'outside.pdf'), 'File outside payment directory affected');
} finally {
    foreach (array('uploads/payments/one.pdf','outside.pdf') as $file) if (is_file(FCPATH.$file)) unlink(FCPATH.$file);
    rmdir(FCPATH.'uploads/payments/directory.pdf'); rmdir(FCPATH.'uploads/payments'); rmdir(FCPATH.'uploads'); rmdir(FCPATH);
}
class InputDouble {
    public $verb = 'POST', $data = array('delete_token'=>'valid','course_id'=>'9','q'=>'name');
    function method($upper = FALSE) { return $this->verb; }
    function post($key, $clean = FALSE) { return isset($this->data[$key]) ? $this->data[$key] : NULL; }
}
class SessionDouble {
    public $data = array('admin_logged_in'=>TRUE,'admin_role'=>'admin','registration_delete_token'=>'valid');
    function userdata($key) { return isset($this->data[$key]) ? $this->data[$key] : NULL; }
    function set_flashdata($key, $value) { $this->data[$key] = $value; }
}
class RegistrationDouble {
    public $calls = array(), $files = array(), $clean = TRUE, $exists = TRUE, $status = 1, $payment_status = 0;
    function get_by_id($id) { $this->calls[] = 'find'; return $this->exists ? (object)array('id'=>$id,'status'=>$this->status,'payment_status'=>$this->payment_status) : NULL; }
    function delete_registration($id) { $this->calls[] = $id; return $this->files; }
    function delete_payment_files($files) { $this->calls[] = 'files'; return $this->clean; }
}
class RegistrationsDouble extends Registrations { function __construct() {} }
foreach (array('success','staff','get','token','missing','database','cleanup') as $case) {
    $c = new RegistrationsDouble();
    $c->input = new InputDouble(); $c->session = new SessionDouble(); $c->Registration_model = new RegistrationDouble();
    if ($case === 'staff') $c->session->data['admin_role'] = 'staff';
    if ($case === 'get') $c->input->verb = 'GET';
    if ($case === 'token') $c->input->data['delete_token'] = array('invalid');
    if ($case === 'missing') $c->Registration_model->exists = FALSE;
    if ($case === 'database') $c->Registration_model->files = FALSE;
    if ($case === 'cleanup') $c->Registration_model->clean = FALSE;
    try { $c->delete(12); throw new RuntimeException('Expected redirect/404'); }
    catch (RedirectResult $e) { check($case === 'staff' ? $e->getMessage() === 'admin/registrations' : strpos($e->getMessage(), 'admin/registrations?course_id=9') === 0, 'Filters lost'); }
    catch (RuntimeException $e) { check($case === 'get' && $e->getMessage() === '404', 'Unexpected error'); }
    if (in_array($case, array('staff','get','token'), TRUE)) check(!$c->Registration_model->calls, 'Unauthorized request reached model');
    if ($case === 'database') check(!in_array('files', $c->Registration_model->calls, TRUE), 'Files deleted after database failure');
    if ($case === 'success') check(isset($c->session->data['success']), 'Success message missing');
    if ($case === 'cleanup') check(isset($c->session->data['error']) && !isset($c->session->data['success']), 'Partial cleanup reported as success');
}
foreach (range(1,5) as $status) {
    foreach (range(0,4) as $payment_status) {
        $c = new RegistrationsDouble(); $c->input = new InputDouble(); $c->session = new SessionDouble();
        $c->Registration_model = new RegistrationDouble();
        $c->Registration_model->status = $status; $c->Registration_model->payment_status = $payment_status;
        try { $c->delete(12); } catch (RedirectResult $e) {}
        $locked = $payment_status === 3 && in_array($status, array(2,5), TRUE);
        check(in_array(12,$c->Registration_model->calls,TRUE) === !$locked, 'Controller rule differs from model');
        if ($locked) check($c->Registration_model->calls === array('find') && isset($c->session->data['error']), 'Locked request touched files/data');
    }
}
echo "PASS: registration status/payment deletion rules, transactions, file cleanup, permissions, tokens and filters\n";
