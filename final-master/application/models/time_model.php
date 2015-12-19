<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Time_model extends CI_Model {

    
    public function __construct() {
       
    }

    
    public function getActivities($id = 0) {
        if ($id === 0) {
            $query = $this->db->get('activities');
            return $query->result_array();
        }
        $query = $this->db->get_where('activities', array('id' => $id));
        return $query->row_array();
    }
    
   
    public function getName($id) {
        $record = $this->getActivities($id);
        if (count($record) > 0) {
            return $record['name'];
        } else {
            return '';
        }
    }
    
    
    public function setActivities() {
        $startentdate = str_pad($this->input->post('startentdatemonth'), 2, "0", STR_PAD_LEFT) .
                "/" . str_pad($this->input->post('startentdateday'), 2, "0", STR_PAD_LEFT);
        $endentdate = str_pad($this->input->post('endentdatemonth'), 2, "0", STR_PAD_LEFT) .
                "/" . str_pad($this->input->post('endentdateday'), 2, "0", STR_PAD_LEFT);
        $data = array(
            'name' => $this->input->post('name'),
            'startentdate' => $startentdate,
            'endentdate' => $endentdate
        );
        return $this->db->insert('activities', $data);
    }
    
   
    public function deleteActivity($id) {
        $this->db->delete('activities', array('id' => $id));
       
    }
    
    
    public function updateActivity() {
        
        $startentdate = str_pad($this->input->post('startentdatemonth'), 2, "0", STR_PAD_LEFT) .
                "/" . str_pad($this->input->post('startentdateday'), 2, "0", STR_PAD_LEFT);
        $endentdate = str_pad($this->input->post('endentdatemonth'), 2, "0", STR_PAD_LEFT) .
                "/" . str_pad($this->input->post('endentdateday'), 2, "0", STR_PAD_LEFT);
        $data = array(
            'name' => $this->input->post('name'),
            'startentdate' => $startentdate,
            'endentdate' => $endentdate
        );

        $this->db->where('id', $this->input->post('id'));
        return $this->db->update('activities', $data);
    }

    
    public function purgeActivities($toDate) {
        $this->db->where('DATE(datetime) <= ', $toDate);
        return $this->db->delete('activities');
    }

    
    public function count() {
        $this->db->select('count(*) as number', FALSE);
        $this->db->from('activities');
        $result = $this->db->get();
        return $result->row()->number;
    }
}
