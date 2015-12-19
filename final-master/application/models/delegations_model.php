<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Delegations_model extends CI_Model {

    
    public function __construct() {

    }

   
    public function listDelegationsForManager($manager) {
        $this->db->select('delegations.*, CONCAT(firstname, \' \', lastname) as delegate_name', FALSE);
        $this->db->join('users', 'delegations.delegate_id = users.id');
        $query = $this->db->get_where('delegations', array('manager_id' => $manager));
        return $query->result_array();
    }
    
   
    public function isDelegateOfManager($employee, $manager) {
        $this->db->from('delegations');
        $this->db->where('delegate_id', $employee);
        $this->db->where('manager_id', $manager);
        $results = $this->db->get()->row_array();
        if (count($results) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    public function hasDelegation($employee) {
        $this->db->from('delegations');
        $this->db->where('delegate_id', $employee);
        $results = $this->db->get()->row_array();
        if (count($results) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
   
    public function listManagersGivingDelegation($id) {
        $this->db->select("manager_id");
        $this->db->from('delegations');
        $this->db->where('delegate_id', $id);
        $results = $this->db->get()->result_array();
        $ids = array();
        foreach ($results as $row) {
            array_push($ids, $row['manager_id']);
        }
        return $ids;
    }
    
   
    public function listMailsOfDelegates($id) {
        $this->db->select("GROUP_CONCAT(email SEPARATOR ',') as list", FALSE);
        $this->db->from('delegations');
        $this->db->join('users', 'delegations.delegate_id = users.id');
        $this->db->group_by('manager_id');
        $this->db->where('manager_id', $id);
        $query = $this->db->get();
        $results = $query->row_array();
        if (count($results) > 0) {
            return $results['list'];
        } else {
            return '';
        }
    }
    
    
    public function addDelegate($manager, $delegate) {
        $data = array(
            'manager_id' => $manager,
            'delegate_id' => $delegate
        );
        $this->db->insert('delegations', $data);
        return $this->db->insert_id();
    }
    
    
    public function deleteDelegation($id) {
        $this->db->delete('delegations', array('id' => $id));
    }
}
