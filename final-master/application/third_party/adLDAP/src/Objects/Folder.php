<?php

namespace Adldap\Objects;

use Adldap\Exceptions\AdldapException;


class Folder extends AbstractObject
{
  
    protected $required = [
        'ou_name',
        'container',
    ];

   
    public function validateRequired()
    {
        parent::validateRequired();

        if (!is_array($this->getAttribute('container'))) {
            $message = 'Container attribute must be an array';

            throw new AdldapException($message);
        }

        return true;
    }
}
