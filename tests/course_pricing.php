<?php
// Uses a uniquely named disposable database; never changes production courses/payments.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
define('BASEPATH',__DIR__.'/../system/'); define('APPPATH',__DIR__.'/../application/'); define('ENVIRONMENT','testing');
require APPPATH.'config/database.php';
require APPPATH.'helpers/pricing_helper.php';
function log_message($level,$message) {}
function show_error($message) { throw new RuntimeException($message); }
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
function check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
require BASEPATH.'database/DB.php';
class FixtureLoader {
    private $owner;
    function __construct($owner) { $this->owner=$owner; }
    function model($name) { if(!isset($GLOBALS['models'][$name])) $GLOBALS['models'][$name]=new $name(); $this->owner->$name=$GLOBALS['models'][$name]; }
}
class CI_Model { function __construct() { $this->db=$GLOBALS['fixture']; $this->load=new FixtureLoader($this); } }
require APPPATH.'models/Pricing_model.php'; require APPPATH.'models/Course_model.php'; require APPPATH.'models/Batch_model.php'; require APPPATH.'models/Registration_model.php';
$config=$db['default']; mysqli_report(MYSQLI_REPORT_OFF);
$admin=new mysqli($config['hostname'],$config['username'],$config['password'],$config['database']);
check(!$admin->connect_errno,'Database unavailable');
$schema='training_fee_test_'.bin2hex(random_bytes(6)); check($admin->query('CREATE DATABASE '.$schema.' CHARACTER SET utf8mb4'),'Cannot create test database');
try {
    $tables=array('training_courses','training_batches','training_registrations','training_registration_participants','training_payments','training_categories','members','training_survey_invitations','training_certificates');
    foreach($tables as $table) check($admin->query('CREATE TABLE '.$schema.'.'.$table.' LIKE '.$config['database'].'.'.$table),'Cannot clone test structure');
    $params=$config; $params['database']=$schema; $params['db_debug']=FALSE;
    $GLOBALS['fixture']=$fixture=DB($params,TRUE); $GLOBALS['models']=array();
    $insert=function($table,$overrides)use($fixture) {
        $fields=$fixture->query('SHOW COLUMNS FROM '.$table)->result_array(); $row=array();
        foreach($fields as $field) {
            if($field['Null']==='NO' && $field['Default']===NULL && strpos($field['Extra'],'auto_increment')===FALSE) {
                $type=$field['Type']; $row[$field['Field']]=preg_match('/int|decimal|float|double/',$type)?0:(preg_match('/date|time/',$type)?'2026-11-16 00:00:00':'fixture');
            }
        }
        $row=array_merge($row,$overrides); check($fixture->insert($table,$row),'Fixture insert failed: '.$table); return $fixture->insert_id();
    };
    $insert('training_categories',array('id'=>1,'name'=>'Category'));
    $insert('training_courses',array('id'=>7,'category_id'=>1,'title'=>'Course','slug'=>'pricing-test','fee'=>'300.00','status'=>2));
    $insert('training_batches',array('id'=>11,'course_id'=>7,'batch_no'=>'1','status'=>1,'capacity'=>100));
    $insert('members',array('id'=>1,'email'=>'old@example.test')); $insert('members',array('id'=>2,'email'=>'new@example.test'));
    $insert('training_registrations',array('id'=>1,'member_id'=>1,'batch_id'=>11,'registration_code'=>'OLD','status'=>1));
    $run_migration=function()use($fixture) {
        $sql=file_get_contents(__DIR__.'/../Design/migrate_course_fee_options.sql');
        check($fixture->conn_id->multi_query($sql),'Migration failed');
        do { $r=$fixture->conn_id->store_result(); if($r)$r->free(); } while($fixture->conn_id->more_results()&&$fixture->conn_id->next_result());
        check(!$fixture->conn_id->errno,'Migration statement failed');
        $fixture->data_cache=array();
    };
    $run_migration(); $run_migration();
    check($fixture->query('SELECT unit_fee_snapshot FROM training_registrations WHERE id=1')->row()->unit_fee_snapshot==='300.00','Legacy fee backfill wrong');
    $pricing=new Pricing_model(); $courses=new Course_model(); $batch=new Batch_model(); $GLOBALS['models']=array('Pricing_model'=>$pricing,'Batch_model'=>$batch);
    $data=(array)$courses->get_by_id(7); $data['fee_mode']='options';
    $data['fee_options']=array(array('id'=>0,'label'=>'Child','amount'=>'200','is_active'=>'1'),array('id'=>0,'label'=>'Adult','amount'=>'300','is_active'=>'1'));
    check($pricing->validate_configuration($data)==='','Valid pricing rejected');
    check($courses->save_with_fees(7,$data),'Course options save failed');
    $choices=$pricing->options(7,TRUE); $child=$choices[0]['id']; $adult=$choices[1]['id'];
    $insert('training_courses',array('id'=>8,'category_id'=>1,'title'=>'Other','slug'=>'other-pricing','fee'=>'10.00','status'=>2));
    $foreign_id=$insert('training_course_fee_options',array('course_id'=>8,'label'=>'Other course option','amount'=>'10.00'));
    $foreign=$data; $foreign['fee_options'][0]['id']=$foreign_id;
    check(!$courses->save_with_fees(7,$foreign),'Foreign course option was edited');
    check(count($pricing->options(7,TRUE))===2,'Failed course save did not roll back');
    $config_now=$pricing->configuration_for_batch(11); $revision=$config_now->pricing_revision;
    $member=array('id'=>2,'title_name'=>'','first_name'=>'Example','last_name'=>'Member','email'=>'new@example.test','phone'=>'','organization_name'=>'');
    check(!$batch->register_member_with_participant($member,11,'999999',$revision),'Foreign option accepted');
    check(!$batch->register_member_with_participant($member,11,$child,(int)$revision-1),'Stale price revision accepted');
    check((int)$fixture->query('SELECT COUNT(*) AS n FROM training_registrations WHERE member_id=2')->row()->n===0,'Failed registration left partial records');
    $id=$batch->register_member_with_participant($member,11,$child,$revision); check($id,'Conditional registration failed');
    check($batch->register_member_with_participant($member,11)===$id,'Duplicate registration failed');
    $person=array_merge($member,array('student_code'=>'','school_name'=>'School','participant_type'=>'other','member_id'=>0,'is_main_member'=>0));
    check($batch->create_registration_participant($id,$person) && $batch->create_registration_participant($id,$person),'Group fixture failed');
    $summary=$batch->sync_registration_payment($id); check((float)$summary->target_amount===600.0,'200 x 3 does not equal 600');
    $snapshot=$fixture->query('SELECT fee_options_snapshot FROM training_registrations WHERE id='.(int)$id)->row()->fee_options_snapshot;
    $updated=(array)$courses->get_by_id(7); $updated['fee_options']=$choices; $updated['fee_options'][0]['is_active']=0; $updated['fee_options'][1]['amount']='777.00';
    check($pricing->validate_configuration($updated)==='' && $courses->save_with_fees(7,$updated),'Repricing failed');
    check($fixture->query('SELECT unit_fee_snapshot FROM training_registrations WHERE id=1')->row()->unit_fee_snapshot==='300.00','Old registration repriced');
    check($fixture->query('SELECT fee_options_snapshot FROM training_registrations WHERE id='.(int)$id)->row()->fee_options_snapshot===$snapshot,'Existing choice menu changed');
    check($pricing->change_selection($id,2,$adult),'Prepayment choice change rejected');
    check((float)$batch->sync_registration_payment($id)->target_amount===900.0,'300 x 3 does not equal 900');
    check(!$pricing->change_selection($id,1,$child),'Another member changed pricing');
    check(!$pricing->change_selection($id,2,'999999'),'Unknown snapshot choice accepted');
    $payment=$fixture->query('SELECT * FROM training_payments WHERE registration_id='.(int)$id.' AND status=1')->row();
    check($payment && (float)$payment->amount===900.0,'Pending amount incorrect');
    $fixture->where('id',$payment->id)->update('training_payments',array('status'=>2,'payment_slip'=>'fixture-no-real-file.pdf'));
    check(!$pricing->change_selection($id,2,$child),'Price changed after slip upload');
    check((float)$fixture->query('SELECT unit_fee_snapshot FROM training_registrations WHERE id='.(int)$id)->row()->unit_fee_snapshot===300.0,'Locked price modified');
    check($batch->create_registration_participant($id,$person),'Additional participant failed');
    $summary=$batch->sync_registration_payment($id);
    check((float)$summary->target_amount===1200.0 && (float)$summary->due_amount===300.0,'Added participant lost original rate/payment');
    $registration=(new Registration_model())->get_by_id($id); check((float)$registration->fee===300.0,'Admin fee uses current course price');
    $cards=$batch->get_registered_by_member(2); check((float)$cards[0]->payment_total===1200.0 && $cards[0]->fee_change_locked,'Frontend pricing disagrees');
    $fixture->where('id',$payment->id)->update('training_payments',array('status'=>4));
    check(!$pricing->change_selection($id,2,$child),'Rejected proof unlocked historical pricing');
    $fixture->where('id',$payment->id)->update('training_payments',array('status'=>3,'payment_slip'=>''));
    check(!$pricing->change_selection($id,2,$child),'Paid status without file unlocked pricing');
    $fixture->where('id',$payment->id)->update('training_payments',array('status'=>1));
    $participant=$fixture->query('SELECT id FROM training_registration_participants WHERE registration_id='.(int)$id.' LIMIT 1')->row()->id;
    $insert('training_survey_invitations',array('participant_id'=>$participant,'assignment_id'=>1,'profile_confirmed_at'=>'2026-11-16 00:00:00'));
    check(!$pricing->change_selection($id,2,$child),'Confirmed participant allowed group price change');
    $insert('members',array('id'=>3,'email'=>'future@example.test'));
    $future=$member; $future['id']=3; $future['email']='future@example.test';
    $latest=$pricing->configuration_for_batch(11);
    check(!$batch->register_member_with_participant($future,11,$child,$latest->pricing_revision),'Disabled current option accepted for a new application');
    $new_id=$batch->register_member_with_participant($future,11,$adult,$latest->pricing_revision);
    check($new_id && (float)$fixture->query('SELECT unit_fee_snapshot FROM training_registrations WHERE id='.(int)$new_id)->row()->unit_fee_snapshot===777.0,'New application did not use new rate');
    $fixed=(array)$courses->get_by_id(7); $fixed['fee_mode']='fixed'; $fixed['fee']='0.00'; $fixed['fee_options']=$pricing->options(7);
    check($pricing->validate_configuration($fixed)==='' && $courses->save_with_fees(7,$fixed),'Free fixed mode failed');
    $insert('members',array('id'=>4,'email'=>'free@example.test')); $free=$member; $free['id']=4; $free['email']='free@example.test';
    $free_id=$batch->register_member_with_participant($free,11);
    check($free_id && (float)$fixture->query('SELECT unit_fee_snapshot FROM training_registrations WHERE id='.(int)$free_id)->row()->unit_fee_snapshot===0.0,'Free fixed registration did not snapshot zero');
    foreach(array(
        array('fee_mode'=>'options','fee'=>'0','fee_options'=>array()),
        array('fee_mode'=>'invalid','fee'=>'0','fee_options'=>array()),
        array('fee_mode'=>'fixed','fee'=>'-1','fee_options'=>array()),
        array('fee_mode'=>'fixed','fee'=>'1.234','fee_options'=>array()),
        array('fee_mode'=>'options','fee'=>'0','fee_options'=>array(array('id'=>0,'label'=>'Same','amount'=>'1','is_active'=>'1'),array('id'=>0,'label'=>'same','amount'=>'2','is_active'=>'1')))
    ) as $invalid) check($pricing->validate_configuration($invalid)!=='','Invalid configuration accepted');
    $run_migration(); check($fixture->query('SELECT fee_options_snapshot FROM training_registrations WHERE id='.(int)$id)->row()->fee_options_snapshot===$snapshot,'Rerun overwrote snapshots');
    check(training_money_cents('0.01')===1 && training_money_cents('1.234')===FALSE && training_money_cents('-1')===FALSE && training_fee_total('0.10',3)==='0.30','Money precision incorrect');
    echo "PASS: real CI/MariaDB course configuration, snapshots, group totals, repricing, ownership, payment locks and additive migration\n";
} finally {
    check((bool)preg_match('/^training_fee_test_[a-f0-9]{12}$/D',$schema),'Unsafe cleanup target');
    $admin->query('DROP DATABASE '.$schema);
}
