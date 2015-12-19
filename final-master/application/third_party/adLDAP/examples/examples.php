<?php

use adLDAP\adLDAP;


include dirname(__FILE__).'/../lib/adLDAP/adLDAP.php';

try {
    $adldap = new adLDAP($options);
} catch (adLDAPException $e) {
    echo $e;
    exit();
}


echo("<pre>\n");

if (0) {
    $result = $adldap->authenticate('username', 'password');
    var_dump($result);
}


if (0) {
    $result = $adldap->group()->addGroup('Parent Group Name', 'Child Group Name');
    var_dump($result);
}


if (0) {
    $result = $adldap->group()->addUser('Group Name', 'username');
    var_dump($result);
}


if (0) {
    $attributes = array(
        'group_name' => 'Test Group',
        'description' => 'Just Testing',
        'container' => array('Groups','A Container'),
    );
    $result = $adldap->group()->create($attributes);
    var_dump($result);
}


if (0) {
  
    $result = $adldap->group()->info('Group Name');
    var_dump($result);
}


if (0) {
    $attributes = array(
        'username' => 'freds',
        'logon_name' => 'freds@mydomain.local',
        'firstname' => 'Fred',
        'surname' => 'Smith',
        'company' => 'My Company',
        'department' => 'My Department',
        'email' => 'freds@mydomain.local',
        'container' => array('Container Parent','Container Child'),
        'enabled' => 1,
        'password' => 'Password123',
    );

    try {
        $result = $adldap->user()->create($attributes);
        var_dump($result);
    } catch (adLDAPException $e) {
        echo $e;
        exit();
    }
}


if (0) {
    $result = $adldap->user()->groups('username');
    print_r($result);
}


if (0) {
   
    $result = $adldap->user()->info('username');
    print_r($result);
}

if (0) {
    $result = $adldap->user()->inGroup('username', 'Group Name');
    var_dump($result);
}


if (0) {
    $attributes = array(
        'change_password' => 1,
    );
    $result = $adldap->user()->modify('username', $attributes);
    var_dump($result);
}

if (0) {
    try {
        $result = $adldap->user()->password('username', 'Password123');
        var_dump($result);
    } catch (adLDAPException $e) {
        echo $e;
        exit();
    }
}


if (0) {
    try {
        $result = $adldap->user()->getLastLogon('username');
        var_dump(date('Y-m-d H:i:s', $result));
    } catch (adLDAPException $e) {
        echo $e;
        exit();
    }
}


if (0) {
    $result = $adldap->folder()->listing(array('Users'), adLDAP::ADLDAP_FOLDER, false);
    var_dump($result);
}
