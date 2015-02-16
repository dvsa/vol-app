<?php

/**
 * HearingAppeal Controller Trait
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\Mvc\MvcEvent;

/**
 * HearingAppeal Controller Trait
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
trait HearingAppealControllerTrait
{

    /**
     * Holds the appeal data Bundle
     *
     * @var array
     */
    protected $appealDataBundle = array(
        'children' => array(
            'reason' => array(
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
    );

    /**
     * Holds the Stay Record Bundle
     *
     * @var array
     */
    protected $stayRecordBundle = array(
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
            ),
            'case' => array(
                'properties' => array(
                    'id'
                )
            )
        )
    );

    /**
     * @return array
     */
    public function getAppealDataBundle()
    {
        return $this->appealDataBundle;
    }

    /**
     * @return array
     */
    public function getStayRecordBundle()
    {
        return $this->stayRecordBundle;
    }

    /**
     * Retrieves appeal data
     *
     * @param int $caseId
     * @return array
     */
    public function getAppealData($caseId)
    {
        $appealResult = $this->makeRestCall(
            'Appeal',
            'GET',
            array(
                'case' => $caseId
            ),
            $this->getAppealDataBundle()
        );

        $appeal = array();

        if (isset($appealResult['Results'][0])) {
            $appeal = $appealResult['Results'][0];
        }

        return $appeal;
    }

    /**
     * Gets stay data for use on the index page
     *
     * @param int $caseId
     * @return array
     */
    public function getStayData($caseId)
    {
        $stayRecords = array();

        $stayResult = $this->makeRestCall(
            'Stay',
            'GET',
            array('case' => $caseId),
            $this->getStayRecordBundle()
        );

        //need a better way to do this...
        foreach ($stayResult['Results'] as $stay) {
            $stayRecords[$stay['stayType']['id']][] = $stay;
        }

        return $stayRecords;
    }
}
