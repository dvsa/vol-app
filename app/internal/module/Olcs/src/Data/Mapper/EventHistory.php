<?php

namespace Olcs\Data\Mapper;

use Dvsa\Olcs\Utils\Helper\DateTimeHelper;
use Olcs\Module;

/**
 * Event History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EventHistory
{
    /**
     * Map From Result
     *
     * @param array $data Api Data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $result['details'] = $data['eventHistoryType']['description'];
        $result['info'] = $data['eventData'];

        $result['date'] = date(Module::$dateTimeFormat, strtotime($data['eventDatetime']));

        $result['by'] = isset($data['user']['contactDetails']['person']['forename']) &&
            isset($data['user']['contactDetails']['person']['familyName']) ?
            $data['user']['contactDetails']['person']['forename'] . ' ' .
            $data['user']['contactDetails']['person']['familyName'] : $data['user']['loginId'];

        //  prepare details
        $details = $data['eventHistoryDetails'] ?? [];

        $dateFields = ['open_date', 'closed_date', 'deleted_date'];
        array_walk(
            $details,
            function (&$item) use ($dateFields) {
                if (!in_array($item['name'], $dateFields)) {
                    return;
                }

                foreach (['newValue', 'oldValue'] as $key) {
                    if (!empty($item[$key])) {
                        $item[$key] = DateTimeHelper::format($item[$key], Module::$dateTimeFormat);
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
