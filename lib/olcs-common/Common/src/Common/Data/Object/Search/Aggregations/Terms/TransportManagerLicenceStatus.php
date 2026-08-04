<?php

declare(strict_types=1);

namespace Common\Data\Object\Search\Aggregations\Terms;

use Common\Data\Object\Search\ComplexTermInterface;

class TransportManagerLicenceStatus extends TermsAbstract implements ComplexTermInterface
{
    protected $title = 'search.form.filter.transport-manager-licence-status';
    protected $key = 'TransportManagerLicenceStatus';

    #[\Override]
    public function getType(): string
    {
        return self::TYPE_COMPLEX;
    }

    #[\Override]
    public function getOptionsKvp(): array
    {
        return $this->getOptions();
    }

    #[\Override]
    public function getOptions(): array
    {
        return [
            '1' => 'Active only',
        ];
    }

    #[\Override]
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
