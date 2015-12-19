<?php

namespace Adldap\Classes;


class AdldapComputers extends AbstractAdldapQueryable
{
  
    public $objectClass = 'computer';

 
    public function groups($computerName, $recursive = null)
    {
        if ($recursive === null) {
            $recursive = $this->adldap->getRecursiveGroups();
        }

        $info = $this->find($computerName);

        if (is_array($info) && array_key_exists('memberof', $info)) {
            $groups = $this->adldap->utilities()->niceNames($info['memberof']);

            if ($recursive === true) {
                foreach ($groups as $id => $groupName) {
                    $extraGroups = $this->adldap->group()->recursiveGroups($groupName);

                    $groups = array_merge($groups, $extraGroups);
                }
            }

            return $groups;
        }

        return false;
    }
}
