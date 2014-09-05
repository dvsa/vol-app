<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Class Licence
 * @package Olcs\Service
 */
class Licence extends AbstractData
{
    protected $id;
    protected $serviceName = 'Licence';

    public function fetchLicenceData($id = null, $bundle= null)
    {
        $id = is_null($id) ? $this->getId() : $id;

        if (is_null($this->getData($id))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($bundle)]);
            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    public function getBundle()
    {
        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'status' => array(
                    'properties' => array('id', 'description')
                ),
                'goodsOrPsv' => array(
                    'properties' => array('id', 'description')
                ),
                'licenceType' => array(
                    'properties' => array('id', 'description')
                ),
                'trafficArea' => array(
                    'properties' => 'ALL'
                ),
                'organisation' => array(
                    'properties' => 'ALL'
                )
            )
        );

        return $bundle;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
