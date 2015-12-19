<?php

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Admin extends CI_Controller {
    
  
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->lang->load('global', $this->language);
    }
    

    public function settings() {
        $this->auth->checkIfOperationIsAllowed('list_settings');
        $data = getUserContext($this);
        $data['title'] = 'application/config/config.php';
        $data['help'] = ''; 
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('admin/settings', $data);
        $this->load->view('templates/footer');
    }
}
