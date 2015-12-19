<?php


 if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


use Sabre\VObject;


class Dayoffs_model extends CI_Model {

    
    public function __construct() {
        
    }
    
    
    public function getDaysOffForCivilYear($contract, $year) {
        $this->db->select('DAY(date) as d, MONTH(date) as m, YEAR(date) as y, type, title');
        $this->db->where('contract', $contract);
        $this->db->where('YEAR(date)', $year);
        $query = $this->db->get('dayoffs');
        $dayoffs =array();
        foreach($query->result() as $row)
        {
           
            $timestamp = mktime(0, 0, 0, $row->m, $row->d, $row->y);
            $dayoffs[$timestamp][0] = $row->type;
            $dayoffs[$timestamp][1] = $row->title;
        }
        return $dayoffs;
    }
    
    
    public function getDaysOffForContract($contract) {
        $this->db->where('contract', $contract);
        $query = $this->db->get('dayoffs');
        $this->db->where("date >= DATE_SUB(NOW(),INTERVAL 2 YEAR"); //Security/performance limit
        return $query->result();
    }
    
    
   
    public function deleteDayOff($contract, $timestamp) {
        $this->db->where('contract', $contract);
        $this->db->where('date', date('Y/m/d', $timestamp));
        return $this->db->delete('dayoffs');
    } 
    
   
    public function deleteDaysOffCascadeContract($contract) {
        $this->db->where('contract', $contract);
        return $this->db->delete('dayoffs');
    }
    
    
    public function deleteListOfDaysOff($contract, $dateList) {
        $dates = explode(",", $dateList);
        $this->db->where('contract', $contract);
        $this->db->where_in('DATE_FORMAT(date, \'%Y-%m-%d\')', $dates);
        return $this->db->delete('dayoffs');
    }

    
    public function addListOfDaysOff($contract, $type, $title, $dateList) {
       
        $dates = explode(",", $dateList);
        $data = array();
        foreach ($dates as $date) {
            $row = array(
                'contract' => $contract,
                'date' => date('Y-m-d', strtotime($date)),
                'type' => $type,
                'title' => $title
            );
            array_push($data, $row);
        }
        return $this->db->insert_batch('dayoffs', $data); 
    }
    
   
    public function copyListOfDaysOff($source, $destination, $year) {
       
        $this->db->where('contract', $destination);
        $this->db->where('YEAR(date)', $year);
        $this->db->delete('dayoffs');
        
        
        $sql = 'INSERT dayoffs(contract, date, type, title) ' .
                ' SELECT ' . $this->db->escape($destination) . ', date, type, title ' .
                ' FROM dayoffs ' .
                ' WHERE contract = ' . $this->db->escape($source) .
                ' AND YEAR(date) = ' . $this->db->escape($year);
        $query = $this->db->query($sql);
        return $query;
    }
    
   
    public function lengthDaysOffBetweenDates($contract, $start, $end) {
        $this->db->select('sum(CASE `type` WHEN 1 THEN 1 WHEN 2 THEN 0.5 WHEN 3 THEN 0.5 END) as days');
        $this->db->where('contract', $contract);
        $this->db->where('date >=', $start);
        $this->db->where('date <=', $end);
        $this->db->from('dayoffs');
        $result = $this->db->get()->result_array();
        return is_null($result[0]['days'])?0:$result[0]['days']; 
    }
    
    
    public function addDayOff($contract, $timestamp, $type, $title) {
        $this->db->select('id');
        $this->db->where('contract', $contract);
        $this->db->where('date', date('Y/m/d', $timestamp));
        $query = $this->db->get('dayoffs');
        if ($query->num_rows() > 0) {
            $data = array(
                'date' => date('Y/m/d', $timestamp),
                'type' => $type,
                'title' => $title
            );
            $this->db->where('id', $query->row('id'));
            return $this->db->update('dayoffs', $data);
        } else {
            $data = array(
                'contract' => $contract,
                'date' => date('Y/m/d', $timestamp),
                'type' => $type,
                'title' => $title
            );
            return $this->db->insert('dayoffs', $data);
        }
    }
    
    
    public function importDaysOffFromICS($contract, $url) {
        require_once(APPPATH . 'third_party/VObjects/vendor/autoload.php');
        $ical = VObject\Reader::read(fopen($url,'r'), VObject\Reader::OPTION_FORGIVING);
        foreach($ical->VEVENT as $event) {
            $start = new DateTime($event->DTSTART);
            $end = new DateTime($event->DTEND);
            $interval = $start->diff($end);
           
            $length = $interval->d;
            $day = $start;
            for ($ii = 0; $ii < $length; $ii++) {
                $tmp = $day->format('U');
                $this->deletedayoff($contract, $tmp);
                $this->adddayoff($contract, $tmp, 1, strval($event->SUMMARY));
                $day->add(new DateInterval('P1D'));
            }
        }
    }
    
   
    public function userDayoffs($user_id, $start = "", $end = "") {
        $this->lang->load('calendar', $this->session->userdata('language'));
        $this->db->select('dayoffs.*');
        $this->db->join('dayoffs', 'users.contract = dayoffs.contract');
        $this->db->where('users.id', $user_id);
        $this->db->where('date >=', $start);
        $this->db->where('date <=', $end);
        $events = $this->db->get('users')->result();
        
        $jsonevents = array();
        foreach ($events as $entry) {
            switch ($entry->type)
            {
                case 1:
                    $title = $entry->title;
                    $startdate = $entry->date . 'T07:00:00';
                    $enddate = $entry->date . 'T18:00:00';
                    $allDay = TRUE;
                    $startdatetype = 'Morning';
                    $enddatetype = 'Afternoon';
                    break;
                case 2:
                    $title = lang('Morning') . ': ' . $entry->title;
                    $startdate = $entry->date . 'T07:00:00';
                    $enddate = $entry->date . 'T12:00:00';
                    $allDay = FALSE;
                    $startdatetype = 'Morning';
                    $enddatetype = 'Morning';
                    break;
                case 3:
                    $title = lang('Afternoon') . ': ' . $entry->title;
                    $startdate = $entry->date . 'T12:00:00';
                    $enddate = $entry->date . 'T18:00:00';
                    $allDay = FALSE;
                    $startdatetype = 'Afternoon';
                    $enddatetype = 'Afternoon';
                    break;
            }
            $jsonevents[] = array(
                'id' => $entry->id,
                'title' => $title,
                'start' => $startdate,
                'color' => '#000000',
                'allDay' => $allDay,
                'end' => $enddate,
                'startdatetype' => $startdatetype,
                'enddatetype' => $enddatetype
            );
        }
        return json_encode($jsonevents);
    }
    
   
    public function allDayoffs($start, $end, $entity_id, $children) {
        $this->lang->load('calendar', $this->session->userdata('language'));
        
        $this->db->select('dayoffs.*, contracts.name');
        $this->db->distinct();
        $this->db->join('contracts', 'dayoffs.contract = contracts.id');
        $this->db->join('users', 'users.contract = contracts.id');
        $this->db->join('organization', 'users.organization = organization.id');
        $this->db->where('date >=', $start);
        $this->db->where('date <=', $end);
        
        if ($children === TRUE) {
            $this->load->model('organization_model');
            $list = $this->organization_model->getAllChildren($entity_id);
            $ids = array();
            if (count($list) > 0) {
                $ids = explode(",", $list[0]['id']);
            }
            array_push($ids, $entity_id);
            $this->db->where_in('organization.id', $ids);
        } else {
            $this->db->where('organization.id', $entity_id);
        }        
        
        $events = $this->db->get('dayoffs')->result();
        
        $jsonevents = array();
        foreach ($events as $entry) {
            switch ($entry->type)
            {
                case 1:
                    $title = $entry->title;
                    $startdate = $entry->date . 'T07:00:00';
                    $enddate = $entry->date . 'T18:00:00';
                    $allDay = TRUE;
                    $startdatetype = 'Morning';
                    $enddatetype = 'Afternoon';
                    break;
                case 2:
                    $title = lang('Morning') . ': ' . $entry->title;
                    $startdate = $entry->date . 'T07:00:00';
                    $enddate = $entry->date . 'T12:00:00';
                    $allDay = FALSE;
                    $startdatetype = 'Morning';
                    $enddatetype = 'Morning';
                    break;
                case 3:
                    $title = lang('Afternoon') . ': ' . $entry->title;
                    $startdate = $entry->date . 'T12:00:00';
                    $enddate = $entry->date . 'T18:00:00';
                    $allDay = FALSE;
                    $startdatetype = 'Afternoon';
                    $enddatetype = 'Afternoon';
                    break;
            }
            $jsonevents[] = array(
                'id' => $entry->id,
                'title' => $entry->name . ': ' . $title,
                'start' => $startdate,
                'color' => '#000000',
                'allDay' => $allDay,
                'end' => $enddate,
                'startdatetype' => $startdatetype,
                'enddatetype' => $enddatetype
            );
        }
        return json_encode($jsonevents);
    }
    
    
    public function purgeDaysoff($toDate) {
        $this->db->where('date <= ', $toDate);
        return $this->db->delete('entitleddays');
    }

   
    public function count() {
        $this->db->select('count(*) as number', FALSE);
        $this->db->from('dayoffs');
        $result = $this->db->get();
        return $result->row()->number;
    }
    
   
    public function lengthDaysOffBetweenDatesForEmployee($id, $start, $end) {
        $this->db->select('dayoffs.*');
        $this->db->join('dayoffs', 'users.contract = dayoffs.contract');
        $this->db->where('users.id', $id);
        $this->db->where('date >= DATE(' . $this->db->escape($start) . ')');
        $this->db->where('date <= DATE(' . $this->db->escape($end) . ')');
        $dayoffs = $this->db->get('users')->result();
        return $dayoffs;
    }
}
