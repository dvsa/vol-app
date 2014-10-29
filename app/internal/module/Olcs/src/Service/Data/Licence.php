<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Class Licence
 * @package Olcs\Service
 */
class Licence extends AbstractData
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $serviceName = 'Licence';

    /**
     * @param integer|null $id
     * @param array|null $bundle
     * @return array
     */
    public function fetchLicenceData($id = null, $bundle = null)
    {
        $id = is_null($id) ? $this->getId() : $id;

        if (is_null($this->getData($id))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($bundle)]);
            $this->setData($id, $data);
        }
        return $this->getData($id);
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'cases' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'appeals' => array(
                            'properties' => 'ALL',
                            'children' => array(
                                'outcome' => array(
                                    'properties' => array(
                                        'id',
                                        'description'
                                    )
                                ),
                                'reason' => array(
                                    'properties' => array(
                                        'id',
                                        'description'
                                    )
                                ),
                            )
                        ),
                        'stays' => array(
                            'properties' => 'ALL',
                            'children' => array(
                                'stayType' => array(
                                    'properties' => array(
                                        'id',
                                        'description'
                                    )
                                ),
                                'outcome' => array(
                                    'properties' => array(
                                        'id',
                                        'description'
                                    )
                                )
                            )
                        )
                    )
                ),
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
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
