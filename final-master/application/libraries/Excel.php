<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }
require_once APPPATH . "third_party/PHPExcel.php";


class Excel extends PHPExcel {
    
    
    public function __construct() {
        parent::__construct(); 
    }
    
 
    public function column_name($number) {
        if ($number < 27) {
            return substr("ABCDEFGHIJKLMNOPQRSTUVWXYZ", $number - 1, 1);
        } else {
            return substr("AAABACADAEAFAGAHAIAJAKALAMANAOAPAQARASATAUAVAWAXAYAZ", (($number -27) * 2), 2);
        }
    }

}
