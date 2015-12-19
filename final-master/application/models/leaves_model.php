<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Leaves_model extends CI_Model {

    
    public function __construct() {
        
    }

   
    public function getLeaves($id = 0) {
        $this->db->select('leaves.*');
        $this->db->select('status.name as status_name, types.name as type_name');
        $this->db->from('leaves');
        $this->db->join('status', 'leaves.status = status.id');
        $this->db->join('types', 'leaves.type = types.id');
        if ($id === 0) {
            return $this->db->get()->result_array();
        }
        $this->db->where('leaves.id', $id);
        return $this->db->get()->row_array();
    }

   
    public function getLeavesOfEmployee($employee) {
        $this->db->select('leaves.*');
        $this->db->select('status.name as status_name, types.name as type_name');
        $this->db->from('leaves');
        $this->db->join('status', 'leaves.status = status.id');
        $this->db->join('types', 'leaves.type = types.id');
        $this->db->where('leaves.employee', $employee);
        $this->db->order_by('leaves.id', 'desc');
        return $this->db->get()->result_array();
    }
    
   
    public function getAcceptedLeavesBetweenDates($employee, $start, $end) {
        $this->db->select('leaves.*, types.name as type');
        $this->db->from('leaves');
        $this->db->join('status', 'leaves.status = status.id');
        $this->db->join('types', 'leaves.type = types.id');
        $this->db->where('employee', $employee);
        $this->db->where("(startdate <= STR_TO_DATE('" . $end . "', '%Y-%m-%d') AND enddate >= STR_TO_DATE('" . $start . "', '%Y-%m-%d'))");
        $this->db->where('leaves.status', 3);   //Accepted
        $this->db->order_by('startdate', 'asc');
        return $this->db->get()->result_array();
    }
    
 
    public function length($employee, $start, $end, $startdatetype, $enddatetype) {
        $this->db->select('sum(CASE `type` WHEN 1 THEN 1 WHEN 2 THEN 0.5 WHEN 3 THEN 0.5 END) as days');
        $this->db->from('users');
        $this->db->join('dayoffs', 'users.contract = dayoffs.contract');
        $this->db->where('users.id', $employee);
        $this->db->where('date >=', $start);
        $this->db->where('date <=', $end);
        $result = $this->db->get()->result_array();
        $startTimeStamp = strtotime($start." UTC");
        $endTimeStamp = strtotime($end." UTC");
        $timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
        if (count($result) != 0) { //Test if some non working days are defined on a contract
            return $numberDays - $result[0]['days'];
        } else {
           
            if ($startdatetype == $enddatetype) {
                return 0.5;
            } else {
                return $numberDays;
            }
        }
    }
    
  
    public function getSumEntitledDays($employee, $contract, $refDate) {
        $this->db->select('types.id as type_id, types.name as type_name');
        $this->db->select('SUM(entitleddays.days) as entitled');
        $this->db->select('MIN(startdate) as min_date');
        $this->db->select('MAX(enddate) as max_date');
        $this->db->from('entitleddays');
        $this->db->join('types', 'types.id = entitleddays.type');
        $this->db->group_by('types.id');
        $this->db->where('entitleddays.startdate <= ', $refDate);
        $this->db->where('entitleddays.enddate >= ', $refDate);
        $where = ' (entitleddays.contract=' . $contract . 
                       ' OR entitleddays.employee=' . $employee . ')';
        $this->db->where($where, NULL, FALSE);   
        $results = $this->db->get()->result_array();
        
        $entitled_days = array();
        foreach ($results as $result) {
            $entitled_days[$result['type_id']] = $result;
        }
        return $entitled_days;
    }
    
    
    public function getLeaveBalanceForEmployee($id, $sum_extra = FALSE, $refDate = NULL) {
       
        if ($refDate == NULL) {
            $refDate = date("Y-m-d");
        }

   
        $this->load->model('contracts_model');
        $hasContract = $this->contracts_model->getBoundaries($id, $startentdate, $endentdate, $refDate);
        if ($hasContract) {
            $this->load->model('types_model');
            $this->load->model('users_model');
       
            $summary = $this->types_model->allTypes($compensate_name);
          
            $user = $this->users_model->getUsers($id);
            $entitlements = $this->getSumEntitledDays($id, $user['contract'], $refDate);
            
            foreach ($entitlements as $entitlement) {
            
                $this->db->select('SUM(leaves.duration) as taken, types.name as type');
                $this->db->from('leaves');
                $this->db->join('types', 'types.id = leaves.type');
                $this->db->where('leaves.employee', $id);
                $this->db->where('leaves.status', 3);
                $this->db->where('leaves.startdate >= ', $entitlement['min_date']);
                $this->db->where('leaves.enddate <=', $entitlement['max_date']);
                $this->db->where('leaves.type', $entitlement['type_id']);
                $this->db->group_by("leaves.type");
                $taken_days = $this->db->get()->result_array();
               
                foreach ($taken_days as $taken) {
                    $summary[$taken['type']][0] = (float) $taken['taken']; //Taken
                }
                //Report the number of available days
                $summary[$entitlement['type_name']][1] = (float) $entitlement['entitled'];
            }
            
        
            $this->db->select('duration, date, cause');
            $this->db->from('overtime');
            $this->db->where('employee', $id);
            $this->db->where("date >= DATE_SUB(STR_TO_DATE('" . $refDate . "', '%Y-%m-%d'),INTERVAL 1 YEAR)");
            $this->db->where('status = 3'); //Accepted
            $overtime_days = $this->db->get()->result_array();
            $sum = 0;
            foreach ($overtime_days as $entitled) {
                if ($sum_extra == FALSE) {
                    $summary['Catch up for ' . $entitled['date']][0] = '-'; //taken
                    $summary['Catch up for ' . $entitled['date']][1] = (float) $entitled['duration']; //entitled
                    $summary['Catch up for ' . $entitled['date']][2] = $entitled['cause']; //description
                }
                $sum += (float) $entitled['duration']; //entitled
            }
            $this->db->select('sum(leaves.duration) as taken');
            $this->db->from('leaves');
            $this->db->where('leaves.employee', $id);
            $this->db->where('leaves.status', 3);
            $this->db->where('leaves.type', 0);
            $this->db->where("leaves.startdate >= DATE_SUB(STR_TO_DATE('" . $refDate . "', '%Y-%m-%d'),INTERVAL 1 YEAR)");
            $this->db->group_by("leaves.type");
            $taken_days = $this->db->get()->result_array();
            if (count($taken_days) > 0) {
                $summary[$compensate_name][0] = (float) $taken_days[0]['taken']; //taken
            } else {
                $summary[$compensate_name][0] = 0; //taken
            }
            //Add the sum of validated catch up for the employee
            if (array_key_exists($compensate_name, $summary)) {
                $summary[$compensate_name][1] = (float) $summary[$compensate_name][1] + $sum; //entitled
            }
            
            //Remove all lines having taken and entitled set to set to 0
            foreach ($summary as $key => $value) {
                if ($value[0]==0 && $value[1]==0) {
                    unset($summary[$key]);
                }
            }
            return $summary;
        } else { //User attached to no contract
            return NULL;
        }        
    }
    
   
    public function getLeavesTypeBalanceForEmployee($id, $type, $startdate = NULL) {
        $summary = $this->getLeaveBalanceForEmployee($id, TRUE, $startdate);
        //return entitled days - taken (for a given leave type)
        if (is_null($summary)) {
            return NULL;
        } else {
            if (array_key_exists($type, $summary)) {
                return ($summary[$type][1] - $summary[$type][0]);
            } else {
                return 0;
            }
        }
    }

  
    public function detectOverlappingLeaves($id, $startdate, $enddate, $startdatetype, $enddatetype, $leave_id=NULL) {
        $overlapping = FALSE;
        $this->db->where('employee', $id);
        $this->db->where('status != 4');
        $this->db->where('(startdate <= DATE(\'' . $enddate . '\') AND enddate >= DATE(\'' . $startdate . '\'))');
        if (!is_null($leave_id)) {
            $this->db->where('id != ', $leave_id);
        }
        $leaves = $this->db->get('leaves')->result();
        
        if ($startdatetype == "Morning") {
            $startTmp = strtotime($startdate." 08:00:00 UTC");
        } else {
            $startTmp = strtotime($startdate." 12:01:00 UTC");
        }
        if ($enddatetype == "Morning") {
            $endTmp = strtotime($enddate." 12:00:00 UTC");
        } else {
            $endTmp = strtotime($enddate." 18:00:00 UTC");
        }
        
        foreach ($leaves as $leave) {
            if ($leave->startdatetype == "Morning") {
                $startTmpDB = strtotime($leave->startdate." 08:00:00 UTC");
            } else {
                $startTmpDB = strtotime($leave->startdate." 12:01:00 UTC");
            }
            if ($leave->enddatetype == "Morning") {
                $endTmpDB = strtotime($leave->enddate." 12:00:00 UTC");
            } else {
                $endTmpDB = strtotime($leave->enddate." 18:00:00 UTC");
            }
            if (($startTmpDB <= $endTmp) && ($endTmpDB >= $startTmp)) {
                $overlapping = TRUE;
            }
        }
        return $overlapping;
    }
    
   
    public function setLeaves($id) {
        $data = array(
            'startdate' => $this->input->post('startdate'),
            'startdatetype' => $this->input->post('startdatetype'),
            'enddate' => $this->input->post('enddate'),
            'enddatetype' => $this->input->post('enddatetype'),
            'duration' => abs($this->input->post('duration')),
            'type' => $this->input->post('type'),
            'cause' => $this->input->post('cause'),
            'status' => $this->input->post('status'),
            'employee' => $id
        );
        $this->db->insert('leaves', $data);
        return $this->db->insert_id();
    }

  
    public function createLeaveByApi($startdate, $enddate, $status, $employee, $cause,
            $startdatetype, $enddatetype, $duration, $type) {
        
        $data = array(
            'startdate' => $startdate,
            'enddate' => $enddate,
            'status' => $status,
            'employee' => $employee,
            'cause' => $cause,
            'startdatetype' => $startdatetype,
            'enddatetype' => $enddatetype,
            'duration' => abs($duration),
            'type' => $type
        );
        $this->db->insert('leaves', $data);
        return $this->db->insert_id();
    }
    
   
    public function updateLeaves($id) {
        $data = array(
            'startdate' => $this->input->post('startdate'),
            'startdatetype' => $this->input->post('startdatetype'),
            'enddate' => $this->input->post('enddate'),
            'enddatetype' => $this->input->post('enddatetype'),
            'duration' => abs($this->input->post('duration')),
            'type' => $this->input->post('type'),
            'cause' => $this->input->post('cause'),
            'status' => $this->input->post('status')
        );
        $this->db->where('id', $id);
        $this->db->update('leaves', $data);
    }
    
    
    public function acceptLeave($id) {
        $data = array(
            'status' => 3
        );
        $this->db->where('id', $id);
        return $this->db->update('leaves', $data);
    }

  
    public function rejectLeave($id) {
        $data = array(
            'status' => 4
        );
        $this->db->where('id', $id);
        return $this->db->update('leaves', $data);
    }
    
   
    public function deleteLeave($id) {
        return $this->db->delete('leaves', array('id' => $id));
    }
    
  
    public function deleteLeavesCascadeUser($id) {
        return $this->db->delete('leaves', array('employee' => $id));
    }
    
   
    public function individual($user_id, $start = "", $end = "") {
        $this->db->select('leaves.*, types.name as type');
        $this->db->join('types', 'leaves.type = types.id');
        $this->db->where('employee', $user_id);
        $this->db->where('( (leaves.startdate <= DATE(' . $this->db->escape($start) . ') AND leaves.enddate >= DATE(' . $this->db->escape($start) . '))' .
                                  ' OR (leaves.startdate >= DATE(' . $this->db->escape($start) . ') AND leaves.enddate <= DATE(' . $this->db->escape($end) . ')) )');
        $this->db->order_by('startdate', 'desc');
        $this->db->limit(1024);  //Security limit
        $events = $this->db->get('leaves')->result();
        
        $jsonevents = array();
        foreach ($events as $entry) {
            
            if ($entry->startdatetype == "Morning") {
                $startdate = $entry->startdate . 'T07:00:00';
            } else {
                $startdate = $entry->startdate . 'T12:00:00';
            }

            if ($entry->enddatetype == "Morning") {
                $enddate = $entry->enddate . 'T12:00:00';
            } else {
                $enddate = $entry->enddate . 'T18:00:00';
            }
            
            switch ($entry->status)
            {
                case 1: $color = '#999'; break;     // Planned
                case 2: $color = '#f89406'; break;  // Requested
                case 3: $color = '#468847'; break;  // Accepted
                case 4: $color = '#ff0000'; break;  // Rejected
            }
            
            $jsonevents[] = array(
                'id' => $entry->id,
                'title' => $entry->type,
                'start' => $startdate,
                'color' => $color,
                'allDay' => FALSE,
                'end' => $enddate,
                'startdatetype' => $entry->startdatetype,
                'enddatetype' => $entry->enddatetype
            );
        }
        return json_encode($jsonevents);
    }

   
    public function workmates($user_id, $start = "", $end = "") {
        $this->db->join('users', 'users.id = leaves.employee');
        $this->db->where('users.manager', $user_id);
        $this->db->where('leaves.status != ', 4);       //Exclude rejected requests
        $this->db->where('( (leaves.startdate <= DATE(' . $this->db->escape($start) . ') AND leaves.enddate >= DATE(' . $this->db->escape($start) . '))' .
                                   ' OR (leaves.startdate >= DATE(' . $this->db->escape($start) . ') AND leaves.enddate <= DATE(' . $this->db->escape($end) . ')))');
        $this->db->order_by('startdate', 'desc');
        $this->db->limit(1024);  //Security limit
        $events = $this->db->get('leaves')->result();
        
        $jsonevents = array();
        foreach ($events as $entry) {
            if ($entry->startdatetype == "Morning") {
                $startdate = $entry->startdate . 'T07:00:00';
            } else {
                $startdate = $entry->startdate . 'T12:00:00';
            }

            if ($entry->enddatetype == "Morning") {
                $enddate = $entry->enddate . 'T12:00:00';
            } else {
                $enddate = $entry->enddate . 'T18:00:00';
            }
            
            switch ($entry->status)
            {
                case 1: $color = '#999'; break;     // Planned
                case 2: $color = '#f89406'; break;  // Requested
                case 3: $color = '#468847'; break;  // Accepted
                case 4: $color = '#ff0000'; break;  // Rejected
            }
            
            $jsonevents[] = array(
                'id' => $entry->id,
                'title' => $entry->firstname .' ' . $entry->lastname,
                'start' => $startdate,
                'color' => $color,
                'allDay' => FALSE,
                'end' => $enddate,
                'startdatetype' => $entry->startdatetype,
                'enddatetype' => $entry->enddatetype
            );
        }
        return json_encode($jsonevents);
    }

    
    public function collaborators($user_id, $start = "", $end = "") {
        $this->db->join('users', 'users.id = leaves.employee');
        $this->db->where('users.manager', $user_id);
        $this->db->where('( (leaves.startdate <= DATE(' . $this->db->escape($start) . ') AND leaves.enddate >= DATE(' . $this->db->escape($start) . '))' .
                                ' OR (leaves.startdate >= DATE(' . $this->db->escape($start) . ') AND leaves.enddate <= DATE(' . $this->db->escape($end) . ')) )');
        $this->db->order_by('startdate', 'desc');
        $this->db->limit(1024);  //Security limit
        $events = $this->db->get('leaves')->result();
        
        $jsonevents = array();
        foreach ($events as $entry) {
            if ($entry->startdatetype == "Morning") {
                $startdate = $entry->startdate . 'T07:00:00';
            } else {
                $startdate = $entry->startdate . 'T12:00:00';
            }

            if ($entry->enddatetype == "Morning") {
                $enddate = $entry->enddate . 'T12:00:00';
            } else {
                $enddate = $entry->enddate . 'T18:00:00';
            }
            
            switch ($entry->status)
            {
                case 1: $color = '#999'; break;     // Planned
                case 2: $color = '#f89406'; break;  // Requested
                case 3: $color = '#468847'; break;  // Accepted
                case 4: $color = '#ff0000'; break;  // Rejected
            }
            
            $jsonevents[] = array(
                'id' => $entry->id,
                'title' => $entry->firstname .' ' . $entry->lastname,
                'start' => $startdate,
                'color' => $color,
                'allDay' => FALSE,
                'end' => $enddate,
                'startdatetype' => $entry->startdatetype,
                'enddatetype' => $entry->enddatetype
            );
        }
        return json_encode($jsonevents);
    }
    
   
    public function department($entity_id, $start = "", $end = "", $children = FALSE) {
        $this->db->select('users.firstname, users.lastname,  leaves.*, types.name as type');
        $this->db->from('organization');
        $this->db->join('users', 'users.organization = organization.id');
        $this->db->join('leaves', 'leaves.employee  = users.id');
        $this->db->join('types', 'leaves.type = types.id');
        $this->db->where('( (leaves.startdate <= DATE(' . $this->db->escape($start) . ') AND leaves.enddate >= DATE(' . $this->db->escape($start) . '))' .
                                    ' OR (leaves.startdate >= DATE(' . $this->db->escape($start) . ') AND leaves.enddate <= DATE(' . $this->db->escape($end) . ')) )');
        if ($children === TRUE) {
            $this->load->model('organization_model');
            $list = $this->organization_model->getAllChildren($entity_id);
            $ids = array();
            if ($list[0]['id'] != '') {
                $ids = explode(",", $list[0]['id']);
                array_push($ids, $entity_id);
                $this->db->where_in('organization.id', $ids);
            } else {
                $this->db->where('organization.id', $entity_id);
            }
        } else {
            $this->db->where('organization.id', $entity_id);
        }
        $this->db->where('leaves.status != ', 4);       //Exclude rejected requests
        $this->db->order_by('startdate', 'desc');
        $this->db->limit(1024);  //Security limit
        $events = $this->db->get()->result();
        $jsonevents = array();
        foreach ($events as $entry) {
            
            if ($entry->startdatetype == "Morning") {
                $startdate = $entry->startdate . 'T07:00:00';
            } else {
                $startdate = $entry->startdate . 'T12:00:00';
            }

            if ($entry->enddatetype == "Morning") {
                $enddate = $entry->enddate . 'T12:00:00';
            } else {
                $enddate = $entry->enddate . 'T18:00:00';
            }
            
            switch ($entry->status)
            {
                case 1: $color = '#999'; break;     // Planned
                case 2: $color = '#f89406'; break;  // Requested
                case 3: $color = '#468847'; break;  // Accepted
                case 4: $color = '#ff0000'; break;  // Rejected
            }
            
            $jsonevents[] = array(
                'id' => $entry->id,
                'title' => $entry->firstname .' ' . $entry->lastname,
                'start' => $startdate,
                'color' => $color,
                'allDay' => FALSE,
                'end' => $enddate,
                'startdatetype' => $entry->startdatetype,
                'enddatetype' => $entry->enddatetype
            );
        }
        return json_encode($jsonevents);
    }
    
  
    public function entity($entity_id, $children = FALSE) {
        $this->db->select('users.firstname, users.lastname,  leaves.*, types.name as type');
        $this->db->from('organization');
        $this->db->join('users', 'users.organization = organization.id');
        $this->db->join('leaves', 'leaves.employee  = users.id');
        $this->db->join('types', 'leaves.type = types.id');
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
        $this->db->where('leaves.status != ', 4);       //Exclude rejected requests
        $this->db->order_by('startdate', 'desc');
        $events = $this->db->get()->result_array();
        return $events;
    }
    
    
    public function getLeavesRequestedToManager($manager, $all = FALSE) {
        $this->load->model('delegations_model');
        $ids = $this->delegations_model->listManagersGivingDelegation($manager);
        $this->db->select('leaves.id as id, users.*, leaves.*, types.name as type_label');
        $this->db->select('status.name as status_name, types.name as type_name');
        $this->db->join('status', 'leaves.status = status.id');
        $this->db->join('types', 'leaves.type = types.id');
        $this->db->join('users', 'users.id = leaves.employee');

        if (count($ids) > 0) {
            array_push($ids, $manager);
            $this->db->where_in('users.manager', $ids);
        } else {
            $this->db->where('users.manager', $manager);
        }
        if ($all == FALSE) {
            $this->db->where('leaves.status', 2);
        }
        $this->db->order_by('leaves.startdate', 'desc');
        $query = $this->db->get('leaves');
        return $query->result_array();
    }
    
  
    public function purgeLeaves($toDate) {
        $this->db->where(' <= ', $toDate);
        return $this->db->delete('leaves');
    }

   
    public function count() {
        $this->db->select('count(*) as number', FALSE);
        $this->db->from('leaves');
        $result = $this->db->get();
        return $result->row()->number;
    }
    
   
    public function all($start, $end) {
        $this->db->select("users.id as user_id, users.firstname, users.lastname, leaves.*", FALSE);
        $this->db->join('users', 'users.id = leaves.employee');
        $this->db->where('( (leaves.startdate <= FROM_UNIXTIME(' . $this->db->escape($start) . ') AND leaves.enddate >= FROM_UNIXTIME(' . $this->db->escape($start) . '))' .
                                   ' OR (leaves.startdate >= FROM_UNIXTIME(' . $this->db->escape($start) . ') AND leaves.enddate <= FROM_UNIXTIME(' . $this->db->escape($end) . ')))');
        $this->db->order_by('startdate', 'desc');
        return $this->db->get('leaves')->result();
    }
    
  
    public function tabular(&$entity=-1, &$month=0, &$year=0, &$children=TRUE) {
        //Mangage paramaters
        if ($month==0) $month = date("m");
        if ($year==0) $year = date("Y");
        $children = filter_var($children, FILTER_VALIDATE_BOOLEAN);
        //If no entity was selected, select the entity of the connected user or the root of the organization
        if ($entity == -1) {
            $this->load->model('users_model');
            $user = $this->users_model->getUsers($this->session->userdata('id'));
            if (is_null($user['organization'])) {
                $entity = 0;
            } else {
                $entity = $user['organization'];
            }
        }
        $tabular = array();
        
        //We must show all users of the departement
        $this->load->model('dayoffs_model');
        $this->load->model('organization_model');
        $employees = $this->organization_model->allEmployees($entity, $children);
        foreach ($employees as $employee) {
            $tabular[$employee->id] = $this->linear($employee->id, $month, $year, TRUE, TRUE, TRUE, FALSE);
        }
        return $tabular;
    }
    
   
    public function monthlyLeavesDuration($linear) {
        $total = 0;
        foreach ($linear->days as $day) {
          if (strstr($day->display, ';')) {
              $display = explode(";", $day->display);
              if ($display[0] == '2') $total += 0.5;
              if ($display[0] == '3') $total += 0.5;
              if ($display[1] == '2') $total += 0.5;
              if ($display[1] == '3') $total += 0.5;
          } else {
              if ($day->display == 2) $total += 0.5;
              if ($day->display == 3) $total += 0.5;
              if ($day->display == 1) $total += 1;
          }
        }
        return $total;
    }
    
  
    public function monthlyLeavesByType($linear) {
        $by_types = array();
        foreach ($linear->days as $day) {
          if (strstr($day->display, ';')) {
              $display = explode(";", $day->display);
              $type = explode(";", $day->type);
              if ($display[0] == '2') array_key_exists($type[0], $by_types) ? $by_types[$type[0]] += 0.5: $by_types[$type[0]] = 0.5;
              if ($display[0] == '3') array_key_exists($type[0], $by_types) ? $by_types[$type[0]] += 0.5: $by_types[$type[0]] = 0.5;
              if ($display[1] == '2') array_key_exists($type[1], $by_types) ? $by_types[$type[1]] += 0.5: $by_types[$type[1]] = 0.5;
              if ($display[1] == '3') array_key_exists($type[1], $by_types) ? $by_types[$type[1]] += 0.5: $by_types[$type[1]] = 0.5;
          } else {
              if ($day->display == 2) array_key_exists($day->type, $by_types) ? $by_types[$day->type] += 0.5: $by_types[$day->type] = 0.5;
              if ($day->display == 3) array_key_exists($day->type, $by_types) ? $by_types[$day->type] += 0.5: $by_types[$day->type] = 0.5;
              if ($day->display == 1) array_key_exists($day->type, $by_types) ? $by_types[$day->type] += 1: $by_types[$day->type] = 1;
          }
        }
        return $by_types;
    }
    
   
    public function linear($employee_id, $month, $year, 
            $planned = FALSE, $requested = FALSE, $accepted = FALSE, $rejected = FALSE) {
        $start = $year . '-' . $month . '-' .  '1';    //first date of selected month
        $lastDay = date("t", strtotime($start));    //last day of selected month
        $end = $year . '-' . $month . '-' . $lastDay;    //last date of selected month
        
        //We must show all users of the departement
        $this->load->model('dayoffs_model');
        $this->load->model('users_model');
        $employee = $this->users_model->getUsers($employee_id);
        $user = new stdClass;
        $user->name = $employee['firstname'] . ' ' . $employee['lastname'];
        $user->days = array();

        //Init all day to working day
        for ($ii = 1; $ii <= $lastDay; $ii++) {
            $day = new stdClass;
            $day->type = '';
            $day->status = '';
            $day->display = 0; //working day
            $user->days[$ii] = $day;
        }

        //Force all day offs (mind the case of employees having no leave)
        $dayoffs = $this->dayoffs_model->lengthDaysOffBetweenDatesForEmployee($employee_id, $start, $end);
        foreach ($dayoffs as $dayoff) {
            $iDate = new DateTime($dayoff->date);
            $dayNum = intval($iDate->format('d'));
            $user->days[$dayNum]->display = (string) $dayoff->type + 3;
            $user->days[$dayNum]->status = (string) $dayoff->type + 3;
            $user->days[$dayNum]->type = $dayoff->title;
        }
        
        //Build the complex query for all leaves
        $this->db->select('leaves.*, types.name as type');
        $this->db->from('leaves');
        $this->db->join('types', 'leaves.type = types.id');
        $this->db->where('(leaves.startdate <= DATE(' . $this->db->escape($end) . ') AND leaves.enddate >= DATE(' . $this->db->escape($start) . '))');
        if (!$planned) $this->db->where('leaves.status != ', 1);
        if (!$requested) $this->db->where('leaves.status != ', 2);
        if (!$accepted) $this->db->where('leaves.status != ', 3);
        if (!$rejected) $this->db->where('leaves.status != ', 4);        
        
        $this->db->where('leaves.employee = ', $employee_id);
        $this->db->order_by('startdate', 'asc');
        $events = $this->db->get()->result();
        $limitDate = DateTime::createFromFormat('Y-m-d', $end);
        $floorDate = DateTime::createFromFormat('Y-m-d', $start);
        
        $this->load->model('dayoffs_model');
        foreach ($events as $entry) {
            
            $startDate = DateTime::createFromFormat('Y-m-d', $entry->startdate);
            if ($startDate < $floorDate) $startDate = $floorDate;
            $iDate = clone $startDate;
            $endDate = DateTime::createFromFormat('Y-m-d', $entry->enddate);
            if ($endDate > $limitDate) $endDate = $limitDate;
            
            //Iteration between 2 dates
            while ($iDate <= $endDate)
            {
                if ($iDate > $limitDate) break;     //The calendar displays the leaves on one month
                if ($iDate < $startDate) continue;  //The leave starts before the first day of the calendar
                $dayNum = intval($iDate->format('d'));

                //Simplify logic
                if ($startDate == $endDate) $one_day = TRUE; else $one_day = FALSE;
                if ($entry->startdatetype == 'Morning') $start_morning = TRUE; else $start_morning = FALSE;
                if ($entry->startdatetype == 'Afternoon') $start_afternoon = TRUE; else $start_afternoon = FALSE;
                if ($entry->enddatetype == 'Morning') $end_morning = TRUE; else $end_morning = FALSE;
                if ($entry->enddatetype == 'Afternoon') $end_afternoon = TRUE; else $end_afternoon = FALSE;
                if ($iDate == $startDate) $first_day = TRUE; else $first_day = FALSE;
                if ($iDate == $endDate) $last_day = TRUE; else $last_day = FALSE;
                
                
                
                //Length of leave request is one day long
                if ($one_day && $start_morning && $end_afternoon) $display = '1';
                if ($one_day && $start_morning && $end_morning) $display = '2';
                if ($one_day && $start_afternoon && $end_afternoon) $display = '3';
                //Length of leave request is one day long is more than one day
                //We are in the middle of a long leave request
                if (!$one_day && !$first_day && !$last_day) $display = '1';
                //First day of a long leave request
                if (!$one_day && $first_day && $start_morning) $display = '1';
                if (!$one_day && $first_day && $start_afternoon) $display = '3';
                //Last day of a long leave request
                if (!$one_day && $last_day && $end_afternoon) $display = '1';
                if (!$one_day && $last_day && $end_morning) $display = '2';
                
                //Check if another leave was defined on this day
                if ($user->days[$dayNum]->display != '4') { //Except full day off
                    if ($user->days[$dayNum]->type != '') { //Overlapping with a day off or another request
                        if (($user->days[$dayNum]->display == 2) ||
                                ($user->days[$dayNum]->display == 6)) { //Respect Morning/Afternoon order
                            $user->days[$dayNum]->type .= ';' . $entry->type;
                            $user->days[$dayNum]->display .= ';' . $display;
                            $user->days[$dayNum]->status .= ';' . $entry->status;
                        } else {
                            $user->days[$dayNum]->type = $entry->type . ';' . $user->days[$dayNum]->type;
                            $user->days[$dayNum]->display = $display . ';' . $user->days[$dayNum]->display;
                            $user->days[$dayNum]->status = $entry->status . ';' . $user->days[$dayNum]->status;
                        }
                    } else  {   //All day entry
                        $user->days[$dayNum]->type = $entry->type;
                        $user->days[$dayNum]->display = $display;
                        $user->days[$dayNum]->status = $entry->status;
                    }
                }
                $iDate->modify('+1 day');   //Next day
            }   
        }
        return $user;
    }
    
}
