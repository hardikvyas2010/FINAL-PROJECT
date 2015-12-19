<?php

namespace Adldap\Objects;

use Adldap\Exceptions\AdldapException;


class Mailbox extends AbstractObject
{
    
    protected $required = [
        'username',
        'storageGroup',
        'emailAddress',
    ];

 
    public function validateRequired($only = [])
    {
        parent::validateRequired();

        if (!is_array($this->getAttribute('storageGroup'))) {
            $message = 'Storage Group attribute must be an array';

            throw new AdldapException($message);
        }

        return true;
    }

    
    public function toLdapArray()
    {
        return [
            'exchange_homemdb' => $this->container.','.$this->baseDn,
            'exchange_proxyaddress' => 'SMTP:'.$this->emailAddress,
            'exchange_mailnickname' => $this->mailNickname,
            'exchange_usedefaults' => $this->mdbUseDefaults,
        ];
    }
}
