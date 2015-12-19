<?php

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Overtime_model extends CI_Model {

    
    public function __construct() {
        
    }

    public function getExtras($id = 0) {
        $this->db->select('overtime.*');
        $this->db->select('status.name as status_name');
        $this->db->from('overtime');
        $this->db->join('status', 'overtime.status = status.id');
        if ($id === 0) {
            return $this->db->get()->result_array();
        }
        $this->db->where('overtime.id', $id);
        return $this->db->get()->row_array();
    }

  
    public function getExtrasOfEmployee($employee) {
        $this->db->select('overtime.*');
        $this->db->select('status.name as status_name');
        $this->db->from('overtime');
        $this->db->join('status', 'overtime.status = status.id');
        $this->db->where('overtime.employee', $employee);
        $this->db->order_by('overtime.id', 'desc');
        return $this->db->get()->result_array();
    }
    
    
    public function setExtra() {
        $data = array(
            'date' => $this->input->post('date'),
            'employee' => $this->session->userdata('id'),
            'duration' => $this->input->post('duration'),
            'cause' => $this->input->post('cause'),
            'status' => $this->input->post('status')
        );
        $this->db->insert('overtime', $data);
        return $this->db->insert_id();
    }

    
    public function updateExtra($id) {
        $data = array(
            'date' => $this->input->post('date'),
            'duration' => $this->input->post('duration'),
            'cause' => $this->input->post('cause'),
            'status' => $this->input->post('status')
        );
        $this->db->where('id', $id);
        $this->db->update('overtime', $data);
    }
    
    
    public function acceptExtra($id) {
        $data = array(
            'status' => 3
        );
        $this->db->where('id', $id);
        return $this->db->update('overtime', $data);
    }

    
    public function rejectExtra($id) {
        $data = array(
            'status' => 4
        );
        $this->db->where('id', $id);
        return $this->db->update('overtime', $data);
    }
    
  
    public function deleteExtra($id) {
        return $this->db->delete('overtime', array('id' => $id));
    }
    
    
    public function deleteExtrasCascadeUser($id) {
        $this->db->delete('overtime', array('employee' => $id));
    }
        
   
    public function requests($user_id, $all = FALSE) {
        $this->load->model('delegations_model');
        $ids = $this->delegations_model->listManagersGivingDelegation($user_id);
        $this->db->select('overtime.id as id, users.*, overtime.*');
        $this->db->select('status.name as status_name');
        $this->db->join('status', 'overtime.status = status.id');
        $this->db->join('users', 'users.id = overtime.employee');
        if (count($ids) > 0) {
            array_push($ids, $user_id);
            $this->db->where_in('users.manager', $ids);
        } else {
            $this->db->where('users.manager', $user_id);
        }
        if ($all == FALSE) {
            $this->db->where('status', 2);
        }
        $this->db->order_by('date', 'desc');
        $query = $this->db->get('overtime');
        return $query->result_array();
    }
    
    
    public function purgeOvertime($toDate) {
        $this->db->where(' <= ', $toDate);
        return $this->db->delete('overtime');
    }

    
    public function count() {
        $this->db->select('count(*) as number', FALSE);
        $this->db->from('overtime');
        $result = $this->db->get();
        return $result->row()->number;
    }
}
