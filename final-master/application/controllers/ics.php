<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


use Sabre\VObject;


class Ics extends CI_Controller {
    
  
    public function __construct() {
        parent::__construct();
        $this->load->library('polyglot');
        require_once(APPPATH . 'third_party/VObjects/vendor/autoload.php');
    }
    
  
    public function dayoffs($user, $contract) {
        if ($this->config->item('ics_enabled') == FALSE) {
            $this->output->set_header("HTTP/1.0 403 Forbidden");
        } else {
           
            $this->load->model('users_model');
            $employee = $this->users_model->getUsers($user);
            if (!is_null($employee['timezone'])) {
                $tzdef = $employee['timezone'];
            } else {
                $tzdef = $this->config->item('default_timezone');
                if ($tzdef == FALSE) $tzdef = 'Europe/Paris';
            }
            $this->lang->load('global', $this->polyglot->code2language($employee['language']));
            
            $this->load->model('dayoffs_model');
            $result = $this->dayoffs_model->getDaysOffForContract($contract);
            if (empty($result)) {
                echo "";
            } else {
                $vcalendar = new VObject\Component\VCalendar();
                foreach ($result as $event) {
                    $startdate = new \DateTime($event->date, new \DateTimeZone($tzdef));
                    $enddate = new \DateTime($event->date, new \DateTimeZone($tzdef));
                    switch ($event->type) {
                        case 1: 
                            $startdate->setTime(0, 0);
                            $enddate->setTime(0, 0);
                            $enddate->modify('+1 day');
                            break;
                        case 2:
                            $startdate->setTime(0, 0);
                            $enddate->setTime(12, 0);
                            break;
                        case 3:
                            $startdate->setTime(12, 0);
                            $enddate->setTime(0, 0);
                            $enddate->modify('+1 day');
                            break;
                    }                    
                    $vcalendar->add('VEVENT', Array(
                        'SUMMARY' => $event->title,
                        'CATEGORIES' => lang('day off'),
                        'DTSTART' => $startdate,
                        'DTEND' => $enddate
                    ));    
                }
                echo $vcalendar->serialize();
            }
        }
    }
    
    
    public function individual($id) {
        if ($this->config->item('ics_enabled') == FALSE) {
            $this->output->set_header("HTTP/1.0 403 Forbidden");
        } else {
            $this->load->model('leaves_model');
            $result = $this->leaves_model->getLeavesOfEmployee($id);
            if (empty($result)) {
                echo "";
            } else {
              
                $this->load->model('users_model');
                $employee = $this->users_model->getUsers($id);
                if (!is_null($employee['timezone'])) {
                    $tzdef = $employee['timezone'];
                } else {
                    $tzdef = $this->config->item('default_timezone');
                    if ($tzdef == FALSE) $tzdef = 'Europe/Paris';
                }
                $this->lang->load('global', $this->polyglot->code2language($employee['language']));
                
                $vcalendar = new VObject\Component\VCalendar();
                foreach ($result as $event) {
                    $startdate = new \DateTime($event['startdate'], new \DateTimeZone($tzdef));
                    $enddate = new \DateTime($event['enddate'], new \DateTimeZone($tzdef));
                    if ($event['startdatetype'] == 'Morning') $startdate->setTime(0, 0);
                    if ($event['startdatetype'] == 'Afternoon') $startdate->setTime(12, 0);
                    if ($event['enddatetype'] == 'Morning') $enddate->setTime(12, 0);
                    if ($event['enddatetype'] == 'Afternoon'){
                        $enddate->setTime(0, 0);
                        $enddate->modify('+1 day');
                    } 
                    
                    $vcalendar->add('VEVENT', Array(
                        'SUMMARY' => lang('leave'),
                        'CATEGORIES' => lang('leave'),
                        'DTSTART' => $startdate,
                        'DTEND' => $enddate,
                        'DESCRIPTION' => $event['cause'],
                        'URL' => base_url() . "leaves/" . $event['id'],
                    ));    
                }
                echo $vcalendar->serialize();
            }
        }
    }

  
    public function entity($user, $entity, $children) {
        if ($this->config->item('ics_enabled') == FALSE) {
            $this->output->set_header("HTTP/1.0 403 Forbidden");
        } else {
            $this->load->model('leaves_model');
            $children = filter_var($children, FILTER_VALIDATE_BOOLEAN);
            $result = $this->leaves_model->entity($entity, $children);
            if (empty($result)) {
                echo "";
            } else {
               
                $this->load->model('users_model');
                $employee = $this->users_model->getUsers($user);
                if (!is_null($employee['timezone'])) {
                    $tzdef = $employee['timezone'];
                } else {
                    $tzdef = $this->config->item('default_timezone');
                    if ($tzdef == FALSE) $tzdef = 'Europe/Paris';
                }
                $this->lang->load('global', $this->polyglot->code2language($employee['language']));
                
                $vcalendar = new VObject\Component\VCalendar();
                foreach ($result as $event) {
                    $startdate = new \DateTime($event['startdate'], new \DateTimeZone($tzdef));
                    $enddate = new \DateTime($event['enddate'], new \DateTimeZone($tzdef));
                    if ($event['startdatetype'] == 'Morning') $startdate->setTime(0, 1);
                    if ($event['startdatetype'] == 'Afternoon') $startdate->setTime(12, 0);
                    if ($event['enddatetype'] == 'Morning') $enddate->setTime(12, 0);
                    if ($event['enddatetype'] == 'Afternoon') $enddate->setTime(23, 59);
                    
                    $vcalendar->add('VEVENT', Array(
                        'SUMMARY' => $event['firstname'] . ' ' . $event['lastname'],
                        'CATEGORIES' => lang('leave'),
                        'DTSTART' => $startdate,
                        'DTEND' => $enddate,
                        'DESCRIPTION' => $event['type'] . ($event['cause']!=''?(' / ' . $event['cause']):''),
                        'URL' => base_url() . "leaves/" . $event['id'],
                    ));    
                }
                echo $vcalendar->serialize();
            }
        }
    }
    
    
    public function ical($id) {
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename=leave.ics');
        $this->load->model('leaves_model');
        $leave = $this->leaves_model->getLeaves($id);
        
        $this->load->model('users_model');
        $employee = $this->users_model->getUsers($leave['employee']);
        if (!is_null($employee['timezone'])) {
            $tzdef = $employee['timezone'];
        } else {
            $tzdef = $this->config->item('default_timezone');
            if ($tzdef == FALSE) $tzdef = 'Europe/Paris';
        }
        $this->lang->load('global', $this->polyglot->code2language($employee['language']));
        
        $vcalendar = new VObject\Component\VCalendar();
        $vcalendar->add('VEVENT', Array(
            'SUMMARY' => lang('leave'),
            'CATEGORIES' => lang('leave'),
            'DESCRIPTION' => $leave['cause'],
            'DTSTART' => new \DateTime($leave['startdate'], new \DateTimeZone($tzdef)),
            'DTEND' => new \DateTime($leave['enddate'], new \DateTimeZone($tzdef)),
            'URL' => base_url() . "leaves/" . $id,
        ));
        echo $vcalendar->serialize();
    }
}
