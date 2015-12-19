<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Entitleddays_model extends CI_Model {

    
    public function __construct() {
        
    }

   
    public function getEntitledDaysForContract($contract) {
        $this->db->select('entitleddays.*, types.name as type_name');
        $this->db->from('entitleddays');
        $this->db->join('types', 'types.id = entitleddays.type');
        $this->db->order_by("startdate", "desc");
        $this->db->where('contract =', $contract);
        return $this->db->get()->result_array();
    }
    
    
    public function getEntitledDaysForEmployee($employee) {
        $this->db->select('entitleddays.*, types.name as type_name');
        $this->db->from('entitleddays');
        $this->db->join('types', 'types.id = entitleddays.type');
        $this->db->order_by("startdate", "desc");
        $this->db->where('employee =', $employee);
        return $this->db->get()->result_array();
    }
    
  
    public function addEntitledDaysToContract($contract_id, $startdate, $enddate, $days, $type, $description) {
        $data = array(
            'contract' => $contract_id,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'days' => $days,
            'type' => $type,
            'description' => $description
        );
        $this->db->insert('entitleddays', $data);
        return $this->db->insert_id();
    }

    
    public function addEntitledDaysToEmployee($user_id, $startdate, $enddate, $days, $type, $description) {
        $data = array(
            'employee' => $user_id,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'days' => $days,
            'type' => $type,
            'description' => $description
        );
        $this->db->insert('entitleddays', $data);
        return $this->db->insert_id();
    }
    
    
    public function deleteEntitledDays($id) {
        return $this->db->delete('entitleddays', array('id' => $id));
    }

   
    public function deleteEntitledDaysCascadeUser($id) {
        $this->db->delete('entitleddays', array('employee' => $id));
    }
    
   
    public function deleteEntitledDaysCascadeContract($id) {
        $this->db->delete('entitleddays', array('contract' => $id));
    }
    
   
    public function updateEntitledDays($id, $startdate, $enddate, $days, $type, $description) {
        $data = array(
            'startdate' => $startdate,
            'enddate' => $enddate,
            'days' => $days,
            'type' => $type,
            'description' => $description
        );

        $this->db->where('id', $id);
        return $this->db->update('entitleddays', $data);
    }
    
   
    public function increase($id, $step) {
        if (!is_numeric($step)) $step = 1;
        $this->db->set('days', 'days + ' . $step, FALSE);
        $this->db->where('id', $id);
        return $this->db->update('entitleddays');
    }
    
    
    public function decrease($id, $step) {
        if (!is_numeric($step)) $step = 1;
        $this->db->set('days', 'days - ' . $step, FALSE);
        $this->db->where('id', $id);
        return $this->db->update('entitleddays');
    }
    
   
    public function updateNbOfDaysOfEntitledDaysRecord($id, $days) {
        if (!is_numeric($days)) $days = 1;
        $this->db->set('days', $days);
        $this->db->where('id', $id);
        return $this->db->update('entitleddays');
    }
    
   
    public function purgeEntitleddays($toDate) {
        $this->db->where('enddate <= ', $toDate);
        return $this->db->delete('entitleddays');
    }

   
    public function count() {
        $this->db->select('count(*) as number', FALSE);
        $this->db->from('entitleddays');
        $result = $this->db->get();
        return $result->row()->number;
    }
}
