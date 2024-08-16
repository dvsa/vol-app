<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Filter\Format;

use Laminas\Filter\AbstractFilter;
use Doctrine\Common\Util\Debug as DoctrineDebug;

/**
 * Class SubmissionResult
 * @package Dvsa\Olcs\Api\Service\Ebsr\Filter\Format
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @template-extends AbstractFilter<array>
 */
class SubmissionResult extends AbstractFilter
{
    /**
     * Constants used for creating bus reg information in the ebsr_submission_result column
     */
    public const UNKNOWN_REG_NO = 'unknown reg no';
    public const UNKNOWN_SERVICE_NO = 'unknown service no';
    public const UNKNOWN_START_POINT = 'unknown start point';
    public const UNKNOWN_FINISH_POINT = 'unknown finish point';
    public const UNKNOWN_START_DATE = 'unknown start date';

    /**
     * common format for the various dates
     */
    public const DATE_FORMAT = 'l F jS Y';

    /**
     * How many levels of doctrine entities we recurse when creating our data array
     */
    public const DOCTRINE_DEBUG_LEVEL = 2;

    /**
     * Formats data for the ebsr_submission_result field
     *
     * @param array $value input values
     *
     * @return array
     */
    public function filter($value)
    {
        $rawData = DoctrineDebug::export($value['rawData'], self::DOCTRINE_DEBUG_LEVEL);

        //set some defaults
        $hasBusData = false;
        $regNo = self::UNKNOWN_REG_NO;
        $serviceNo = self::UNKNOWN_SERVICE_NO;
        $origin = self::UNKNOWN_START_POINT;
        $destination = self::UNKNOWN_FINISH_POINT;
        $startDate = self::UNKNOWN_START_DATE;

        //if the submission progressed far enough (i.e. beyond xml schema errors), then we will have a data array
        if (is_array($rawData)) {
            //check for a reg no
            if (isset($rawData['licNo']) && isset($rawData['routeNo'])) {
                $hasBusData = true;
                $regNo = $rawData['licNo'] . '/' . $rawData['routeNo'];
            }

            //check service no
            if (isset($rawData['serviceNo'])) {
                $hasBusData = true;
                $serviceNo = $rawData['serviceNo'];

                if (isset($rawData['otherServiceNumbers']) && is_array($rawData['otherServiceNumbers'])) {
                    $serviceNo .= ' (' . implode(', ', $rawData['otherServiceNumbers']) . ')';
                }
            }

            //check start point
            if (isset($rawData['startPoint'])) {
                $hasBusData = true;
                $origin = $rawData['startPoint'];
            }

            //check finish point
            if (isset($rawData['finishPoint'])) {
                $hasBusData = true;
                $destination = $rawData['finishPoint'];
            }

            //check effective date
            if (isset($rawData['effectiveDate'])) {
                $hasBusData = true;
                $startDate = $this->formatDate($rawData['effectiveDate']);
            }
        }

        return [
            'errors' => $value['errorMessages'],
            'extra_bus_data' => [
                'submissionDate' => $this->formatDate($value['ebsrSub']->getSubmittedDate()),
                'submissionErrors' => $value['errorMessages'],
                'registrationNumber' => $regNo,
                'origin' => $origin,
                'destination' => $destination,
                'lineName' => $serviceNo,
                'startDate' => $startDate,
                'hasBusData' => $hasBusData
            ]
        ];
    }

    /**
     * Formats dates for use in the logs we save to ebsr_submission_result column
     *
     * @param string|\DateTime $date the date
     *
     * @return string
     */
    private function formatDate($date)
    {
        if (!$date instanceof \DateTime) {
            return date(self::DATE_FORMAT, strtotime($date));
        }

        return $date->format(self::DATE_FORMAT);
    }
}
