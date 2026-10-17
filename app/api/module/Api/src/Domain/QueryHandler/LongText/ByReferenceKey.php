<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LongText;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\System\LongText;
use Dvsa\Olcs\Transfer\Query\LongText\ByReferenceKey as Query;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

final class ByReferenceKey extends AbstractQueryHandler
{
    protected $repoServiceName = 'LongText';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        assert($query instanceof Query);

        return $this->result(
            $this->getRepo()->fetchByReferenceKey(
                (string) $query->getReferenceKey(),
                $query->getLocale() ?? LongText::DEFAULT_LOCALE,
            ),
        );
    }
}
