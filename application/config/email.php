<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* Keep the SMTP password outside source control. */
$config['protocol']     = 'smtp';
$config['smtp_host']    = 'smtp.gmail.com';
$config['smtp_port']    = 587;
$config['smtp_user']    = 'amnat@aru.ac.th';
$config['smtp_pass']    = 'cidjzdmewndpogoz';
$config['smtp_crypto']  = 'tls';
$config['smtp_timeout'] = 30;

$config['mailtype'] = 'html';
$config['charset']  = 'utf-8';
$config['newline']  = "\r\n";
$config['crlf']     = "\r\n";
$config['wordwrap'] = TRUE;
