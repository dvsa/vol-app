<?php

/**
 * Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

/**
 * Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicence implements MapperInterface
{
    /**
     * @return ((((int[]|mixed|null)[]|mixed)[]|mixed)[]|mixed)[]
     *
     * @psalm-return array{version: mixed, 'type-of-licence': array{'operator-location': mixed, 'operator-type': mixed, 'licence-type': array{'licence-type': mixed, ltyp_siContent: array{'vehicle-type': mixed|null, 'lgv-declaration': array{'lgv-declaration-confirmation': 0|1}}}}}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return [
            'version' => $data['version'],
            'type-of-licence' => [
                'operator-location' => $data['niFlag'],
                'operator-type' => $data['goodsOrPsv']['id'],
                'licence-type' => [
                    'licence-type' => $data['licenceType']['id'],
                    'ltyp_siContent' => [
                        'vehicle-type' => $data['vehicleType']['id'] ?? null,
                        'lgv-declaration' => [
                            'lgv-declaration-confirmation' => $data['lgvDeclarationConfirmation'] ? 1 : 0,
                        ]
                    ]
                ]
            ]
        ];
    }
}
