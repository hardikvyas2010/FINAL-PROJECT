<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Roles_model extends CI_Model {

    
    public function __construct() {

       
    }

    
    public function getRoles($id = 0) {
        if ($id === 0) {
            $query = $this->db->get('roles');
            return $query->result_array();
        }
        $query = $this->db->get_where('roles', array('id' => $id));
        return $query->row_array();
    }
}
