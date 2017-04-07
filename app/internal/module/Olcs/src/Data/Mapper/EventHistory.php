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

        $result['date'] = date(\DATETIME_FORMAT, strtotime($data['eventDatetime']));

        $result['by'] = isset($data['user']['contactDetails']['person']['forename']) &&
            isset($data['user']['contactDetails']['person']['familyName']) ?
            $data['user']['contactDetails']['person']['forename'] . ' ' .
            $data['user']['contactDetails']['person']['familyName'] : $data['user']['loginId'];

        //  prepare details
        $details = isset($data['eventHistoryDetails']) ? $data['eventHistoryDetails'] : [];

        $dateFields = ['open_date', 'closed_date', 'deleted_date'];
        array_walk(
            $details,
            function(&$item) use ($dateFields) {
                if (!in_array($item['name'], $dateFields)) {
                    return;
                }

                foreach (['newValue', 'oldValue'] as $key) {
                    if ($item[$key] !== null) {
                        $item[$key] = date(\DATETIME_FORMAT, strtotime($item['newValue'] . ' UTC'));
                    }
                }
            }
        );

        return [
            'readOnlyData' => $result,
            'eventHistoryDetails' => $details,
        ];
    }
}
