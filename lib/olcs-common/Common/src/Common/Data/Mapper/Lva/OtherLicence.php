<?php

/**
 * OtherLicence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Data\Mapper\Lva;

/**
 * OtherLicence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OtherLicence
{
    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{id: mixed, version: mixed, licNo: mixed, willSurrender: mixed, holderName: mixed, disqualificationDate: mixed, disqualificationLength: mixed, previousLicenceType: array{id: mixed}}
     */
    public static function mapFromResult(array $data): array
    {
        return [
            'id' => $data['id'],
            'version' => $data['version'],
            'licNo' => $data['licNo'],
            'willSurrender' => $data['willSurrender'],
            'holderName' => $data['holderName'],
            'disqualificationDate' => $data['disqualificationDate'],
            'disqualificationLength' => $data['disqualificationLength'],
            'previousLicenceType' => [
                'id' => $data['previousLicenceType']['id']
            ]
        ];
    }
}
