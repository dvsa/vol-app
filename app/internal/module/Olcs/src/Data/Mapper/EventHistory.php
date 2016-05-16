<?php

/**
 * Event History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Data\Mapper;

/**
 * Event History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EventHistory
{
    public static function mapFromResult(array $data)
    {
        $result['details'] = $data['eventHistoryType']['description'];
        $result['info'] = $data['eventData'];

        $dateTime = \DateTime::createFromFormat(\DateTime::ISO8601, $data['eventDatetime']);
        $result['date'] = $dateTime->format('H:i, d/m/y');

        $result['by'] = isset($data['user']['contactDetails']['person']['forename']) &&
            isset($data['user']['contactDetails']['person']['familyName']) ?
            $data['user']['contactDetails']['person']['forename'] . ' ' .
            $data['user']['contactDetails']['person']['familyName'] : $data['user']['loginId'];

        return [
            'readOnlyData' => $result,
            'eventHistoryDetails' => isset($data['eventHistoryDetails']) ? $data['eventHistoryDetails'] : []
        ];
    }
}
