<?php

namespace Adldap\Classes;

use Adldap\Objects\Group;
use Adldap\Adldap;


class AdldapGroups extends AbstractAdldapQueryable
{
   
    public $objectCategory = 'group';

    
    public $objectClass = 'group';

    
    public function search($sAMAaccountType = Adldap::ADLDAP_SECURITY_GLOBAL_GROUP, $select = [], $sorted = true)
    {
        $search = $this->adldap->search()
            ->select($select)
            ->where('objectCategory', '=', 'group');

        if ($sAMAaccountType !== null) {
            $search->where('samaccounttype', '=', $sAMAaccountType);
        }

        if ($sorted) {
            $search->sortBy('samaccountname', 'asc');
        }

        return $search->get();
    }

    
    public function addGroup($parent, $child)
    {
        // Find the parent group's dn
        $parentDn = $this->dn($parent);

        $childDn = $this->dn($child);

        if ($parentDn && $childDn) {
            $add['member'] = $childDn;

            // Add the child to the parent group and return the result
            return $this->connection->modAdd($parentDn, $add);
        }

        return false;
    }

    public function addUser($groupName, $username)
    {
        $groupDn = $this->dn($groupName);

        $userDn = $this->adldap->user()->dn($username);

        if ($groupDn && $userDn) {
            $add['member'] = $userDn;

            return $this->connection->modAdd($groupDn, $add);
        }

        return false;
    }

   
    public function addContact($groupName, $contactDn)
    {
        $groupDn = $this->dn($groupName);

        if ($groupDn && $contactDn) {
            $add = [];
            $add['member'] = $contactDn;

            return $this->connection->modAdd($groupDn, $add);
        }

        return false;
    }


    public function create(array $attributes)
    {
        $group = new Group($attributes);

        $group->validateRequired();

        // Reset the container by reversing the current container
        $group->setAttribute('container', array_reverse($group->getAttribute('container')));

        $add['cn'] = $group->getAttribute('group_name');
        $add['samaccountname'] = $group->getAttribute('group_name');
        $add['objectClass'] = 'Group';
        $add['description'] = $group->getAttribute('description');

        $container = 'OU='.implode(',OU=', $group->getAttribute('container'));

        $dn = 'CN='.$add['cn'].', '.$container.','.$this->adldap->getBaseDn();

        return $this->connection->add($dn, $add);
    }

   
    public function rename($groupName, $newName, $container)
    {
        $groupDn = $this->dn($groupName);

        if ($groupDn) {
            $newRDN = 'CN='.$newName;

            // Determine the container
            $container = array_reverse($container);
            $container = 'OU='.implode(', OU=', $container);

            $dn = $container.', '.$this->adldap->getBaseDn();

            return $this->connection->rename($groupDn, $newRDN, $dn, true);
        }

        return false;
    }

   
    public function removeGroup($parentName, $childName)
    {
        $parentDn = $this->dn($parentName);

        $childDn = $this->dn($childName);

        if (is_string($parentDn) && is_string($childDn)) {
            $del = [];
            $del['member'] = $childDn;

            return $this->connection->modDelete($parentDn, $del);
        }

        return false;
    }

   
    public function removeUser($groupName, $username)
    {
        $groupDn = $this->dn($groupName);

        $userDn = $this->adldap->user()->dn($username);

        if (is_string($groupDn) && is_string($userDn)) {
            $del = [];
            $del['member'] = $userDn;

            return $this->connection->modDelete($groupDn, $del);
        }

        return false;
    }

   
    public function removeContact($group, $contactName)
    {
        // Find the parent dn
        $groupDn = $this->dn($group);

        $contactDn = $this->adldap->contact()->dn($contactName);

        if (is_string($groupDn) && is_string($contactDn)) {
            $del = [];
            $del['member'] = $contactDn;

            return $this->connection->modDelete($groupDn, $del);
        }

        return false;
    }

    public function members($group, $fields = [])
    {
        $group = $this->find($group);

        if (is_array($group) && array_key_exists('member', $group)) {
            $members = [];

            foreach ($group['member'] as $member) {
                $members[] = $this->adldap->search()
                    ->setDn($member)
                    ->select($fields)
                    ->where('objectClass', '=', 'user')
                    ->where('objectClass', '=', 'person')
                    ->first();
            }

            return $members;
        }

        return false;
    }

    
    public function recursiveGroups($groupName)
    {
        $groups = [];

        $info = $this->find($groupName);

        if (is_array($info) && array_key_exists('cn', $info)) {
            $groups[] = $info['cn'];

            if (array_key_exists('memberof', $info)) {
                if (is_array($info['memberof'])) {
                    foreach ($info['memberof'] as $group) {
                        $explodedDn = $this->connection->explodeDn($group);

                        $groups = array_merge($groups, $this->recursiveGroups($explodedDn[0]));
                    }
                }
            }
        }

        return $groups;
    }

    
    public function allSecurity($includeDescription = false, $search = '*', $sorted = true)
    {
        return $this->search(Adldap::ADLDAP_SECURITY_GLOBAL_GROUP, $includeDescription, $search, $sorted);
    }

    
    public function allDistribution($includeDescription = false, $search = '*', $sorted = true)
    {
        return $this->search(Adldap::ADLDAP_DISTRIBUTION_GROUP, $includeDescription, $search, $sorted);
    }

   
    public function getPrimaryGroup($groupId, $userId)
    {
        $this->adldap->utilities()->validateNotNull('Group ID', $groupId);
        $this->adldap->utilities()->validateNotNull('User ID', $userId);

        $groupId = substr_replace($userId, pack('V', $groupId), strlen($userId) - 4, 4);

        $sid = $this->adldap->utilities()->getTextSID($groupId);

        $result = $this->adldap->search()
                ->where('objectsid', '=', $sid)
                ->first();

        if (is_array($result) && array_key_exists('dn', $result)) {
            return $result['dn'];
        }

        return false;
    }
}
