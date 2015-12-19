<?php



include dirname(__FILE__).'/../lib/adLDAP/adLDAP.php';
try {
    $adldap = new adLDAP($options);
} catch (adLDAPException $e) {
    echo $e;
    exit();
}

echo("<pre>\n");

$collection = $adldap->user()->infoCollection('username');

print_r($collection->memberOf);
print_r($collection->displayName);
