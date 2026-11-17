<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function certificate_number_defaults()
{
    return array('number_x'=>70.707, 'number_y'=>93.333, 'number_width'=>25.253,
        'number_font_size'=>9, 'number_font_color'=>'#282828', 'number_align'=>'R');
}

function certificate_number_text($number)
{
    return preg_match('/^SCI-[0-9]{4}-[0-9]{6}$/D', $number) ? $number : 'เลขที่ '.$number;
}
