<?php

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }
 

class Help {

   
    private $CI;

 
    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('language');
        $this->CI->load->library('session');
        $this->CI->lang->load('global', $this->CI->session->userdata('language'));
    }

    public function create_help_link($page) {
        if (lang($page) != "") {
            return '&nbsp;' .
                      '<a href="' . lang($page) . '"' .
                      ' title="' . lang('global_link_tooltip_documentation') . '"' .
                      ' target="_blank" rel="nofollow"><i class="icon-question-sign"></i></a>';
        } else {
            return '';
        }        
    }

  
    public function get_default_help_page($page) {
        if (lang('global_link_doc_page_calendar_organization') == "") {
            $defaut['global_link_doc_page_calendar_organization'] = 'http://jorani.org/page-calendar-organization.html';
            $defaut['global_link_doc_page_my_summary'] = 'http://jorani.org/page-my-summary.html';
            $defaut['global_link_doc_page_request_leave'] = 'http://jorani.org/how-to-request-a-leave.html';
            $defaut['global_link_doc_page_edit_leave_type'] = 'http://jorani.org/edit-leave-types.html';
            $defaut['global_link_doc_page_hr_organization'] = 'http://jorani.org/page-describe-organization.html';
            $defaut['global_link_doc_page_reset_password'] = 'http://jorani.org/how-to-change-my-password.html';
            $defaut['global_link_doc_page_leave_validation'] = 'http://jorani.org/page-leave-requests-validation.html';
            $defaut['global_link_doc_page_login'] = 'http://jorani.org/page-login-to-the-application.html';
            $defaut['global_link_doc_page_create_user'] = 'http://jorani.org/page-create-a-new-user.html';
            $defaut['global_link_doc_page_list_users'] = 'http://jorani.org/page-list-of-users.html';
            $defaut['global_link_doc_page_list_employees'] = 'http://jorani.org/page-list-of-employees.html';
            if (array_key_exists($page, $defaut)) {
                return "";
            } else {
                return "http://jorani.org/";
            }
        }
    }

}
