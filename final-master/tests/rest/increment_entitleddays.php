<?php

require("JoraniAPI.php");

-------------------------------------------------------------------------------------

$user = 'testclient';                   
$password = 'testpass';             

$employee_ids = array();    
$contract_ids = array();        
$entity_ids = array();         

$includeChildren = TRUE;

$days = (float) 1;


$type = 1;


$description = 'Credit line added by robot - ' . date("D M d, Y G:i");

//Define entitlment period
//CURRENT_PERIOD        The entitlement can be taken only during the current yearly period (recommended)
//'FROM_MONTH              The entitlement can be taken from the current month to the end of yearly period
//CURRENT_MONTH        The entitlement can be taken only during the current month
//CURRENT_YEAR           The entitlement can be taken only during the current year
$period = JoraniAPI::CURRENT_PERIOD;

//---------------------------------------------------------------------------------------------------------------------------------------------------
// End of configuration
//---------------------------------------------------------------------------------------------------------------------------------------------------

//Connect to the REST API
$api = new JoraniAPI($url, $user, $password);

//Get the list of employee ids and add the entitled days
foreach ($employee_ids as $employee_id){
    $employee = $api->getEmployees($employee_id);
    $contract = $api->getContracts($employee->contract);
    $startdate = $api->getStartDate($contract, $period);
    $enddate = $api->getEndDate($contract, $period);
    if ($employee->active == 1) {
        $api->addEntitledDaysEmployee($employee->id, $startdate, $enddate, $days, $type, $description);
        echo 'Added ' . $days . ' day(s) to employee #' . $employee->id . PHP_EOL;
    } else {
        echo 'No credit to inactiveemployee #' . $employee->id . PHP_EOL;
    }
}

//Get the list of contract ids and add the entitled days
foreach ($contract_ids as $contract_id){
    $contract = $api->getContracts($contract_id);
    $startdate = $api->getStartDate($contract, $period);
    $enddate = $api->getEndDate($contract, $period);
    $api->addEntitledDaysContract($contract->id, $startdate, $enddate, $days, $type, $description);
    echo 'Added ' . $days . ' day(s) to contract #' . $contract_id . PHP_EOL;
}

//Get the list of entity ids and add the entitled days
foreach ($entity_ids as $entity_id){
    $list_employees = $api->getListOfEmployeesInEntity($entity_id, $includeChildren);
    //Get the list of employee ids and add the entitled days
    foreach ($list_employees as $employee){
        $employee = $api->getEmployees($employee->id);
        $contract = $api->getContracts($employee->contract);
        $startdate = $api->getStartDate($contract, $period);
        $enddate = $api->getEndDate($contract, $period);
        if ($employee->active == 1) {
            $api->addEntitledDaysEmployee($employee->id, $startdate, $enddate, $days, $type, $description);
            echo 'Added ' . $days . ' day(s) to employee #' . $employee->id . PHP_EOL;
        } else {
            echo 'No credit to inactiveemployee #' . $employee->id . PHP_EOL;
        }
    }
}
