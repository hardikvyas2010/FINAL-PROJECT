<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Entitleddays extends CI_Controller {
   
  
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('entitleddays_model');
        $this->lang->load('entitleddays', $this->language);
    }

  
    public function user($id) {
        $this->auth->checkIfOperationIsAllowed('entitleddays_user');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['id'] = $id;
        $data['entitleddays'] = $this->entitleddays_model->getEntitledDaysForEmployee($id);
        $this->load->model('types_model');
        $data['types'] = $this->types_model->getTypes();
        $this->load->model('users_model');
        $user = $this->users_model->getUsers($id);
        $data['employee_name'] = $user['firstname'] . ' ' . $user['lastname'];
        
        if (!empty ($user['contract'])) {
            $this->load->model('contracts_model');
            $contract = $this->contracts_model->getContracts($user['contract']);
            $data['contract_name'] = $contract['name'];
            $data['contract_start_month'] = intval(substr($contract['startentdate'], 0, 2));
            $data['contract_start_day'] = intval(substr($contract['startentdate'], 3));
            $data['contract_end_month'] = intval(substr($contract['endentdate'], 0, 2));
            $data['contract_end_day'] = intval(substr($contract['endentdate'], 3));
        } else {
            $data['contract_name'] = '';
        }
        
        $data['title'] = lang('entitleddays_user_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_entitleddays_employee');
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('entitleddays/user', $data);
        $this->load->view('templates/footer');
    }
    
  
    public function contract($id) {
        $this->auth->checkIfOperationIsAllowed('entitleddays_contract');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['id'] = $id;
        $data['entitleddays'] = $this->entitleddays_model->getEntitledDaysForContract($id);
        $this->load->model('types_model');
        $data['types'] = $this->types_model->getTypes();
        $this->load->model('contracts_model');
        $contract = $this->contracts_model->getContracts($id);
        $data['contract_name'] = $contract['name'];
        $data['contract_start_month'] = intval(substr($contract['startentdate'], 0, 2));
        $data['contract_start_day'] = intval(substr($contract['startentdate'], 3));
        $data['contract_end_month'] = intval(substr($contract['endentdate'], 0, 2));
        $data['contract_end_day'] = intval(substr($contract['endentdate'], 3));
        
        $data['title'] = lang('entitleddays_contract_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_entitleddays_contract');
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('entitleddays/contract', $data);
        $this->load->view('templates/footer');
    }
    
  
    public function organization() {
        setUserContext($this);
        $this->auth->checkIfOperationIsAllowed('entitleddays_user');
        $data = getUserContext($this);
        $this->lang->load('organization', $this->language);
        $this->lang->load('datatable', $this->language);
        $this->lang->load('treeview', $this->language);
        $data['title'] = lang('organization_index_title');
        $data['help'] = '';
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('entitleddays/organization', $data);
        $this->load->view('templates/footer');
    }
    
   
    public function userdelete($id) {
        $this->auth->checkIfOperationIsAllowed('entitleddays_user_delete');
        $this->output->set_content_type('text/plain');
        echo $this->entitleddays_model->deleteEntitledDays($id);
    }
    
  
    public function contractdelete($id) {
        $this->auth->checkIfOperationIsAllowed('entitleddays_contract_delete');
        $this->output->set_content_type('text/plain');
        echo $this->entitleddays_model->deleteEntitledDays($id);
    }
    
  
    public function ajax_user() {
        if ($this->auth->isAllowed('entitleddays_user') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $user_id = $this->input->post('user_id', TRUE);
            $startdate = $this->input->post('startdate', TRUE);
            $enddate = $this->input->post('enddate', TRUE);
            $days = $this->input->post('days', TRUE);
            $type = $this->input->post('type', TRUE);
            $description = sanitize($this->input->post('description', TRUE));
            if (isset($startdate) && isset($enddate) && isset($days) && isset($type) && isset($user_id)) {
                $this->output->set_content_type('text/plain');
                $id = $this->entitleddays_model->addEntitledDaysToEmployee($user_id, $startdate, $enddate, $days, $type, $description);
                echo $id;
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }
    
   
    public function ajax_contract() {
        if ($this->auth->isAllowed('entitleddays_user') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $contract_id = $this->input->post('contract_id', TRUE);
            $startdate = $this->input->post('startdate', TRUE);
            $enddate = $this->input->post('enddate', TRUE);
            $days = $this->input->post('days', TRUE);
            $type = $this->input->post('type', TRUE);
            $description = sanitize($this->input->post('description', TRUE));
            if (isset($startdate) && isset($enddate) && isset($days) && isset($type) && isset($contract_id)) {
                $this->output->set_content_type('text/plain');
                $id = $this->entitleddays_model->addEntitledDaysToContract($contract_id, $startdate, $enddate, $days, $type, $description);
                echo $id;
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }
    
  
    public function ajax_update() {
        if ($this->auth->isAllowed('entitleddays_user') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->post('id', TRUE);
            $operation = $this->input->post('operation', TRUE);   
            if (isset($id) && isset($operation)) {
                $this->output->set_content_type('text/plain');
                $days = $this->input->post('days', TRUE);
                switch ($operation) {
                    case  "increase":
                        $id = $this->entitleddays_model->increase($id, $days);
                        break;
                    case "decrease":
                        $id = $this->entitleddays_model->decrease($id, $days);
                        break;
                    case "credit":
                        $id = $this->entitleddays_model->updateNbOfDaysOfEntitledDaysRecord($id, $days);
                        break;
                    case "update":
                        $startdate = $this->input->post('startdate', TRUE);
                        $enddate = $this->input->post('enddate', TRUE);
                        $type = $this->input->post('type', TRUE);
                        $description = sanitize($this->input->post('description', TRUE));
                        $id = $this->entitleddays_model->updateEntitledDays($id, $startdate, $enddate, $days, $type, $description);
                        break;
                    default:
                        $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
                }
                echo $id;
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }

}
