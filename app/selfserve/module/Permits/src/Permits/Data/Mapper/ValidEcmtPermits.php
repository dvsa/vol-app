<?php

namespace Permits\Data\Mapper;

use JsonSchema\Exception\ResourceNotFoundException;

/**
 *
 * Valid ECMT permits list mapper
 */
class ValidEcmtPermits
{
    public static function mapForDisplay(array $data)
    {
        $permits = [];

        if (empty($data)) {
            throw new ResourceNotFoundException('Permits not found');
        }

        $cnt = 0;
        foreach ($data['result'] as $permit) {
            $cnt++;
            $permitNo = array_key_exists('permitNumber', $permit) ? $permit['permitNumber'] : $cnt;
            $issueDate = array_key_exists('issueDate', $permit) ? $permit['issueDate'] : '';
            $permits[] = [
                'permitNumber' => $permitNo,
                'status' => $permit['irhpPermitApplication']['ecmtPermitApplication']['status'],
                'issueDate' => $issueDate,
                'countries' => $permit['irhpPermitRange']['countrys'],
            ];
        }

        $firstPermit = $data['result'][0];
        // TODO - OLCS-21979 - move to the backend
        $dueDate = '';
        if (!array_key_exists('permitNumber', $firstPermit)) {
            $dueDate = date(
                \DATE_FORMAT,
                strtotime(
                    "+9 weekdays",
                    strtotime(
                        $firstPermit['irhpPermitApplication']['ecmtPermitApplication']['fees'][0]['invoicedDate']
                    )
                )
            );
        }
        return [
            'irhpPermitStock' => $firstPermit['irhpPermitRange']['irhpPermitStock'],
            'results' => $permits,
            'count' => $data['count'],
            'ref' => $firstPermit['irhpPermitApplication']['ecmtPermitApplication']['applicationRef'],
            'status' => $firstPermit['irhpPermitApplication']['ecmtPermitApplication']['status']['description'],
            'dueDate' => $dueDate
        ];
    }
}
