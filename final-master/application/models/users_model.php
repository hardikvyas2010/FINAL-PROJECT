<?php

 

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }


class Users_model extends CI_Model {

    
    public function __construct() {

    }

    
    public function getUsers($id = 0) {
        $this->db->select('users.*');
        $this->db->select('roles.name as role_name');
        $this->db->join('roles', 'roles.id = users.role');
        if ($id === 0) {
            $query = $this->db->get('users');
            return $query->result_array();
        }
        $query = $this->db->get_where('users', array('users.id' => $id));
        return $query->row_array();
    }
    
   
    public function getAllEmployees() {
        $this->db->select('id, firstname, lastname, email');
        $query = $this->db->get('users');
        return $query->result_array();
    }
    
   
    public function getName($id) {
        $record = $this->getUsers($id);
        if (count($record) > 0) {
            return $record['firstname'] . ' ' . $record['lastname'];
        }
    }
    
    
    public function getCollaboratorsOfManager($id = 0) {
        $this->db->from('users');
        $this->db->order_by("lastname", "asc");
        $this->db->order_by("firstname", "asc");
        $this->db->where('manager', $id);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    
    public function isCollaboratorOfManager($employee, $manager) {
        $this->db->from('users');
        $this->db->where('id', $employee);
        $this->db->where('manager', $manager);
        $result = $this->db->get()->result_array();
        return (count($result) > 0);
    }
    
    
    public function isLoginAvailable($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
   
    public function deleteUser($id) {
        $this->db->delete('users', array('id' => $id));
        $this->load->model('entitleddays_model');
        $this->load->model('leaves_model');
        $this->load->model('overtime_model');
        $this->entitleddays_model->deleteEntitledDaysCascadeUser($id);
        $this->leaves_model->deleteLeavesCascadeUser($id);
        $this->overtime_model->deleteExtrasCascadeUser($id);
     
        $data = array(
            'manager' => NULL
        );
        $this->db->where('manager', $id);
        $this->db->update('users', $data);
    }

    public function setUsers() {
       
        require_once(APPPATH . 'third_party/phpseclib/vendor/autoload.php');
        $rsa = new phpseclib\Crypt\RSA();
        $private_key = file_get_contents('./assets/keys/private.pem', TRUE);
        $rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
        $rsa->loadKey($private_key, phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1);
        $password = $rsa->decrypt(base64_decode($this->input->post('CipheredValue')));
        
       
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        

        $role = 0;
        foreach($this->input->post("role") as $role_bit){
            $role = $role | $role_bit;
        }        
        
        if ($this->input->post('datehired') == "") {
            $datehired = NULL;
        } else {
            $datehired = $this->input->post('datehired');
        }
        
        $data = array(
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'login' => $this->input->post('login'),
            'email' => $this->input->post('email'),
            'password' => $hash,
            'role' => $role,
            'manager' => $this->input->post('manager'),
            'contract' => $this->input->post('contract'),
            'organization' => $this->input->post('entity'),
            'position' => $this->input->post('position'),
            'datehired' => $datehired,
            'identifier' => $this->input->post('identifier'),
            'language' => $this->input->post('language'),
            'timezone' => $this->input->post('timezone')
        );
        if ($this->config->item('ldap_basedn_db')) $data['ldap_path'] = $this->input->post('ldap_path');
        $this->db->insert('users', $data);
   
        if ($this->input->post('manager') == -1) {
            $id = $this->db->insert_id();
            $data = array(
                'manager' => $id
            );
            $this->db->where('id', $id);
            $this->db->update('users', $data);
        }
        return $password;
    }
    
    
    public function insertUserByApi($firstname, $lastname, $login, $email, $password, $role,
            $manager = NULL,
            $organization = NULL,
            $contract = NULL,
            $position = NULL,
            $datehired = NULL,
            $identifier = NULL,
            $language = NULL,
            $timezone = NULL,
            $ldap_path = NULL,
            $active = NULL,
            $country = NULL,
            $calendar = NULL) {

 
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        $this->db->set('firstname', $firstname);
        $this->db->set('lastname', $lastname);
        $this->db->set('login', $login);
        $this->db->set('email', $email);
        $this->db->set('password', $hash);
        $this->db->set('role', $role);
        if (isset($manager)) $this->db->set('manager', $manager);
        if (isset($organization)) $this->db->set('organization', $organization);
        if (isset($contract)) $this->db->set('contract', $contract);
        if (isset($position)) $this->db->set('position', $position);
        if (isset($datehired)) $this->db->set('datehired', $datehired);
        if (isset($identifier)) $this->db->set('identifier', $identifier);
        if (isset($language)) $this->db->set('language', $language);
        if (isset($timezone)) $this->db->set('timezone', $timezone);
        if (isset($ldap_path)) $this->db->set('ldap_path', $ldap_path);
        if (isset($active)) $this->db->set('active', $active);
        if (isset($country)) $this->db->set('country', $country);
        if (isset($calendar)) $this->db->set('calendar', $calendar);
        $this->db->insert('users');
        return $this->db->insert_id();
    }

    public function updateUserByApi($id, $data) {
        if (isset($password)){
        
            $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
            $hash = crypt($password, $salt);
            $this->db->set('password', $hash);
        }
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
    
   
    public function updateUsers() {
        
       
        $role = 0;
        foreach($this->input->post("role") as $role_bit){
            $role = $role | $role_bit;
        }
   
        if ($this->input->post('manager') == -1) {
            $manager = $this->input->post('id');
        } else {
            $manager = $this->input->post('manager');
        }
        
        if ($this->input->post('datehired') == "") {
            $datehired = NULL;
        } else {
            $datehired = $this->input->post('datehired');
        }
        
        $data = array(
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'login' => $this->input->post('login'),
            'email' => $this->input->post('email'),
            'role' => $role,
            'manager' => $manager,
            'contract' => $this->input->post('contract'),
            'organization' => $this->input->post('entity'),
            'position' => $this->input->post('position'),
            'datehired' => $datehired,
            'identifier' => $this->input->post('identifier'),
            'language' => $this->input->post('language'),
            'timezone' => $this->input->post('timezone')
        );
        if ($this->config->item('ldap_basedn_db')) $data['ldap_path'] = $this->input->post('ldap_path');

        $this->db->where('id', $this->input->post('id'));
        $result = $this->db->update('users', $data);
        return $result;
    }

    
    public function resetPassword($id, $CipheredNewPassword) {

        require_once(APPPATH . 'third_party/phpseclib/vendor/autoload.php');
        $rsa = new phpseclib\Crypt\RSA();
        $private_key = file_get_contents('./assets/keys/private.pem', TRUE);
        $rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
        $rsa->loadKey($private_key, phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1);
        $password = $rsa->decrypt(base64_decode($CipheredNewPassword));
        
     
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
        $data = array(
            'password' => $hash
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    public function resetClearPassword($id) {
     
        $password = $this->randomPassword(10);
   
        $salt = '$2a$08$' . substr(strtr(base64_encode($this->getRandomBytes(16)), '+', '.'), 0, 22) . '$';
        $hash = crypt($password, $salt);
 
        $data = array(
            'password' => $hash
        );
        $this->db->where('id', $id);
        $this->db->update('users', $data);
        return $password;
    }
    
  
    public function randomPassword($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
    
   
    private function loadProfile($row) {
        if (((int) $row->role & 1)) {
            $is_admin = TRUE;
        } else {
            $is_admin = FALSE;
        }

       
        if (((int) $row->role & 25)) {
            $is_hr = TRUE;
        } else {
            $is_hr = FALSE;
        }

     
        $isManager = FALSE;
        if (count($this->getCollaboratorsOfManager($row->id)) > 0) {
            $isManager = TRUE;
        } else {
            $this->load->model('delegations_model');
            if ($this->delegations_model->hasDelegation($row->id))
                $isManager = TRUE;
        }

        $newdata = array(
            'login' => $row->login,
            'id' => $row->id,
            'firstname' => $row->firstname,
            'lastname' => $row->lastname,
            'is_manager' => $isManager,
            'is_admin' => $is_admin,
            'is_hr' => $is_hr,
            'manager' => $row->manager,
            'logged_in' => TRUE
        );
        $this->session->set_userdata($newdata);
    }

    
    public function checkCredentials($login, $password) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $this->db->where('active = TRUE');
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
       
            return FALSE;
        } else {
            $row = $query->row();
            $hash = crypt($password, $row->password);
            if ($hash == $row->password) {
                // Password does match stored password.
                $this->loadProfile($row);
                return TRUE;
            } else {
                // Password does not match stored password.
                return FALSE;
            }
        }
    }
    
 
    public function checkCredentialsLDAP($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $this->db->where('active = TRUE');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $this->loadProfile($row);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    

    public function checkCredentialsEmail($email) {
        $this->db->from('users');
        $this->db->where('email', $email);
        $this->db->where('active = TRUE');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $this->loadProfile($row);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function getBaseDN($login) {
        $this->db->select('ldap_path');
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->ldap_path;
        } else {
            return "";
        }
    }
    
    
    public function employeesOfEntity($id = 0, $children = TRUE) {
        $this->db->select('users.id as id,'
                . ' users.firstname as firstname,'
                . ' users.lastname as lastname,'
                . ' users.email as email,'
                . ' organization.name as entity,'
                . ' contracts.name as contract,'
                . ' CONCAT_WS(\' \',managers.firstname,  managers.lastname) as manager_name', FALSE);
        $this->db->from('users');
        $this->db->join('contracts', 'contracts.id = users.contract', 'left outer');
        $this->db->join('users as managers', 'managers.id = users.manager', 'left outer');
        $this->db->join('organization', 'organization.id = users.organization', 'left outer');

        if ($children == TRUE) {
            $this->load->model('organization_model');
            $list = $this->organization_model->getAllChildren($id);
            $ids = array();
            if (count($list) > 0) {
                if ($list[0]['id'] != '') {
                    $ids = explode(",", $list[0]['id']);
                }
            }
            array_push($ids, $id);
            $this->db->where_in('organization.id', $ids);
        } else {
            $this->db->where('users.organization', $id);
        }

        return $this->db->get()->result();
    }
    
   
    public function updateUsersCascadeContract($id) {
        $this->db->set('contract', NULL);
        $this->db->where('contract', $id);
        $result = $this->db->update('users');
        return $result;
    }
    
   
    public function setActive($id, $active) {
        $this->db->set('active', $active);
        $this->db->where('id', $id);
        return $this->db->update('users');
    }
    
   
    public function getUserByLogin($login) {
        $this->db->from('users');
        $this->db->where('login', $login);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            //No match found
            return null;
        } else {
            return $query->row();
        }
    }
    
   
    protected function getRandomBytes($length) {
        if(function_exists('openssl_random_pseudo_bytes')) {
          $rnd = openssl_random_pseudo_bytes($length, $strong);
          if ($strong === TRUE)
            return $rnd;
        }
        $sha =''; $rnd ='';
        if (file_exists('/dev/urandom')) {
          $fp = fopen('/dev/urandom', 'rb');
          if ($fp) {
              if (function_exists('stream_set_read_buffer')) {
                  stream_set_read_buffer($fp, 0);
              }
              $sha = fread($fp, $length);
              fclose($fp);
          }
        }
        for ($i=0; $i<$length; $i++) {
          $sha  = hash('sha256',$sha.mt_rand());
          $char = mt_rand(0,62);
          $rnd .= chr(hexdec($sha[$char].$sha[$char+1]));
        }
        return $rnd;
    }
}
