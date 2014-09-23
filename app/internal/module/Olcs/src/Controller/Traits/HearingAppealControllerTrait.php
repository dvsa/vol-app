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
                'case' => $caseId,
                'bundle' => json_encode($this->appealDataBundle)
            )
        );

        $appeal = array();

        if (!empty($appealResult['Results'][0])) {
            $appeal = $this->formatDates(
                $appealResult['Results'][0],
                array(
                    'deadlineDate',
                    'appealDate',
                    'hearingDate',
                    'decisionDate',
                    'papersDueDate',
                    'papersSentDate',
                    'withdrawnDate'
                )
            );
        }

        return $appeal;
    }

    /**
     * Formats the specified fields in the supplied array with the correct date format
     * Expect to replace this with a view helper later
     *
     * @param array $data
     * @param array $fields
     * @return array
     */
    private function formatDates($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = date('d/m/Y', strtotime($data[$field]));
            }
        }

        return $data;
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

        $stayResult = $this->makeRestCall('Stay', 'GET',
            array('case' => $caseId), $this->stayRecordBundle);

        //need a better way to do this...
        foreach ($stayResult['Results'] as $stay) {
            $stayRecords[$stay['stayType']['id']][] = $stay;
        }

        return $stayRecords;
    }
}
