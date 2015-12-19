<?php


if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Contracts_model extends CI_Model {

    public function __construct() {
        
    }

    public function getContracts($id = 0) {
        if ($id === 0) {
            $query = $this->db->get('contracts');
            return $query->result_array();
        }
        $query = $this->db->get_where('contracts', array('id' => $id));
        return $query->row_array();
    }
    
    
    public function getName($id) {
        $record = $this->getContracts($id);
        if (count($record) > 0) {
            return $record['name'];
        } else {
            return '';
        }
    }
    
    
    public function setContracts() {
        $startentdate = str_pad($this->input->post('startentdatemonth'), 2, "0", STR_PAD_LEFT) .
                "/" . str_pad($this->input->post('startentdateday'), 2, "0", STR_PAD_LEFT);
        $endentdate = str_pad($this->input->post('endentdatemonth'), 2, "0", STR_PAD_LEFT) .
                "/" . str_pad($this->input->post('endentdateday'), 2, "0", STR_PAD_LEFT);
        $data = array(
            'name' => $this->input->post('name'),
            'startentdate' => $startentdate,
            'endentdate' => $endentdate
        );
        return $this->db->insert('contracts', $data);
    }
    
 
    public function deleteContract($id) {
        $this->db->delete('contracts', array('id' => $id));
        $this->load->model('users_model');
        $this->load->model('entitleddays_model');
        $this->load->model('dayoffs_model');
        $this->entitleddays_model->deleteEntitledDaysCascadeContract($id);
        $this->dayoffs_model->deleteDaysOffCascadeContract($id);
        $this->users_model->updateUsersCascadeContract($id);
    }
    
   
    public function updateContract() {
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
        return $this->db->update('contracts', $data);
    }
    
  
    public function getBoundaries($userId, &$startentdate, &$endentdate, $refDate = NULL) {
        $this->db->select('startentdate, endentdate');
        $this->db->from('contracts');
        $this->db->join('users', 'users.contract = contracts.id');
        $this->db->where('users.id', $userId);
        $boundaries = $this->db->get()->result_array();
        
        if ($refDate == NULL) {
            $refDate = date("Y-m-d");
        }
        $refYear = substr($refDate, 0, 4);
        $refMonth = substr($refDate, 5, 2);
        $nextYear = strval(intval($refYear) + 1);
        $lastYear = strval(intval($refYear) - 1);
        
        if (count($boundaries) != 0) {
            $startmonth = intval(substr($boundaries[0]['startentdate'], 0, 2));
            if ($startmonth == 1 ) {
                $startentdate = $refYear . "-" . str_replace("/", "-", $boundaries[0]['startentdate']);
                $endentdate =  $refYear . "-" . str_replace("/", "-", $boundaries[0]['endentdate']);
            } else {
                if (intval($refMonth) < 6) {
                    $startentdate = $lastYear . "-" . str_replace("/", "-", $boundaries[0]['startentdate']);
                    $endentdate = $refYear . "-" . str_replace("/", "-", $boundaries[0]['endentdate']);
                } else {
                    $startentdate = $refYear . "-" . str_replace("/", "-", $boundaries[0]['startentdate']);
                    $endentdate = $nextYear . "-" . str_replace("/", "-", $boundaries[0]['endentdate']);
                }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
