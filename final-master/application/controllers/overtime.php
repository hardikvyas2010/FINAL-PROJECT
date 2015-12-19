<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Overtime extends CI_Controller {
    
 
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('overtime_model');
        $this->lang->load('overtime', $this->language);
        $this->lang->load('global', $this->language);
    }

   
    public function index($filter = 'requested') {
        $this->auth->checkIfOperationIsAllowed('list_overtime');
        if ($filter == 'all') {
            $showAll = TRUE;
        } else {
            $showAll = FALSE;
        }
        
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['filter'] = $filter;
        $data['title'] = lang('overtime_index_title');
        $data['requests'] = $this->overtime_model->requests($this->user_id, $showAll);
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('overtime/index', $data);
        $this->load->view('templates/footer');
    }

   
    public function accept($id) {
        $this->auth->checkIfOperationIsAllowed('accept_overtime');
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $extra = $this->overtime_model->getExtras($id);
        if (empty($extra)) {
            redirect('notfound');
        }
        $employee = $this->users_model->getUsers($extra['employee']);
        $is_delegate = $this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager']);
        if (($this->user_id == $employee['manager']) || ($this->is_hr)  || ($is_delegate)) {
            $this->overtime_model->acceptExtra($id);
            $this->sendMail($id);
            $this->session->set_flashdata('msg', lang('overtime_accept_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('overtime');
            }
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to accept extra #' . $id);
            $this->session->set_flashdata('msg', lang('overtime_accept_flash_msg_error'));
            redirect('leaves');
        }
    }


    public function reject($id) {
        $this->auth->checkIfOperationIsAllowed('reject_overtime');
        $this->load->model('users_model');
        $this->load->model('delegations_model');
        $extra = $this->overtime_model->getExtras($id);
        if (empty($extra)) {
            redirect('notfound');
        }
        $employee = $this->users_model->getUsers($extra['employee']);
        $is_delegate = $this->delegations_model->isDelegateOfManager($this->user_id, $employee['manager']);
        if (($this->user_id == $employee['manager']) || ($this->is_hr)  || ($is_delegate)) {
            $this->overtime_model->rejectExtra($id);
            $this->sendMail($id);
            $this->session->set_flashdata('msg', lang('overtime_reject_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('overtime');
            }
        } else {
            log_message('error', 'User #' . $this->user_id . ' illegally tried to reject extra #' . $id);
            $this->session->set_flashdata('msg', lang('overtime_reject_flash_msg_error'));
            redirect('leaves');
        }
    }
    
   
    private function sendMail($id)
    {
        $this->load->model('users_model');
        $this->load->model('organization_model');
        $extra = $this->overtime_model->getExtras($id);
        
        $employee = $this->users_model->getUsers($extra['employee']);
        $supervisor = $this->organization_model->getSupervisor($employee['organization']);

       
        $this->load->library('email');
        $this->load->library('polyglot');
        $usr_lang = $this->polyglot->code2language($employee['language']);
        
        
        $lang_mail = new CI_Lang();
        $lang_mail->load('email', $usr_lang);
        $lang_mail->load('global', $usr_lang);
        
        $date = new DateTime($extra['date']);
        $startdate = $date->format($lang_mail->line('global_date_format'));

        $this->load->library('parser');
        $data = array(
            'Title' => $lang_mail->line('email_overtime_request_validation_title'),
            'Firstname' => $employee['firstname'],
            'Lastname' => $employee['lastname'],
            'Date' => $startdate,
            'Duration' => $extra['duration'],
            'Cause' => $extra['cause']
        );
        
        if ($extra['status'] == 3) {
            $message = $this->parser->parse('emails/' . $employee['language'] . '/overtime_accepted', $data, TRUE);
            $subject = $lang_mail->line('email_overtime_request_accept_subject');
        } else {
            $message = $this->parser->parse('emails/' . $employee['language'] . '/overtime_rejected', $data, TRUE);
            $subject = $lang_mail->line('email_overtime_request_reject_subject');
        }
        sendMailByWrapper($this, $subject, $message, $employee['email'], is_null($supervisor)?NULL:$supervisor->email);
    }
    
  
    public function export($filter = 'requested') {
        $this->load->library('excel');
        $data['filter'] = $filter;
        $this->load->view('overtime/export', $data);
    }
    
}
