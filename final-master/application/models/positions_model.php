<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Positions_model extends CI_Model {

    
    public function __construct() {
        
    }

    
    public function getPositions($id = 0) {
        if ($id === 0) {
            $query = $this->db->get('positions');
            return $query->result_array();
        }
        $query = $this->db->get_where('positions', array('id' => $id));
        return $query->row_array();
    }
    
   
    public function getName($id) {
        $record = $this->getPositions($id);
        return $record['name'];
    }
    
   
    public function setPositions($name, $description) {
        $data = array(
            'name' => $name,
            'description' => $description
        );
        return $this->db->insert('positions', $data);
    }
    
    
    public function deletePosition($id) {
        $delete = $this->db->delete('positions', array('id' => $id));
        $data = array(
            'position' => 0
        );
        $this->db->where('position', $id);
        $update = $this->db->update('users', $data);
        return $delete && $update;
    }
    
    
    public function updatePositions($id, $name, $description) {
        $data = array(
            'name' => $name,
            'description' => $description
        );
        $this->db->where('id', $id);
        return $this->db->update('positions', $data);
    }
}
