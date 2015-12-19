<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Contracts extends CI_Controller {
    
    
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->lang->load('contract', $this->language);
        $this->load->model('contracts_model');
    }
    
   
    public function index() {
        $this->auth->checkIfOperationIsAllowed('list_contracts');
        $this->lang->load('datatable', $this->language);
        $data = getUserContext($this);
        $data['title'] = lang('contract_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_contracts_list');
        $data['contracts'] = $this->contracts_model->getContracts();
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('contracts/index', $data);
        $this->load->view('templates/footer');
    }
    
   
    public function edit($id) {
        $this->auth->checkIfOperationIsAllowed('edit_contract');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->lang->load('calendar', $this->language);
        $data['title'] = lang('contract_edit_title');
        
        $this->form_validation->set_rules('name', lang('contract_edit_field_name'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('startentdatemonth', lang('contract_edit_field_start_month'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('startentdateday', lang('contract_edit_field_start_day'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('endentdatemonth', lang('contract_edit_field_end_month'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('endentdateday', lang('contract_edit_field_end_day'), 'required|xss_clean|strip_tags');

        $data['contract'] = $this->contracts_model->getContracts($id);
        if (empty($data['contract'])) {
            redirect('notfound');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('contracts/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $this->contracts_model->updateContract();
            $this->session->set_flashdata('msg', lang('contract_edit_msg_success'));
            redirect('contracts');
        }
    }
    
   
    public function create() {
        $this->auth->checkIfOperationIsAllowed('create_contract');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->lang->load('calendar', $this->language);
        $data['title'] = lang('contract_create_title');

        $this->form_validation->set_rules('name', lang('contract_create_field_name'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('startentdatemonth', lang('contract_create_field_start_month'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('startentdateday', lang('contract_create_field_start_day'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('endentdatemonth', lang('contract_create_field_end_month'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('endentdateday', lang('contract_create_field_end_day'), 'required|xss_clean|strip_tags');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('contracts/create', $data);
            $this->load->view('templates/footer');
        } else {
            $this->contracts_model->setContracts();
            $this->session->set_flashdata('msg', lang('contract_create_msg_success'));
            redirect('contracts');
        }
    }
 
    public function delete($id) {
        $this->auth->checkIfOperationIsAllowed('delete_contract');
       
        $data['contract'] = $this->contracts_model->getContracts($id);
        if (empty($data['contract'])) {
            redirect('notfound');
        } else {
            $this->contracts_model->deleteContract($id);
        }
        $this->session->set_flashdata('msg', lang('contract_delete_msg_success'));
        redirect('contracts');
    }
    

    public function calendar($id, $year = 0) {
        $this->auth->checkIfOperationIsAllowed('calendar_contract');
        $data = getUserContext($this);
        $this->lang->load('calendar', $this->language);
        $data['title'] = lang('contract_calendar_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_contracts_calendar');
        if ($year <> 0) {
            $data['year'] = $year;
        } else {
            $data['year'] = date("Y");
        }
        
    
        $data['contracts'] = $this->contracts_model->getContracts();
        
        foreach ($data['contracts'] as $key => $value) {
            if ($value['id'] == $id) {
                unset($data['contracts'][$key]);
                break;
            }
        }
        $contract = $this->contracts_model->getContracts($id);
        $data['contract_id'] = $id;
        $data['contract_name'] = $contract['name'];
        $data['contract_start_month'] = intval(substr($contract['startentdate'], 0, 2));
        $data['contract_start_day'] = intval(substr($contract['startentdate'], 3));
        $data['contract_end_month'] = intval(substr($contract['endentdate'], 0, 2));
        $data['contract_end_day'] = intval(substr($contract['endentdate'], 3));
        $this->load->model('dayoffs_model');
        $data['dayoffs'] = $this->dayoffs_model->getDaysOffForCivilYear($id, $data['year']);
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('contracts/calendar', $data);
        $this->load->view('templates/footer');
    }
    
   
    public function copydayoff($source, $destination, $year) {
        $this->auth->checkIfOperationIsAllowed('calendar_contract');
        $this->load->model('dayoffs_model');
        $this->dayoffs_model->copyListOfDaysOff($source, $destination, $year);
        
        $this->session->set_flashdata('msg', lang('contract_calendar_copy_msg_success'));
        redirect('contracts/' . $destination . '/calendar/' . $year);
    }

  
    public function editdayoff() {
        if ($this->auth->isAllowed('adddayoff_contract') === FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $contract = $this->input->post('contract', TRUE);
            $timestamp = $this->input->post('timestamp', TRUE);
            $type = $this->input->post('type', TRUE);
            $title = sanitize($this->input->post('title', TRUE));
            if (isset($contract) && isset($timestamp) && isset($type) && isset($title)) {
                $this->load->model('dayoffs_model');
                $this->output->set_content_type('text/plain');
                if ($type == 0) {
                    echo $this->dayoffs_model->deleteDayOff($contract, $timestamp);
                } else {
                    echo $this->dayoffs_model->addDayOff($contract, $timestamp, $type, $title);
                }
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }
    

    public function series() {
        if ($this->auth->isAllowed('adddayoff_contract') === FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            if (($this->input->post('day', TRUE) != NULL) && ($this->input->post('type', TRUE) != NULL) &&
                    ($this->input->post('start', TRUE) != NULL) && ($this->input->post('end', TRUE) != NULL)
                     && ($this->input->post('contract', TRUE) != NULL)) {
                header("Content-Type: text/plain");

               
                $start = strtotime($this->input->post('start', TRUE));
                $end = strtotime($this->input->post('end', TRUE));
                $type = $this->input->post('type', TRUE);
                $freq = $this->input->post('day', TRUE);
                if ($freq == "all") {
                    $day = $start;
                } else {
                    $day = strtotime($freq, $start);
                }
                
                $list = '';
                while ($day <= $end) {
                    $list .= date("Y-m-d", $day) . ",";
                    if ($freq == "all") {
                        $day = strtotime("+1 day", $day);
                    } else {
                        $day = strtotime("+1 weeks", $day);
                    }
                }
                $list = rtrim($list, ",");
                $contract = $this->input->post('contract', TRUE);
                $title = sanitize($this->input->post('title', TRUE));
                $this->load->model('dayoffs_model');
                $this->dayoffs_model->deleteListOfDaysOff($contract, $list);
                if ($type != 0) {
                    $this->dayoffs_model->addListOfDaysOff($contract, $type, $title, $list);
                    echo 'updated';
                } else {
                    echo 'deleted';
                }
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }

 
    public function import() {
        header("Content-Type: plain/text");
        $contract = $this->input->post('contract', TRUE);
        $url = $this->input->post('url', TRUE);
       
        if (!filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            $headers = @get_headers($url);
            if(strpos($headers[0],'200') === FALSE) { 
                echo("$url was not found or distant server is not reachable");
            }
            else {
                $this->load->model('dayoffs_model');
                $this->dayoffs_model->importDaysOffFromICS($contract, $url);
            }
        } else {
            echo("$url is not a valid URL");
        }
    }
    
  
    public function userDayoffs($id = 0) {
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        $this->load->model('dayoffs_model');
        if ($id == 0) $id =$this->user_id;
        echo $this->dayoffs_model->userDayoffs($id, $start, $end);
    }
    
   
    public function allDayoffs() {
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        $entity = $this->input->get('entity', TRUE);
        $children = filter_var($this->input->get('children', TRUE), FILTER_VALIDATE_BOOLEAN);
        $this->load->model('dayoffs_model');
        echo $this->dayoffs_model->allDayoffs($start, $end, $entity, $children);
    }
    
  
    public function export() {
        $this->auth->checkIfOperationIsAllowed('export_contracts');
        $this->load->library('excel');
        $this->load->view('contracts/export');
    }
}
