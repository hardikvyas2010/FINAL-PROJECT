<?php

class JoraniAPI {
   
    private $token = NULL;
    
    const CURRENT_PERIOD = 1;          
    const FROM_MONTH = 2;               
    const CURRENT_MONTH = 3;         
    const CURRENT_YEAR = 4;              
    
  
    function __construct($url, $username, $password) {
        $this->base_url = $url;
        $this->token = $this->getToken($username, $password);
    }

    
    private function getToken($username, $password) {
        $url = $this->base_url . 'api/token';
        $data = array('grant_type' => 'client_credentials');
        $cred = sprintf('Authorization: Basic %s', base64_encode("$username:$password"));
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . $cred ."\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_array = json_decode($result);
        $token = $result_array->access_token;
        return $token;
    }
    
  
    public function getEmployees($employee = NULL) {
        if (is_null($employee)) {
            $url = $this->base_url . 'api/users';
        } else {
            $url = $this->base_url . 'api/users/' . $employee;
        }
        $data = array('access_token' => $this->token);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_array = json_decode($result);
        return $result_array;
    }
    
   
    public function getEntitledDaysListForEmployee($employee) {
        $url = $this->base_url . 'api/entitleddaysemployee/' . $employee;
        $data = array('access_token' => $this->token);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_array = json_decode($result);
        return $result_array;
    }
    
   
    public function addEntitledDaysEmployee($employee, $startdate, $enddate, $days, $type, $description) {
        $url = $this->base_url . 'api/addentitleddaysemployee/' . $employee;
        $data = array('access_token' => $this->token,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'days' => $days,
                'type' => $type,
                'description' => $description,);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
       
        $result_int = json_decode($result);
        return $result_int;
    }
    
    public function addEntitledDaysContract($contract, $startdate, $enddate, $days, $type, $description) {
        $url = $this->base_url . 'api/addentitleddayscontract/' . $contract;
        $data = array('access_token' => $this->token,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'days' => $days,
            'type' => $type,
            'description' => $description,);
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
     
        $result_int = json_decode($result);
        return $result_int;
    }

   
    public function getContracts($contract = NULL) {
        if (is_null($contract)) {
            $url = $this->base_url . 'api/contracts';
        } else {
            $url = $this->base_url . 'api/contracts/' . $contract;
        }
        $data = array('access_token' => $this->token);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_array = json_decode($result);
        return $result_array;
    }
    
   
    public function getListOfEmployeesInEntity($entity, $children = TRUE) {
        $url = $this->base_url . 'api/getListOfEmployeesInEntity/' . $entity . '/' . (($children === TRUE)?'true':'false');
        $data = array('access_token' => $this->token);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_array = json_decode($result);
        return $result_array;
    }
    
 
    public function getStartDate($contract, $period = self::CURRENT_PERIOD) {
        switch ($period) {
           case self::CURRENT_PERIOD:
               $startdate = date('Y') . '-' . str_replace ('/', '-', $contract->startentdate);
               break;
           case self::FROM_MONTH:
           case self::CURRENT_MONTH:
               $startdate = date('Y-m-01');
               break;
           default://CURRENT_YEAR
               $startdate = date('Y') . '-01-01';
       }
       return $startdate;
    }

    
    public function getEndDate($contract, $period = self::CURRENT_PERIOD) {
        switch ($period) {
           case self::CURRENT_PERIOD:
           case self::FROM_MONTH:
               $enddate = date('Y') . '-' . str_replace ('/', '-', $contract->endentdate);
               break;
           case self::CURRENT_MONTH:
               $enddate = date('Y-m-t');
               break;
           default://CURRENT_YEAR
               $enddate = date('Y') . '-12-31';
       }
       return $enddate;
    }
    
   
    function hasEntitlementInPeriod($employee, $type, $startdate, $enddate) {
        $startdate = DateTime::createFromFormat('Y-m-d', $startdate);
        $enddate = DateTime::createFromFormat('Y-m-d', $enddate);
        $entitled_days = $this->getEntitledDaysListForEmployee($employee);
        foreach ($entitled_days as $credit){
            if ($credit->type == $type) {
                $creditStartdate = DateTime::createFromFormat('Y-m-d', $credit->startdate );
                $creditEnddate = DateTime::createFromFormat('Y-m-d', $credit->enddate);
                if (($creditStartdate >= $startdate) && ($creditEnddate <= $enddate))
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
}
