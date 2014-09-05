<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Could be moved to common?
 *
 * Class Licence
 * @package Olcs\Service
 */
class User extends AbstractData implements ListDataInterface
{
    protected $id;
    protected $serviceName = 'User';

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the data
     * as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore this
     * flag if the data doesn't allow for option groups to be constructed.
     *
     * @param mixed $context
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchUserListData();
        $ret = [];

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $datum) {
            $ret[$datum['id']] = $datum['name'];
        }

        return $ret;
    }


    public function fetchUserListData($bundle= null)
    {
        if (is_null($this->getData('userlist'))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get('', ['bundle' => json_encode($bundle)]);
            $this->setData('userlist', false);
            if (isset($data['Results'])) {
                $this->setData('userlist', $data['Results']);
            }
        }

        return $this->getData('userlist');
    }

    public function getBundle()
    {
        $bundle = array(
            'properties' => 'ALL',
        );

        return $bundle;
    }
}