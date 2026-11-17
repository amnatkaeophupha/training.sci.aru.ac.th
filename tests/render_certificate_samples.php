<?php
// Generates fictional PDF samples only; reads background artwork, never issues/sends.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/'); define('APPPATH', __DIR__.'/../application/');
define('FCPATH', dirname(__DIR__).'/'); define('ENVIRONMENT','testing');
require APPPATH.'config/database.php';
require APPPATH.'helpers/certificate_helper.php';
require APPPATH.'libraries/Certificate_pdf.php';
function show_error($message,$code=500) { throw new RuntimeException($message); }
$config=$db['default']; $connection=new mysqli($config['hostname'],$config['username'],$config['password'],$config['database']);
$template=$connection->query('SELECT * FROM training_certificate_templates WHERE batch_id=11')->fetch_assoc();
if (!$template || !is_file(FCPATH.$template['background_path'])) throw new RuntimeException('Sample background unavailable');
$directory=__DIR__.'/results/certificates'; if (!is_dir($directory)) mkdir($directory,0755,TRUE);
$base=array_merge($template,certificate_number_defaults(),array('certificate_no'=>'SCI-2569-000001','recipient_name'=>'นายสมชาย ตัวอย่าง'));
$cases=array(
    'number-default'=>$base,
    'number-legacy'=>array_merge($base,array('certificate_no'=>'CERT-000001')),
    'number-left'=>array_merge($base,array('number_x'=>35,'number_y'=>26.5,'number_width'=>40,'number_font_size'=>16,'number_font_color'=>'#19733d','number_align'=>'L')),
    'number-center'=>array_merge($base,array('number_x'=>10,'number_y'=>26.5,'number_width'=>80,'number_font_size'=>16,'number_font_color'=>'#245d8f','number_align'=>'C'))
);
foreach($cases as $name=>$data) {
    $pdf=(new Certificate_pdf())->content((object)$data);
    if (substr($pdf,0,5)!=='%PDF-') throw new RuntimeException('Invalid PDF');
    file_put_contents($directory.'/'.$name.'.pdf',$pdf);
}
echo "PASS: generated four fictional certificate PDFs using the real PDF library and existing background artwork\n";
