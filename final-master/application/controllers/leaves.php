<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


require_once FCPATH . "local/triggers/leave.php";


class Leaves extends CI_Controller {
    
    
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('leaves_model');
        $this->load->model('types_model');
        $this->lang->load('leaves', $this->language);
        $this->lang->load('global', $this->language);
    }
    
  
    public function index() {
        $this->auth->checkIfOperationIsAllowed('list_leaves');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['leaves'] = $this->leaves_model->getLeavesOfEmployee($this->session->userdata('id'));
        $data['title'] = lang('leaves_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_leave_requests_list');
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('leaves/index', $data);
        $this->load->view('templates/footer');
    }
    
  
    public function counters($refTmp = NULL) {
        $this->auth->checkIfOperationIsAllowed('counters_leaves');
        $data = getUserContext($this);
        $refDate = date("Y-m-d");
        if ($refTmp != NULL) {
            $refDate = date("Y-m-d", $refTmp);
            $data['isDefault'] = 0;
        } else {
            $data['isDefault'] = 1;
        }
        $data['refDate'] = $refDate;
        $data['summary'] = $this->leaves_model->getLeaveBalanceForEmployee($this->user_id, FALSE, $refDate);

        if (!is_null($data['summary'])) {
            $data['title'] = lang('leaves_summary_title');
            $data['help'] = $this->help->create_help_link('global_link_doc_page_my_summary');
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('leaves/counters', $data);
            $this->load->view('templates/footer');
        } else {
            $this->session->set_flashdata('msg', lang('leaves_summary_flash_msg_error'));
            redirect('leaves');
        }
    }

 
    public function view($source, $id) {
        $this->auth->checkIfOperationIsAllowed('view_leaves');
        $data = getUserContext($this);
        $data['leave'] = $this->leaves_model->getLeaves($id);
        if (empty($data['leave'])) {
            redirect('notfound');
        }
     
        if ($data['leave']['employee'] != $this->user_id) {
            if ((!$this->is_hr)) {
                $this->load->model('users_model');
                $employee = $this->users_model->getUsers($data['leave']['employee']);
                if ($employee['manager'] != $this->user_id) {
                    $this->load->model('delegations_model');
                    if (!$this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager'])) {
                        log_message('error', 'User #' . $this->user_id . ' illegally tried to view leave #' . $id);
                        redirect('leaves');
                    }
                }
            } 
        } 
        $data['source'] = $source;
        $data['title'] = lang('leaves_view_html_title');
        if ($source == 'requests') {
            if (empty($employee)) {
                $this->load->model('users_model');
                $data['name'] = $this->users_model->getName($data['leave']['employee']);
            } else {
                $data['name'] = $employee['firstname'] . ' ' . $employee['lastname'];
            }
        } else {
            $data['name'] = '';
        }
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('leaves/view', $data);
        $this->load->view('templates/footer');
    }

 
    public function create() {
        $this->auth->checkIfOperationIsAllowed('create_leaves');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $data['title'] = lang('leaves_create_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_request_leave');
        
        $this->form_validation->set_rules('startdate', lang('leaves_create_field_start'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('startdatetype', 'Start Date type', 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('enddate', lang('leaves_create_field_end'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('enddatetype', 'End Date type', 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('duration', lang('leaves_create_field_duration'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('type', lang('leaves_create_field_type'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('cause', lang('leaves_create_field_cause'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('status', lang('leaves_create_field_status'), 'required|xss_clean|strip_tags');

        $data['credit'] = 0;
        $default_type = $this->config->item('default_leave_type');
        $default_type = $default_type == FALSE ? 0 : $default_type;
        if ($this->form_validation->run() === FALSE) {
            $data['types'] = $this->types_model->getTypes();
            foreach ($data['types'] as $type) {
                if ($type['id'] == $default_type) {
                    $data['credit'] = $this->leaves_model->getLeavesTypeBalanceForEmployee($this->user_id, $type['name']);
                    break;
                }
            }
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('leaves/create');
            $this->load->view('templates/footer');
        } else {
            if (function_exists('triggerCreateLeaveRequest')) {
                triggerCreateLeaveRequest($this);
            }
            $leave_id = $this->leaves_model->setLeaves($this->session->userdata('id'));
            $this->session->set_flashdata('msg', lang('leaves_create_flash_msg_success'));
            
            if ($this->input->post('status') == 2) {
                $this->sendMail($leave_id);
            }
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('leaves');
            }
        }
    }
    
    public function edit($id) {
        $this->auth->checkIfOperationIsAllowed('edit_leaves');
        $data = getUserContext($this);
        $data['leave'] = $this->leaves_model->getLeaves($id);
       
        if (empty($data['leave'])) {
            redirect('notfound');
        }
        
        if (!$this->is_hr) {
            if (($this->session->userdata('manager') != $this->user_id) &&
                    $data['leave']['status'] != 1) {
                if ($this->config->item('edit_rejected_requests') == FALSE ||
                    $data['leave']['status'] != 4) {//Configuration switch that allows editing the rejected leave requests
                    log_message('error', 'User #' . $this->user_id . ' illegally tried to edit leave #' . $id);
                    $this->session->set_flashdata('msg', lang('leaves_edit_flash_msg_error'));
                    redirect('leaves');
                 }
            }
        } 
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        $data['title'] = lang('leaves_edit_html_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_request_leave');
        $data['id'] = $id;
        
        $data['credit'] = 0;
        $data['types'] = $this->types_model->getTypes();
        foreach ($data['types'] as $type) {
            if ($type['id'] == $data['leave']['type']) {
                $data['credit'] = $this->leaves_model->getLeavesTypeBalanceForEmployee($data['leave']['employee'], $type['name']);
                break;
            }
        }
        
        $this->form_validation->set_rules('startdate', lang('leaves_edit_field_start'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('startdatetype', 'Start Date type', 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('enddate', lang('leaves_edit_field_end'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('enddatetype', 'End Date type', 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('duration', lang('leaves_edit_field_duration'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('type', lang('leaves_edit_field_type'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('cause', lang('leaves_edit_field_cause'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('status', lang('leaves_edit_field_status'), 'required|xss_clean|strip_tags');

        if ($this->form_validation->run() === FALSE) {
            $this->load->model('users_model');
            $data['name'] = $this->users_model->getName($data['leave']['employee']);
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('leaves/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $this->leaves_model->updateLeaves($id);      
            $this->session->set_flashdata('msg', lang('leaves_edit_flash_msg_success'));
           
            if ($this->input->post('status') == 2) {
                $this->sendMail($id);
            }
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('leaves');
            }
        }
    }
    
   
    private function sendMail($id) {
        $this->load->model('users_model');
        $this->load->model('types_model');
        $this->load->model('delegations_model');
       
        $leave = $this->leaves_model->getLeaves($id);
        $user = $this->users_model->getUsers($leave['employee']);
        $manager = $this->users_model->getUsers($user['manager']);

        
        if (empty($manager['email'])) {
            $this->session->set_flashdata('msg', lang('leaves_create_flash_msg_error'));
        } else {
           
            $this->load->library('email');
            $this->load->library('polyglot');
            $usr_lang = $this->polyglot->code2language($manager['language']);
            
            
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            $lang_mail->load('global', $usr_lang);
            
            $date = new DateTime($leave['startdate']);
            $startdate = $date->format($lang_mail->line('global_date_format'));
            $date = new DateTime($leave['enddate']);
            $enddate = $date->format($lang_mail->line('global_date_format'));

            $this->load->library('parser');
            $data = array(
                'Title' => $lang_mail->line('email_leave_request_title'),
                'Firstname' => $user['firstname'],
                'Lastname' => $user['lastname'],
                'StartDate' => $startdate,
                'EndDate' => $enddate,
                'StartDateType' => $lang_mail->line($leave['startdatetype']),
                'EndDateType' => $lang_mail->line($leave['enddatetype']),
                'Type' => $this->types_model->getName($leave['type']),
                'Duration' => $leave['duration'],
                'Balance' => $this->leaves_model->getLeavesTypeBalanceForEmployee($leave['employee'] , $leave['type_name'], $leave['startdate']),
                'Reason' => $leave['cause'],
                'BaseUrl' => $this->config->base_url(),
                'LeaveId' => $id,
                'UserId' => $this->user_id
            );
            $message = $this->parser->parse('emails/' . $manager['language'] . '/request', $data, TRUE);
            $this->email->set_encoding('quoted-printable');
            
            if ($this->config->item('from_mail') != FALSE && $this->config->item('from_name') != FALSE ) {
                $this->email->from($this->config->item('from_mail'), $this->config->item('from_name'));
            } else {
               $this->email->from('do.not@reply.me', 'LMS');
            }
            $this->email->to($manager['email']);
            if ($this->config->item('subject_prefix') != FALSE) {
                $subject = $this->config->item('subject_prefix');
            } else {
               $subject = '[Jorani] ';
            }
         
            $delegates = $this->delegations_model->listMailsOfDelegates($manager['id']);
            if ($delegates != '') {
                $this->email->cc($delegates);
            }
            
            $this->email->subject($subject . $lang_mail->line('email_leave_request_subject') . ' ' .
                    $this->session->userdata('firstname') . ' ' .
                    $this->session->userdata('lastname'));
            $this->email->message($message);
            $this->email->send();
        }
    }

  
    public function delete($id) {
        $can_delete = FALSE;
      
        $leaves = $this->leaves_model->getLeaves($id);
        if (empty($leaves)) {
            redirect('notfound');
        } else {
            if ($this->is_hr) {
                $can_delete = TRUE;
            } else {
                if ($leaves['status'] == 1 ) {
                    $can_delete = TRUE;
                }
                if ($this->config->item('delete_rejected_requests') == TRUE ||
                    $leaves['status'] == 4) {
                    $can_delete = TRUE;
                }
            }
            if ($can_delete === TRUE) {
                $this->leaves_model->deleteLeave($id);
            } else {
                $this->session->set_flashdata('msg', lang('leaves_delete_flash_msg_error'));
                if (isset($_GET['source'])) {
                    redirect($_GET['source']);
                } else {
                    redirect('leaves');
                }
            }
        }
        $this->session->set_flashdata('msg', lang('leaves_delete_flash_msg_success'));
        if (isset($_GET['source'])) {
            redirect($_GET['source']);
        } else {
            redirect('leaves');
        }
    }

  
    public function export() {
        $this->load->library('excel');
        $this->load->view('leaves/export');
    }

  
    public function individual($id = 0) {
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        if ($id == 0) $id =$this->session->userdata('id');
        echo $this->leaves_model->individual($id, $start, $end);
    }

   
    public function workmates() {
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        echo $this->leaves_model->workmates($this->session->userdata('manager'), $start, $end);
    }
    
  
    public function collaborators() {
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        echo $this->leaves_model->collaborators($this->user_id, $start, $end);
    }

  
    public function organization($entity_id) {
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        $children = filter_var($this->input->get('children', TRUE), FILTER_VALIDATE_BOOLEAN);
        echo $this->leaves_model->department($entity_id, $start, $end, $children);
    }


    public function department() {
        header("Content-Type: application/json");
        $this->load->model('organization_model');
        $department = $this->organization_model->getDepartment($this->user_id);
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        echo $this->leaves_model->department($department[0]['id'], $start, $end);
    }
    
  
    public function validate() {
        header("Content-Type: application/json");
        $id = $this->input->post('id', TRUE);
        $type = $this->input->post('type', TRUE);
        $startdate = $this->input->post('startdate', TRUE);
        $enddate = $this->input->post('enddate', TRUE);
        $startdatetype = $this->input->post('startdatetype', TRUE);
        $enddatetype = $this->input->post('enddatetype', TRUE);
        $leave_id = $this->input->post('leave_id', TRUE);
        $leaveValidator = new stdClass;
        if (isset($id) && isset($type)) {
            if (isset($startdate) && $startdate !== "") {
                $leaveValidator->credit = $this->leaves_model->getLeavesTypeBalanceForEmployee($id, $type, $startdate);
            } else {
                $leaveValidator->credit = $this->leaves_model->getLeavesTypeBalanceForEmployee($id, $type);
            }
        }
        if (isset($id) && isset($startdate) && isset($enddate)) {
            if (isset($startdatetype) && isset($enddatetype)) {
                $leaveValidator->length = $this->leaves_model->length($id, $startdate, $enddate, $startdatetype, $enddatetype);
                if (isset($leave_id)) {
                    $leaveValidator->overlap = $this->leaves_model->detectOverlappingLeaves($id, $startdate, $enddate, $startdatetype, $enddatetype, $leave_id);
                } else {
                    $leaveValidator->overlap = $this->leaves_model->detectOverlappingLeaves($id, $startdate, $enddate, $startdatetype, $enddatetype);
                }
            }
        }
        
        $this->load->model('contracts_model');
        $startentdate = NULL;
        $endentdate = NULL;
        $hasContract = $this->contracts_model->getBoundaries($id, $startentdate, $endentdate);
        $leaveValidator->startentdate = $startentdate;
        $leaveValidator->endentdate = $endentdate;
        $leaveValidator->hasContract = $hasContract;
        echo json_encode($leaveValidator);
    }
}
