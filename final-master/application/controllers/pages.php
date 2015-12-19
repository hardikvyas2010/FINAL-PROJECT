<?php


if (!defined('BASEPATH')) {exit('No direct script access allowed');}


class Pages extends CI_Controller {
   
  
    public function __construct() {
        parent::__construct();
        setUserContext($this);
    }

    
    public function notfound() {
        $data = getUserContext($this);
        $data['title'] = 'Error';
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('pages/notfound', $data);
        $this->load->view('templates/footer', $data);
    }
    
   
    public function view($page = 'home') {
        $data = getUserContext($this);
        $trans = array("-" => " ", "_" => " ", "." => " ");
        $data['title'] = ucfirst(strtr($page, $trans)); 
        
        if (strpos($page,'export') === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
        }
        $view = 'pages/' . $this->language_code .'/' . $page . '.php';
        $pathCI = APPPATH . 'views/';
        $pathLocal = FCPATH .'local/';
        
        if (file_exists($pathLocal . $view)) {
            $this->load->customView($pathLocal, $view, $data);
        } else {
            if (!file_exists($pathCI . $view)) {
                    redirect('notfound');
            }
            $this->load->view($view, $data);
        }
        if (strpos($page,'export') === FALSE) {
            $this->load->view('templates/footer', $data);
        }
    }

}
