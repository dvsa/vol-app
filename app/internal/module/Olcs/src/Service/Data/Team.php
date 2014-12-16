<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Class Team
 * @package Olcs\Service
 */
class Team extends AbstractData implements ListDataInterface
{
    protected $id;
    protected $serviceName = 'Team';

    /**
     * @param mixed $context
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchTeamListData();
        $ret = [];

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $datum) {
            $ret[$datum['id']] = $datum['name'];
        }

        return $ret;
    }


    public function fetchTeamListData($bundle = null)
    {
        if (is_null($this->getData('teamlist'))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get('', ['bundle' => json_encode($bundle)]);
            $this->setData('teamlist', false);
            if (isset($data['Results'])) {
                $this->setData('teamlist', $data['Results']);
            }
        }

        return $this->getData('teamlist');
    }

    public function getBundle()
    {
        $bundle = array(
            'properties' => 'ALL',
        );

        return $bundle;
    }
}
