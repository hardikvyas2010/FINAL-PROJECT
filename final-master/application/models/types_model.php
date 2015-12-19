<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Types_model extends CI_Model {

    public function __construct() {
        
    }

    
    public function getTypes($id = 0) {
        if ($id === 0) {
            $query = $this->db->get('types');
            return $query->result_array();
        }
        $query = $this->db->get_where('types', array('id' => $id));
        return $query->row_array();
    }
    
    
    public function getName($id) {
        $type = $this->getTypes($id);
        return $type['name'];
    }
    
   
    public function setTypes($name) {
        $data = array(
            'name' => $this->input->post('name')
        );
        return $this->db->insert('types', $data);
    }
    
   
    public function deleteType($id) {
        $this->db->delete('types', array('id' => $id));
    }
    
   
    public function updateTypes($id, $name) {
        $data = array(
            'name' => $name
        );
        $this->db->where('id', $id);
        return $this->db->update('types', $data);
    }
    
   
    public function usage($id) {
        $this->db->select('COUNT(*)');
        $this->db->from('leaves');
        $this->db->where('type', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result['COUNT(*)'];
    }
    
    
    public function allTypes(&$compensate_name) {
        $summary = array();
        $types = $this->db->get_where('types')->result_array();
        foreach ($types as $type) {
            $summary[$type['name']][0] = 0; 
            if ($type['id'] != 0) {
                $summary[$type['name']][1] = 0; 
                $summary[$type['name']][2] = ''; 
            } else {
                $compensate_name = $type['name'];
                $summary[$type['name']][1] = '-'; 
                $summary[$type['name']][2] = ''; 
            }
        }
        return $summary;
    }
}
