<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Organization_model extends CI_Model {

    public function __construct() {

    }

   
    public function getDepartment($user_id) {
        $this->db->select('organization.id as id, organization.name as name');
        $this->db->from('organization');
        $this->db->join('users', 'users.organization = organization.id');
        $this->db->where('users.id', $user_id);
        $query = $this->db->get();
        $arr = $query->result_array();
        return $arr;
    }
    
   
    public function getName($id) {
        $this->db->from('organization');
        $this->db->where("id", $id); 
        $query = $this->db->get();
        $record = $query->result_array();
        if(count($record) > 0) {
            return $record[0]['name'];
        } else {
            return '';
        }
    }
    
   
    public function getAllEntities() {
        $this->db->from('organization');
        $this->db->order_by("parent_id", "desc"); 
        $this->db->order_by("name", "asc");
        return $this->db->get();
    }

   
    public function getAllChildren($id) {
        $query = 'SELECT GetFamilyTree(id) as id' .
                    ' FROM organization' .
                    ' WHERE id =' . $id;
        $query = $this->db->query($query); 
        $arr = $query->result_array();
        return $arr;
    }
    
    
    public function move($id, $parent_id) {
        $data = array(
            'parent_id' => $parent_id
        );
        $this->db->where('id', $id);
        return $this->db->update('organization', $data);
    }
    
   
    public function attachEmployee($id, $entity) {
        $data = array(
            'organization' => $entity
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

   
    public function delete($entity) {
        $list = $this->getAllChildren($entity);
       
        $data = array(
            'organization' => NULL
        );
        $ids = array();
        if (strlen($list[0]['id']) > 0) {
            $ids = explode(",", $list[0]['id']);
        }
        array_push($ids, $entity);
        $this->db->where_in('organization', $ids);
        $res1 = $this->db->update('users', $data);
     
        $this->db->where_in('id', $ids);
        $res2 = $this->db->delete('organization');
        return $res1 && $res2;
    }
    
  
    public function detachEmployee($id) {
        $data = array(
            'organization' => NULL
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
    
    
    public function rename($id, $text) {
        $data = array(
            'name' => $text
        );
        $this->db->where('id', $id);
        return $this->db->update('organization', $data);
    }
   
    public function create($parent_id, $text) {
        $data = array(
            'name' => $text,
            'parent_id' => $parent_id
        );
        return $this->db->insert('organization', $data);
    }
    
   
    public function copy($id, $parent_id) {
        $this->db->from('organization');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $row = $query->row();
        $data = array(
            'name' => $row->name,
            'parent_id' => $parent_id
        );
        return $this->db->insert('organization', $data);
    }
    
    
    public function employees($id) {
        $this->db->select('id, firstname, lastname, email, datehired');
        $this->db->from('users');
        $this->db->where('organization', $id);
        $this->db->order_by('lastname', 'asc'); 
        $this->db->order_by('firstname', 'asc');
        return $this->db->get();
    }
    
    
    public function allEmployees($id, $children = FALSE) {
        $this->db->select('users.id, users.identifier, users.firstname, users.lastname, users.datehired');
        $this->db->select('organization.name as department, positions.name as position, contracts.name as contract');
        $this->db->select('contracts.id as contract_id');
        $this->db->from('organization');
        $this->db->join('users', 'users.organization = organization.id');
        $this->db->join('positions', 'positions.id  = users.position', 'left');
        $this->db->join('contracts', 'contracts.id  = users.contract', 'left');
        if ($children === TRUE) {
            $this->load->model('organization_model');
            $list = $this->organization_model->getAllChildren($id);
            $ids = array();
            if (count($list) > 0) {
                if ($list[0]['id'] != '') {
                    $ids = explode(",", $list[0]['id']);
                    array_push($ids, $id);
                    $this->db->where_in('organization.id', $ids);
                } else {
                    $this->db->where('organization.id', $id);
                }
            }
        } else {
            $this->db->where('organization.id', $id);
        }
        $this->db->order_by('lastname', 'asc'); 
        $this->db->order_by('firstname', 'asc');
        $employees = $this->db->get()->result();
        return $employees;
    }
    
    
    public function setSupervisor($id, $entity) {
        $data = array(
            'supervisor' => $id
        );
        $this->db->where('id', $entity);
        return $this->db->update('organization', $data);
    }
    

    public function getSupervisor($entity) {
        $this->db->select('users.id, CONCAT(users.firstname, \' \', users.lastname) as username, email', FALSE);
        $this->db->from('organization');
        $this->db->join('users', 'users.id = organization.supervisor');
        $this->db->where('organization.id', $entity);
        $result = $this->db->get()->result();
        if (count($result) > 0) {
            return $result[0];
        } else {
            return NULL;
        }
    }
}
