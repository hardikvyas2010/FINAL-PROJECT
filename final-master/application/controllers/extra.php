<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


require_once FCPATH . "local/triggers/extra.php";


class Extra extends CI_Controller {
    
   
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('overtime_model');
        $this->lang->load('extra', $this->language);
        $this->lang->load('global', $this->language);
    }

  
    public function index() {
        $this->auth->checkIfOperationIsAllowed('list_extra');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['extras'] = $this->overtime_model->getExtrasOfEmployee($this->user_id);
        $data['title'] = lang('extra_index_title');
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('extra/index', $data);
        $this->load->view('templates/footer');
    }
    
  
    public function view($source, $id) {
        $this->auth->checkIfOperationIsAllowed('view_extra');
        $data = getUserContext($this);
        $data['extra'] = $this->overtime_model->getExtras($id);
        if (empty($data['extra'])) {
            redirect('notfound');
        }
        
       
        if ($data['extra']['employee'] != $this->user_id) {
            if ((!$this->is_hr)) {
                $this->load->model('users_model');
                $employee = $this->users_model->getUsers($data['extra']['employee']);
                if ($employee['manager'] != $this->user_id) {
                    $this->load->model('delegations_model');
                    if (!$this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager'])) {
                        log_message('error', 'User #' . $this->user_id . ' illegally tried to view overtime #' . $id);
                        redirect('extra');
                    }
                }
            } 
        } 
        
        $data['title'] = lang('extra_view_hmtl_title');
        $data['source'] = $source;
        if ($source == 'overtime') {
            if (empty($employee)) {
                $this->load->model('users_model');
                $data['name'] = $this->users_model->getName($data['extra']['employee']);
            } else {
                $data['name'] = $employee['firstname'] . ' ' . $employee['lastname'];
            }
        } else {
            $data['name'] = '';
        }
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('extra/view', $data);
        $this->load->view('templates/footer');
    }

    
    public function create() {
        $this->auth->checkIfOperationIsAllowed('create_extra');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('date', lang('extra_create_field_date'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('duration', lang('extra_create_field_duration'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('cause', lang('extra_create_field_cause'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('status', lang('extra_create_field_status'), 'required|xss_clean|strip_tags');

        if ($this->form_validation->run() === FALSE) {
            $data['title'] = lang('extra_create_title');
            $data['help'] = $this->help->create_help_link('global_link_doc_page_create_overtime');
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('extra/create');
            $this->load->view('templates/footer');
        } else {
            if (function_exists('triggerCreateExtraRequest')) {
                triggerCreateExtraRequest($this);
            }
            $extra_id = $this->overtime_model->setExtra();
            $this->session->set_flashdata('msg', lang('extra_create_msg_success'));
           
            if ($this->input->post('status') == 2) {
                $this->sendMail($extra_id);
            }
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('extra');
            }
        }
    }
    
  
    public function edit($id) {
        $this->auth->checkIfOperationIsAllowed('edit_extra');
        $data = getUserContext($this);
        $data['extra'] = $this->overtime_model->getExtras($id);
      
        if (empty($data['extra'])) {
            redirect('notfound');
        }
      
        if (!$this->is_hr) {
            if (($this->session->userdata('manager') != $this->user_id) &&
                    $data['extra']['status'] != 1) {
                log_message('error', 'User #' . $this->user_id . ' illegally tried to edit overtime request #' . $id);
                $this->session->set_flashdata('msg', lang('extra_edit_msg_error'));
                redirect('extra');
            }
        } 
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('date', lang('extra_edit_field_date'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('duration', lang('extra_edit_field_duration'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('cause', lang('extra_edit_field_cause'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('status', lang('extra_edit_field_status'), 'required|xss_clean|strip_tags');

        if ($this->form_validation->run() === FALSE) {
            $data['title'] = lang('extra_edit_hmtl_title');
            $data['help'] = $this->help->create_help_link('global_link_doc_page_create_overtime');
            $data['id'] = $id;
            $this->load->model('users_model');
            $data['name'] = $this->users_model->getName($data['extra']['employee']);
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('extra/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $this->overtime_model->updateExtra($id);      
            $this->session->set_flashdata('msg', lang('extra_edit_msg_success'));
            
            if ($this->input->post('status') == 2) {
                $this->sendMail($id);
            }
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('extra');
            }
        }
    }
    
    
    private function sendMail($id) {
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $manager = $this->users_model->getUsers($this->session->userdata('manager'));

        
        if (empty($manager['email'])) {
            $this->session->set_flashdata('msg', lang('extra_create_msg_error'));
        } else {
            $acceptUrl = base_url() . 'overtime/accept/' . $id;
            $rejectUrl = base_url() . 'overtime/reject/' . $id;

            
            $this->load->library('email');
            $this->load->library('polyglot');
            $usr_lang = $this->polyglot->code2language($manager['language']);
            
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            $lang_mail->load('global', $usr_lang);

            $date = new DateTime($this->input->post('date'));
            $startdate = $date->format($lang_mail->line('global_date_format'));

            $this->load->library('parser');
            $data = array(
                'Title' => $lang_mail->line('email_extra_request_validation_title'),
                'Firstname' => $this->session->userdata('firstname'),
                'Lastname' => $this->session->userdata('lastname'),
                'Date' => $startdate,
                'Duration' => $this->input->post('duration'),
                'Cause' => $this->input->post('cause'),
                'UrlAccept' => $acceptUrl,
                'UrlReject' => $rejectUrl
            );
            $message = $this->parser->parse('emails/' . $manager['language'] . '/overtime', $data, TRUE);
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
            $this->email->subject($subject . $lang_mail->line('email_extra_request_reject_subject') . ' ' .
                    $this->session->userdata('firstname') . ' ' .
                    $this->session->userdata('lastname'));
            $this->email->message($message);
            $this->email->send();
        }
    }

    
    public function delete($id) {
        $can_delete = FALSE;
       
        $extra = $this->overtime_model->getExtras($id);
        if (empty($extra)) {
            redirect('notfound');
        } else {
            if ($this->is_hr) {
                $can_delete = TRUE;
            } else {
                if ($extra['status'] == 1 ) {
                    $can_delete = TRUE;
                }
            }
            if ($can_delete === TRUE) {
                $this->overtime_model->deleteExtra($id);
                $this->session->set_flashdata('msg', lang('extra_delete_msg_success'));
            } else {
                $this->session->set_flashdata('msg', lang('extra_delete_msg_error'));
            }
        }
        if (isset($_GET['source'])) {
            redirect($_GET['source']);
        } else {
            redirect('extra');
        }
    }
    
   
    public function export() {
        $this->load->library('excel');
        $this->load->view('extra/export');
    }
}
