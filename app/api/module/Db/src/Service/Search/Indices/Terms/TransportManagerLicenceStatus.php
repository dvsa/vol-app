<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Db\Service\Search\Indices\Terms;

class TransportManagerLicenceStatus implements ComplexTermInterface
{
    public function applySearch(array &$params): void
    {
        $params['must_not'][] = [
            'terms' => [
                'app_status_id' => [
                    'apsts_refused',
                    'apsts_valid',
                    'apsts_curtailed',
                    'apsts_withdrawn',
                    'apsts_cancelled',
                    'apsts_not_submitted',
                ],
            ],
        ];
        $params['must_not'][] = [
            'terms' => [
                'lic_status' => [
                    'lsts_cancelled',
                    'lsts_terminated',
                    'lsts_withdrawn',
                ],
            ],
        ];
        $params['must_not'][] = [
            'exists' => [
                'field' => 'date_removed',
            ],
        ];
    }
}
