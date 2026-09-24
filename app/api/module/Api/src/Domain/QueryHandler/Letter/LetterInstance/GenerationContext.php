<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance\LetterContextTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\GenerationContext as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Goods/PSV and NI context for a letter that is about to be generated
 */
final class GenerationContext extends AbstractQueryHandler
{
    use LetterContextTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = [
        'Application',
        'Cases',
        'BusReg',
        'TransportManager',
        'IrhpApplication',
        'Organisation',
    ];

    /**
     * @param QueryInterface $query
     * @return array{goodsOrPsv: ?string, isNi: ?bool}
     */
    #[\Override]
    public function handleQuery(QueryInterface $query): array
    {
        /** @var Qry $query */

        // Never persisted, it just carries the relations Generate would set
        $letterInstance = new LetterInstanceEntity();
        $this->setOptionalRelations($letterInstance, $query);

        return $this->goodsOrPsvAndNi($letterInstance);
    }
}
