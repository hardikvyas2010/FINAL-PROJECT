<?php

namespace Adldap\Classes;

use Adldap\Adldap;


abstract class AbstractAdldapBase
{
   
    protected $adldap;

  
    protected $connection;


    public function __construct(Adldap $adldap)
    {
        $this->adldap = $adldap;

        $connection = $adldap->getLdapConnection();

        if ($connection) {
            $this->connection = $connection;
        }
    }

  
    public function getAdldap()
    {
        return $this->adldap;
    }
}
