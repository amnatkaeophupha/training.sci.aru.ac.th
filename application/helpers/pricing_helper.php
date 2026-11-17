<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Parse money without trusting floating point values or silently rounding input. */
function training_money_cents($value)
{
    if (!is_scalar($value) || !preg_match('/\A([0-9]{1,8})(?:\.([0-9]{1,2}))?\z/D',trim((string)$value),$parts)) return FALSE;
    return (int)$parts[1]*100+(int)str_pad(isset($parts[2])?$parts[2]:'',2,'0');
}
function training_money_decimal($cents)
{
    return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT);
}
function training_fee_total($unit,$people)
{
    $cents=training_money_cents($unit);
    return training_money_decimal(($cents===FALSE?0:$cents)*max(1,(int)$people));
}
function training_fee_token($session)
{
    $token=$session->userdata('course_fee_token');
    if (!is_string($token) || $token==='') { $token=bin2hex(random_bytes(32)); $session->set_userdata('course_fee_token',$token); }
    return $token;
}
function training_fee_token_valid($session,$token)
{
    $expected=$session->userdata('course_fee_token');
    return is_string($token) && is_string($expected) && $expected!=='' && hash_equals($expected,$token);
}
