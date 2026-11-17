<?php
// Run: php tests/survey_statistics.php. Uses isolated data only.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
define('BASEPATH',__DIR__.'/../system/');
class CI_Model {}
require __DIR__.'/../application/models/Survey_model.php';
function check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
function html_escape($value) { return htmlspecialchars($value,ENT_QUOTES,'UTF-8'); }
function site_url($url='') { return '/training/index.php/'.$url; }
$model=new Survey_model();
$calculate=new ReflectionMethod('Survey_model','rating_statistics'); $calculate->setAccessible(TRUE);
$cases=array(
    array(array(),0,NULL,NULL),
    array(array(4),1,4,4),
    array(array(5,1,3),3,3,3),
    array(array(5,3,5,4),4,4.25,4.5),
    array(array(2,2,2,2),4,2,2),
    array(array(NULL,0,6,-1,'2.5','invalid',FALSE,array(1),'5','1'),2,3,3)
);
$example=array_merge(array_fill(0,2,1),array_fill(0,5,2),array_fill(0,13,3),array_fill(0,11,4),array_fill(0,32,5));
$cases[]=array($example,63,255/63,5);
foreach($cases as $case) {
    list($values,$count,$mean,$median)=$case;
    $answers=array_map(function($value){return (object)array('rating_value'=>$value);},$values);
    $stats=$calculate->invoke($model,$answers);
    check($stats['count']===$count,'Wrong valid response count');
    check($mean===NULL?$stats['mean']===NULL:abs($stats['mean']-$mean)<0.000001,'Wrong mean');
    check($median===NULL?$stats['median']===NULL:(float)$stats['median']===(float)$median,'Wrong median');
    check(array_sum($stats['counts'])===$count,'Distribution does not match statistics');
}
class QueryDouble {
    public $queue=array();
    function __call($method,$args) { return $this; }
    function count_all_results($table) { return 0; }
    function result() { return array_shift($this->queue); }
}
class ReportModel extends Survey_model {
    function get_assignment($id) { return (object)array('id'=>$id,'survey_id'=>1,'course_title'=>'Example','batch_no'=>'1'); }
    function get_questions($id) { return array(
        (object)array('id'=>1,'question_type'=>'rating','question_text'=>'Rating'),
        (object)array('id'=>2,'question_type'=>'single_choice','question_text'=>'Choice'),
        (object)array('id'=>3,'question_type'=>'long_text','question_text'=>'Comment')
    ); }
}
$model=new ReportModel(); $model->db=new QueryDouble();
$model->db->queue=array(array_map(function($score){return (object)array('rating_value'=>$score);},array(3,4,5,5)),array((object)array('option_text'=>'Option <A>')),array((object)array('text_value'=>'Comment <B>')));
$report=$model->get_report(6);
check($report->questions[0]->rating_stats['median']===4.5,'Report did not attach statistics');
check(!isset($report->questions[1]->rating_stats) && !isset($report->questions[2]->rating_stats),'Statistics applied to non-rating questions');
class LoaderDouble { function view($name) {} }
class Renderer {
    public $load;
    function __construct() { $this->load=new LoaderDouble(); }
    function render($report) { ob_start(); include __DIR__.'/../application/views/admins/survey_report.php'; return ob_get_clean(); }
}
$renderer=new Renderer(); $html=$renderer->render($report);
check(substr_count($html,'มัธยฐาน (Median)')===1 && strpos($html,'4.50')!==FALSE && strpos($html,'4.25')!==FALSE,'Statistics not correctly rendered');
check(strpos($html,'Option &lt;A&gt;')!==FALSE && strpos($html,'Comment &lt;B&gt;')!==FALSE,'Existing choice/text rendering changed');
$report->questions[0]->rating_stats=$calculate->invoke($model,array()); $html=$renderer->render($report);
check(substr_count($html,'—')===2 && strpos($html,'/ 5')!==FALSE,'Empty statistics not rendered safely');
echo "PASS: odd/even/empty/invalid rating statistics, real example distribution, report integration and rendering\n";
