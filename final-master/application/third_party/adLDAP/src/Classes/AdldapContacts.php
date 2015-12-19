<?php

namespace Adldap\Classes;

use Adldap\Objects\Contact;


class AdldapContacts extends AbstractAdldapQueryable
{
  
    public $objectClass = 'contact';

 
    public function create(array $attributes)
    {
        $contact = new Contact($attributes);

        $contact->validateRequired();

      
        $add = $this->adldap->ldapSchema($attributes);

     
        $add['cn'][0] = $contact->{'display_name'};

        $add['objectclass'][0] = 'top';
        $add['objectclass'][1] = 'person';
        $add['objectclass'][2] = 'organizationalPerson';
        $add['objectclass'][3] = 'contact';

        if (!$contact->hasAttribute('exchange_hidefromlists')) {
            $add['msExchHideFromAddressLists'][0] = 'TRUE';
        }

       
        $attributes['container'] = array_reverse($attributes['container']);

        $container = 'OU='.implode(',OU=', $attributes['container']);

        $dn = 'CN='.$this->adldap->utilities()->escapeCharacters($add['cn'][0]).', '.$container.','.$this->adldap->getBaseDn();

       
        return $this->connection->add($dn, $add);
    }

  
    public function modify($contactName, $attributes)
    {
        $contactDn = $this->dn($contactName);

        if ($contactDn) {
        
            $mod = $this->adldap->ldapSchema($attributes);

        
            if (!$mod) {
                return false;
            }

        
            return $this->connection->modify($contactDn, $mod);
        }

        return false;
    }

 
 
 
    public function contactMailEnable($contactName, $emailAddress, $mailNickname = null)
    {
        $contactDn = $this->dn($contactName);

        if ($contactDn) {
            return $this->adldap->exchange()->contactMailEnable($contactDn, $emailAddress, $mailNickname);
        }

        return false;
    }
}
