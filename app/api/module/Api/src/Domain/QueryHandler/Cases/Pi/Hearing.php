<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as PiHearingEntity;

/**
 * Pi Hearing
 */
final class Hearing extends AbstractQueryHandler
{
    protected $repoServiceName = 'PiHearing';

    /**
     * Handles query
     *
     * @param QueryInterface $query
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var PiHearingEntity $hearing */
        $hearing = $this->getRepo()->fetchUsingId($query);

        $values = array_merge(
            $hearing->getPi()->flattenSlaTargetDates(),
            [
                'isTm' => $hearing->getPi()->getCase()->isTm(),
            ]
        );

        return $this->result(
            $hearing,
            [
                'venue',
                'presidingTc',
                'presidedByRole',
                'pi' => [
                    'publicationLinks' => [
                        'publication' => [
                            'pubStatus'
                        ]
                    ]
                ],
            ],
            $values
        );
    }
}
