<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Organization extends CI_Controller {
    
  
    public function __construct() {
        parent::__construct();
       
    }

  
    public function index() {
        setUserContext($this);
        $this->auth->checkIfOperationIsAllowed('organization_index');
        $data = getUserContext($this);
        $this->lang->load('organization', $this->language);
        $this->lang->load('datatable', $this->language);
        $this->lang->load('treeview', $this->language);
        $data['title'] = lang('organization_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_hr_organization');
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('organization/index', $data);
        $this->load->view('templates/footer');
    }

  
    public function select() {
        if (($this->config->item('public_calendar') == TRUE) && (!$this->session->userdata('logged_in'))) {
            $this->load->library('polyglot');
            $data['language'] = $this->config->item('language');
            $data['language_code'] = $this->polyglot->language2code($data['language']);
            $this->lang->load('organization', $data['language']);
            $this->lang->load('treeview', $data['language']);
            $data['help'] = '';
            $data['logged_in'] = FALSE;
            $this->load->view('organization/select', $data);
        } else {
            setUserContext($this);
            $this->auth->checkIfOperationIsAllowed('organization_select');
            $data = getUserContext($this);
            $this->lang->load('organization', $this->language);
            $this->lang->load('treeview', $this->language);
            $this->load->view('organization/select', $data);
        }
    }

 
    public function rename() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->get('id', TRUE);
            $text = sanitize($this->input->get('text', TRUE));
            $this->load->model('organization_model');
            $this->organization_model->rename($id, $text);
        }
    }
    
   
    public function create() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->get('id', TRUE);
            $text = sanitize($this->input->get('text', TRUE));
            $this->load->model('organization_model');
            $this->organization_model->create($id, $text);
        }
    }
    
    
    public function move() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->get('id', TRUE);
            $parent = $this->input->get('parent', TRUE);
            $this->load->model('organization_model');
            $this->organization_model->move($id, $parent);
        }
    }
    
    
    public function copy() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->get('id', TRUE);
            $parent = $this->input->get('parent', TRUE);
            $this->load->model('organization_model');
            $this->organization_model->copy($id, $parent);
        }
    }

  
    public function employees() {
        header("Content-Type: application/json");
        setUserContext($this);
        $id = $this->input->get('id', TRUE);
        $this->load->model('organization_model');
        $employees = $this->organization_model->employees($id)->result();
        $msg = '{"iTotalRecords":' . count($employees);
        $msg .= ',"iTotalDisplayRecords":' . count($employees);
        $msg .= ',"aaData":[';
        foreach ($employees as $employee) {
            $msg .= '["' . $employee->id . '",';
            $msg .= '"' . $employee->firstname . '",';
            $msg .= '"' . $employee->lastname . '",';
            $msg .= '"' . $employee->email . '"';
            $msg .= '],';
        }
        $msg = rtrim($msg, ",");
        $msg .= ']}';
        echo $msg;
    }
    
   
    public function employeesDateHired() {
        header("Content-Type: application/json");
        setUserContext($this);
        $id = $this->input->get('id', TRUE);
        $this->load->model('organization_model');
        $employees = $this->organization_model->employees($id)->result();
        $msg = '{"iTotalRecords":' . count($employees);
        $msg .= ',"iTotalDisplayRecords":' . count($employees);
        $msg .= ',"aaData":[';
        foreach ($employees as $employee) {
            $msg .= '["' . $employee->id . '",';
            $msg .= '"' . $employee->firstname . " " . $employee->lastname . '",';
            $msg .= '"' . $employee->datehired . '"';
            $msg .= '],';
        }
        $msg = rtrim($msg, ",");
        $msg .= ']}';
        echo $msg;
    }
    
  
    public function addemployee() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->get('user', TRUE);
            $entity = $this->input->get('entity', TRUE);
            $this->load->model('organization_model');
            echo json_encode($this->organization_model->attachEmployee($id, $entity));
        }
    }   
    
   
    public function delemployee() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $id = $this->input->get('user', TRUE);
            $this->load->model('organization_model');
            echo json_encode($this->organization_model->detachEmployee($id));
        }
    } 
    
  
    public function delete() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $entity = $this->input->get('entity', TRUE);
            $this->load->model('organization_model');
            echo json_encode($this->organization_model->delete($entity));
        }
    }
    
  
    public function root() {
        header("Content-Type: application/json");
        if (($this->config->item('public_calendar') == TRUE) && (!$this->session->userdata('logged_in'))) {
            
        } else {
            setUserContext($this);
            $this->auth->checkIfOperationIsAllowed('organization_select');
        }
        
        $id = $this->input->get('id', TRUE);
        if ($id == "#") {
            unset($id);
        }
        $this->load->model('organization_model');
        $entities = $this->organization_model->getAllEntities();
        $msg = '[';
        foreach ($entities->result() as $entity) {
            $msg .= '{"id":"' . $entity->id . '",';
            if ($entity->parent_id == -1) {
                $msg .= '"parent":"#",';
            } else {
                $msg .= '"parent":"' . $entity->parent_id . '",';
            }
            $msg .= '"text":"' . $entity->name . '"';
            $msg .= '},';
        }
        $msg = rtrim($msg, ",");
        $msg .= ']';
        echo $msg;
    }
    
   
    public function getsupervisor() {
        header("Content-Type: application/json");
        setUserContext($this);
        $entity = $this->input->get('entity', TRUE);
        if (isset($entity)) {
            $this->load->model('organization_model');
            echo json_encode($this->organization_model->getSupervisor($entity));
        } else {
            $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
        }
    }

  
    public function setsupervisor() {
        header("Content-Type: application/json");
        setUserContext($this);
        if ($this->auth->isAllowed('edit_organization') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            if ($this->input->get('user', TRUE) == "") {
                $id = NULL;
            } else {
                $id = $this->input->get('user', TRUE);
            }
            $entity = $this->input->get('entity', TRUE);
            $this->load->model('organization_model');
            echo json_encode($this->organization_model->setSupervisor($id, $entity));
        }
    }
}
