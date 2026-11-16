<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Normalize an optional batch identifier without accepting arrays or partial numbers. */
function training_batch_id($value)
{
    if (!is_string($value) && !is_int($value)) return 0;
    return (int) filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
}
