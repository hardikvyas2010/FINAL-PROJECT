<?php

namespace Adldap\Classes;

use Adldap\Objects\Folder;
use Adldap\Adldap;


class AdldapFolders extends AbstractAdldapQueryable
{
    
    public function all($fields = [], $sorted = true, $sortBy = 'name', $sortByDirection = 'asc')
    {
        $search = $this->adldap->search()
            ->select($fields)
            ->where('objectClass', '*')
            ->where('distinguishedname', '!', $this->adldap->getBaseDn());

        if ($sorted) {
            $search->sortBy($sortBy, $sortByDirection);
        }

        return $search->get();
    }

    
    public function find($name, $fields = [])
    {
        $results = $this->adldap->search()
            ->select($fields)
            ->where('OU', '=', $name)
            ->first();

        if (count($results) > 0) {
            return $results;
        }

        return false;
    }

    
    public function listing($folders = [], $dnType = Adldap::ADLDAP_FOLDER, $recursive = null, $type = null)
    {
        $search = $this->adldap->search();

        if (is_array($folders) && count($folders) > 0) {
            
            $folders = array_reverse($folders);

            
            $ou = $dnType.'='.implode(','.$dnType.'=', $folders);

            $search->where('distinguishedname', '!', $ou.$this->adldap->getBaseDn());

            
            $dn = $ou.','.$this->adldap->getBaseDn();

            $search->setDn($dn);
        } else {
            $search->where('distinguishedname', '!', $this->adldap->getBaseDn());
        }

        if ($type === null) {
            $search->where('objectClass', '*');
        } else {
            $search->where('objectClass', '=', $type);
        }

        if ($recursive === false) {
            $search->recursive(false);
        }

        return $search->get();
    }

   
    public function create(array $attributes)
    {
        $folder = new Folder($attributes);

        $folder->validateRequired();

        $folder->setAttribute('container', array_reverse($folder->getAttribute('container')));

        $add = [];

        $add['objectClass'] = 'organizationalUnit';
        $add['OU'] = $folder->getAttribute('ou_name');

        $containers = 'OU='.implode(',OU=', $folder->getAttribute('container'));

        $dn = 'OU='.$add['OU'].', '.$containers.$this->adldap->getBaseDn();

        return $this->connection->add($dn, $add);
    }
}
