<?php

namespace Adldap\Classes;

use Adldap\Exceptions\AdldapException;
use Adldap\Objects\Mailbox;


class AdldapExchange extends AbstractAdldapQueryable
{
   
    public $serverObjectCategory = 'msExchExchangeServer';

    
    public $storageGroupObjectCategory = 'msExchStorageGroup';

   
    public function all($fields = [], $sorted = true, $sortBy = 'cn', $sortByDirection = 'asc')
    {
        $namingContext = $this->getConfigurationNamingContext();

        if ($namingContext) {
            $search = $this->adldap->search()
                ->setDn($namingContext)
                ->select($fields)
                ->where('objectCategory', '=', $this->serverObjectCategory);

            if ($sorted) {
                $search->sortBy($sortBy, $sortByDirection);
            }

            return $search->get();
        }

        return false;
    }

  
    public function find($name, $fields = [])
    {
        $namingContext = $this->getConfigurationNamingContext();

        if ($namingContext) {
            return $this->adldap->search()
                ->setDn($namingContext)
                ->select($fields)
                ->where('objectCategory', '=', $this->serverObjectCategory)
                ->where('anr', '=', $name)
                ->first();
        }

        return false;
    }

   
    public function createMailbox($username, $storageGroup, $emailAddress, $mailNickname = null, $useDefaults = true, $baseDn = null, $isGUID = false)
    {
        $mailbox = new Mailbox([
            'username' => $username,
            'storageGroup' => $storageGroup,
            'emailAddress' => $emailAddress,
            'mailNickname' => $mailNickname,
            'baseDn' => ($baseDn ? $baseDn : $this->adldap->getBaseDn()),
            'mdbUseDefaults' => $this->adldap->utilities()->boolToStr($useDefaults),
        ]);

        // Validate the mailbox fields
        $mailbox->validateRequired();

        // Set the container attribute by imploding the storage group array
        $mailbox->setAttribute('container', 'CN='.implode(',CN=', $storageGroup));

        // Set the mail nickname to the username if it isn't provided
        if ($mailbox->{'mailNickname'} === null) {
            $mailbox->setAttribute('mailNickname', $mailbox->{'username'});
        }

        // Perform the creation and return the result
        return $this->adldap->user()->modify($username, $mailbox->toLdapArray(), $isGUID);
    }

    
    public function addX400($username, $country, $admd, $pdmd, $org, $surname, $givenName, $isGUID = false)
    {
        $this->adldap->utilities()->validateNotNull('Username', $username);

        $proxyValue = 'X400:';

        // Find the dn of the user
        $user = $this->adldap->user()->info($username, ['cn', 'proxyaddresses'], $isGUID);

        if ($user[0]['dn'] === null) {
            return false;
        }

        $userDn = $user[0]['dn'];

        // We do not have to demote an email address from the default so we can just add the new proxy address
        $attributes['exchange_proxyaddress'] = $proxyValue.'c='.$country.';a='.$admd.';p='.$pdmd.';o='.$org.';s='.$surname.';g='.$givenName.';';

        // Translate the update to the LDAP schema
        $add = $this->adldap->ldapSchema($attributes);

        if (!$add) {
            return false;
        }

      
        return $this->connection->add($userDn, $add);
    }

  
    public function addAddress($username, $emailAddress, $default = false, $isGUID = false)
    {
        $this->adldap->utilities()->validateNotNull('Username', $username);
        $this->adldap->utilities()->validateNotNull('Email Address', $emailAddress);

        $proxyValue = 'smtp:';

        if ($default === true) {
            $proxyValue = 'SMTP:';
        }

        // Find the dn of the user
        $user = $this->adldap->user()->info($username, ['cn', 'proxyaddresses'], $isGUID);

        if ($user[0]['dn'] === null) {
            return false;
        }

        $userDn = $user[0]['dn'];

        // We need to scan existing proxy addresses and demote the default one
        if (is_array($user[0]['proxyaddresses']) && $default === true) {
            $modAddresses = [];

            for ($i = 0; $i < $user[0]['proxyaddresses']['count']; $i++) {
                if (strpos($user[0]['proxyaddresses'][$i], 'SMTP:') !== false) {
                    $user[0]['proxyaddresses'][$i] = str_replace('SMTP:', 'smtp:', $user[0]['proxyaddresses'][$i]);
                }

                if ($user[0]['proxyaddresses'][$i] != '') {
                    $modAddresses['proxyAddresses'][$i] = $user[0]['proxyaddresses'][$i];
                }
            }

            $modAddresses['proxyAddresses'][($user[0]['proxyaddresses']['count'] - 1)] = 'SMTP:'.$emailAddress;

            $result = $this->connection->modReplace($userDn, $modAddresses);
        } else {
            // We do not have to demote an email address from the default so we can just add the new proxy address
            $attributes['exchange_proxyaddress'] = $proxyValue.$emailAddress;

            // Translate the update to the LDAP schema
            $add = $this->adldap->ldapSchema($attributes);

            if (!$add) {
                return false;
            }

            /*
             * Perform the update, take out the '@' to see any errors,
             * usually this error might occur because the address already
             * exists in the list of proxyAddresses
             */
            $result = $this->connection->modAdd($userDn, $add);
        }

        return $result;
    }

    public function deleteAddress($username, $emailAddress, $isGUID = false)
    {
        $this->adldap->utilities()->validateNotNull('Username', $username);
        $this->adldap->utilities()->validateNotNull('Email Address', $emailAddress);

        // Find the dn of the user
        $user = $this->adldap->user()->info($username, ['cn', 'proxyaddresses'], $isGUID);

        if ($user[0]['dn'] === null) {
            return false;
        }

        $userDn = $user[0]['dn'];

        if (is_array($user[0]['proxyaddresses'])) {
            $mod = [];

            for ($i = 0; $i < $user[0]['proxyaddresses']['count']; $i++) {
                if (strpos($user[0]['proxyaddresses'][$i], 'SMTP:') !== false && $user[0]['proxyaddresses'][$i] == 'SMTP:'.$emailAddress) {
                    $mod['proxyAddresses'][0] = 'SMTP:'.$emailAddress;
                } elseif (strpos($user[0]['proxyaddresses'][$i], 'smtp:') !== false && $user[0]['proxyaddresses'][$i] == 'smtp:'.$emailAddress) {
                    $mod['proxyAddresses'][0] = 'smtp:'.$emailAddress;
                }
            }

            return $this->connection->modDelete($userDn, $mod);
        }

        return false;
    }

    
    public function primaryAddress($username, $emailAddress, $isGUID = false)
    {
        $this->adldap->utilities()->validateNotNull('Username', $username);
        $this->adldap->utilities()->validateNotNull('Email Address', $emailAddress);

        // Find the dn of the user
        $user = $this->adldap->user()->info($username, ['cn', 'proxyaddresses'], $isGUID);

        if ($user[0]['dn'] === null) {
            return false;
        }

        $userDn = $user[0]['dn'];

        if (is_array($user[0]['proxyaddresses'])) {
            $modAddresses = [];

            for ($i = 0; $i < $user[0]['proxyaddresses']['count']; $i++) {
                if (strpos($user[0]['proxyaddresses'][$i], 'SMTP:') !== false) {
                    $user[0]['proxyaddresses'][$i] = str_replace('SMTP:', 'smtp:', $user[0]['proxyaddresses'][$i]);
                }

                if ($user[0]['proxyaddresses'][$i] == 'smtp:'.$emailAddress) {
                    $user[0]['proxyaddresses'][$i] = str_replace('smtp:', 'SMTP:', $user[0]['proxyaddresses'][$i]);
                }

                if ($user[0]['proxyaddresses'][$i] != '') {
                    $modAddresses['proxyAddresses'][$i] = $user[0]['proxyaddresses'][$i];
                }
            }

            return $this->connection->modReplace($userDn, $modAddresses);
        }

        return false;
    }

   
    public function contactMailEnable($distinguishedName, $emailAddress, $mailNickname = null)
    {
        $this->adldap->utilities()->validateNotNull('Distinguished Name [dn]', $distinguishedName);
        $this->adldap->utilities()->validateNotNull('Email Address', $emailAddress);

        if ($mailNickname !== null) {
            // Find the dn of the user
            $user = $this->adldap->contact()->info($distinguishedName, ['cn', 'displayname']);

            if ($user[0]['displayname'] === null) {
                return false;
            }

            $mailNickname = $user[0]['displayname'][0];
        }

        $attributes = ['email' => $emailAddress,'contact_email' => 'SMTP:'.$emailAddress,'exchange_proxyaddress' => 'SMTP:'.$emailAddress,'exchange_mailnickname' => $mailNickname];

        // Translate the update to the LDAP schema
        $mod = $this->adldap->ldapSchema($attributes);

        // Check to see if this is an enabled status update
        if (!$mod) {
            return false;
        }

        // Do the update
        return $this->connection->modify($distinguishedName, $mod);
    }

   
    public function servers($fields = [])
    {
        return $this->all($fields);
    }

    
    public function storageGroups($exchangeServer, $attributes = ['cn', 'distinguishedname'], $recursive = null)
    {
        $this->adldap->utilities()->validateNotNull('Exchange Server', $exchangeServer);

        $this->adldap->utilities()->validateLdapIsBound();

        if ($recursive === null) {
            $recursive = $this->adldap->getRecursiveGroups();
        }

        $filter = "(&(objectCategory=$this->storageGroupObjectCategory))";

        $results = $this->connection->search($exchangeServer, $filter, $attributes);

        if ($results) {
            $entries = $this->connection->getEntries($results);

            if ($recursive === true) {
                for ($i = 0; $i < $entries['count']; $i++) {
                    $entries[$i]['msexchprivatemdb'] = $this->storageDatabases($entries[$i]['distinguishedname'][0]);
                }
            }

            return $entries;
        }

        return false;
    }

   
    public function storageDatabases($storageGroup, $attributes = ['cn', 'distinguishedname', 'displayname'])
    {
        $this->adldap->utilities()->validateNotNull('Storage Group', $storageGroup);

        $this->adldap->utilities()->validateLdapIsBound();

        $filter = '(&(objectCategory=msExchPrivateMDB))';

        $results = $this->connection->search($storageGroup, $filter, $attributes);

        $entries = $this->connection->getEntries($results);

        return $entries;
    }

    
    private function getConfigurationNamingContext()
    {
        $result = $this->adldap->getRootDse(['configurationnamingcontext']);

        if (is_array($result) && array_key_exists('configurationnamingcontext', $result)) {
            return $result['configurationnamingcontext'];
        }

        return false;
    }
}
