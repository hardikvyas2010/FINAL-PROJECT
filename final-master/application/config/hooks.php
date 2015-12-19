<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$hook['pre_controller'] = array(
    'class' => '',
    'function' => 'start',
    'filename' => 'CodeCoverage.php',
    'filepath' => 'hooks'
);

$hook['post_system'] = array(
    'class' => '',
    'function' => 'stop',
    'filename' => 'CodeCoverage.php',
    'filepath' => 'hooks'
);
