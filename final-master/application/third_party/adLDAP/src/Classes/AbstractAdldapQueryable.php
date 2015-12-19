<?php

namespace Adldap\Classes;


abstract class AbstractAdldapQueryable extends AbstractAdldapBase
{
  
    public $objectClass = '';

  
    public function all($fields = [], $sorted = true, $sortBy = 'cn', $sortByDirection = 'asc')
    {
        $search = $this->adldap->search()
            ->select($fields)
            ->where('objectClass', '=', $this->objectClass);

        if ($sorted) {
            $search->sortBy($sortBy, $sortByDirection);
        }

        return $search->get();
    }


    public function find($name, $fields = [])
    {
        $results = $this->adldap->search()
            ->select($fields)
            ->where('objectClass', '=', $this->objectClass)
            ->where('anr', '=', $name)
            ->first();

        if (count($results) > 0) {
            return $results;
        }

        return false;
    }

  
    public function dn($name)
    {
        $info = $this->find($name);

        if (is_array($info) && array_key_exists('dn', $info)) {
            return $info['dn'];
        }

        return false;
    }

    public function delete($dn)
    {
        $this->adldap->utilities()->validateNotNullOrEmpty('Distinguished Name [dn]', $dn);

        return $this->connection->delete($dn);
    }


    public function info($name, $fields = [])
    {
        return $this->find($name, $fields);
    }


    public function inGroup($name, $group, $recursive = null)
    {
        if ($recursive === null) {
            $recursive = $this->adldap->getRecursiveGroups();
        }

      
        $groups = $this->groups($name, $recursive);

      
        if (is_array($groups) && in_array($group, $groups)) {
            return true;
        }

        return false;
    }


    public function groups($name, $recursive = null)
    {
        if ($recursive === null) {
            $recursive = $this->adldap->getRecursiveGroups();
        }

        $info = $this->find($name);

        if (is_array($info) && array_key_exists('memberof', $info)) {
            $groups = $this->adldap->utilities()->niceNames($info['memberof']);

            if ($recursive === true) {
                foreach ($groups as $id => $groupName) {
                    $extraGroups = $this->adldap->group()->recursiveGroups($groupName);

                    $groups = array_merge($groups, $extraGroups);
                }
            }

           
            return array_unique($groups);
        }

        return false;
    }
}
