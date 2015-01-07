<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Class Cases
 * @package Olcs\Service
 */
class Cases extends AbstractData
{
    /**
     * @var string
     */
    protected $serviceName = 'Cases';

    /**
     * @param integer $id
     * @param array|null $bundle
     * @return array
     */
    public function fetchCaseData($id, $bundle = null)
    {
        if (is_null($this->getData($id))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($bundle)]);
            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    public function fetchList($params = [], $bundle = null)
    {
        if (null === $bundle) {
            $bundle = $this->getBundle();
        }
        $params['bundle'] = json_encode($bundle);

        return $this->getRestClient()->get('', $params);
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        $bundle = array(
            'children' => array(
                /**
                 * @note [OLCS-5306] check this, it appears to be an invalid part of the bundle
                'submissionSections' => array(
                    'properties' => array(
                        'id',
                        'description'
                    )
                ),
                 */
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
                ),
                'legacyOffences' => array(
                    'properties' => 'ALL',
                ),
                'caseType' => array(
                    'properties' => 'id',
                ),
                'licence' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'status' => array(
                            'properties' => array('id')
                        ),
                        'licenceType' => array(
                            'properties' => array('id', 'description', 'olbsKey')
                        ),
                        'goodsOrPsv' => array(
                            'properties' => array('id')
                        ),
                        'trafficArea' => array(
                            'properties' => 'ALL'
                        ),
                        'establishmentCd' => array(
                            'properties' => 'ALL',
                            'children' => array(
                                'address' => array(
                                    'properties' => 'ALL'
                                )
                            )
                        ),
                        'organisation' => array(
                            'properties' => 'ALL',
                            'children' => array(
                                'type' => array(
                                    'properties' => array('id')
                                ),
                                'tradingNames' => array(
                                    'properties' => 'ALL'
                                ),
                                'organisationPersons' => array(
                                    'properties' => 'ALL',
                                    'children' => array(
                                        'person' => array(
                                            'properties' => 'ALL',
                                            'children' => array(
                                                'contactDetails' => array(
                                                    'properties' => 'ALL'
                                                )
                                            )
                                        )
                                    )
                                ),
                                'contactDetails' => array(
                                    'properties' => 'ALL',
                                    'children' => array(
                                        'address' => array(
                                            'properties' => 'ALL'
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
                'application' => array(
                    'properties' => 'ALL'
                )
            )
        );

        return $bundle;
    }
}
