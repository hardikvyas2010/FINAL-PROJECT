<?php

namespace Adldap\Objects;

use Adldap\Exceptions\AdldapException;


class Contact extends AbstractObject
{

    protected $required = [
        'display_name',
        'email',
        'container',
    ];


    public function validateRequired($only = [])
    {
        parent::validateRequired($only);

        if (!is_array($this->getAttribute('container'))) {
            throw new AdldapException('Container attribute must be an array.');
        }

        return true;
    }
}
