<?php

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

/**
 * Community Licence mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicence implements MapperInterface
{
    /**
     * Map from result
     *
     * @param array $data data
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $dates = [];
        $resData = [];

        if (isset($data['currentSuspension'])) {
            $suspension = $data['currentSuspension'];
        } elseif (isset($data['futureSuspension'])) {
            $suspension = $data['futureSuspension'];
        }

        if (isset($suspension)) {
            $dates['startDate'] = $suspension['startDate'];
            $dates['endDate'] = $suspension['endDate'] ?? null;
            $resData['id'] = $suspension['id'];
            $resData['version'] = $suspension['version'];
            $resData['reasons'] = $suspension['reasons'] ?? null;
        }

        $resData['status'] = $data['status']['id'];

        return [
            'dates' => $dates,
            'data'  => $resData
        ];
    }

    /**
     * Map from form
     *
     * @param array $data               data
     * @param int   $communityLicenceId community licence id
     *
     * @return array
     */
    public static function mapFromForm(array $data, $communityLicenceId)
    {
        $dataFieldset = $data['data'];
        $result = [
            'id' => $dataFieldset['id'],
            'version' => $dataFieldset['version'],
            'communityLicenceId' => $communityLicenceId,
            'reasons' => $dataFieldset['reasons'],
            'status' => $dataFieldset['status']
        ];

        if (
            isset($data['dates']['startDate'])
            && !empty($data['dates']['startDate']['year'])
            && !empty($data['dates']['startDate']['month'])
            && !empty($data['dates']['startDate']['day'])
        ) {
            $startDate = $data['dates']['startDate'];
            $result['startDate'] = $startDate['year'] . '-' . $startDate['month'] . '-' . $startDate['day'];
        }

        if (
            isset($data['dates']['endDate'])
            && !empty($data['dates']['endDate']['year'])
            && !empty($data['dates']['endDate']['month'])
            && !empty($data['dates']['endDate']['day'])
        ) {
            $endDate = $data['dates']['endDate'];
            $result['endDate'] = $endDate['year'] . '-' . $endDate['month'] . '-' . $endDate['day'];
        }

        return $result;
    }
}
