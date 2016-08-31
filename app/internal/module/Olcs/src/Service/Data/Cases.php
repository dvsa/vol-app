<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Olcs\Data\Object\Cases as CaseDataObject;
use Common\Service\Data\CloseableInterface;

/**
 * Class Cases
 * @package Olcs\Service
 */
class Cases extends AbstractData implements CloseableInterface
{
    /**
     * @var string
     */
    protected $serviceName = 'Cases';

    /**
     * Wrapper method to match interface. Calls fetchCaseData.
     *
     * @param $id
     * @param null $bundle
     * @return array
     */
    public function fetchData($id, $bundle = null)
    {
        return $this->fetchCaseData($id, $bundle);
    }

    /**
     * @NOTE Migrated this to default Cases query
     *
     * @param integer $id
     * @param array|null $bundle
     * @return array
     */
    public function fetchCaseData($id, $bundle = null)
    {
        if (is_null($this->getData($id))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data = $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($bundle)]);
            if ($data) {
                $data = new CaseDataObject($data);
            }
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
                'outcomes',
                'categorys',
                'appeal' => array(
                    'children' => array(
                        'outcome' => array(),
                        'reason' => array(),
                    )
                ),
                'stays' => array(
                    'children' => array(
                        'stayType' => array(),
                        'outcome' => array()
                    )
                ),
                'legacyOffences' => array(),
                'transportManager' => array(),
                'caseType' => array(),
                'licence' => array(
                    'children' => array(
                        'status' => array(),
                        'licenceType' => array(),
                        'goodsOrPsv' => array(),
                        'trafficArea' => array(),
                        'establishmentCd' => array(
                            'children' => array(
                                'address' => array()
                            )
                        ),
                        'organisation' => array(
                            'children' => array(
                                'type' => array(),
                                'tradingNames' => array(),
                                'organisationPersons' => array(
                                    'children' => array(
                                        'person' => array(
                                            'children' => array(
                                                'contactDetails' => array()
                                            )
                                        )
                                    )
                                ),
                                'contactDetails' => array(
                                    'children' => array(
                                        'address' => array()
                                    )
                                )
                            )
                        )
                    )
                ),
                'application' => array(
                    'children' => array(
                        'status',
                        'operatingCentres',
                        'goodsOrPsv',
                        'publicationLinks' => array(
                            'criteria' => array(
                                'publicationSection' => array(1,3)
                            ),
                            'children' => array(
                                'publication'
                            )
                        )
                    )
                ),
                'tmDecisions' => array()
            )
        );

        return $bundle;
    }

    /**
     * Can this entity be closed
     * @param $id
     * @return bool
     */
    public function canClose($id)
    {
        $data = $this->fetchCaseData($id);

        if (isset($data['outcomes']) && !empty($data['outcomes'])) {
            return !$this->isClosed($id);
        }

        return false;
    }

    /**
     * Is this entity closed
     * @param $id
     * @return bool
     */
    public function isClosed($id)
    {
        $data = $this->fetchCaseData($id);

        return (bool) isset($data['closedDate']);
    }

    /**
     * Can this entity be reopened
     * @param $id
     * @return bool
     */
    public function canReopen($id)
    {
        return $this->isClosed($id);
    }
}
