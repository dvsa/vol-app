<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Language;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\System\Language;
use Dvsa\Olcs\Transfer\Query\Language\GetList as GetListQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Supported system languages
 */
class GetList extends AbstractQueryHandler
{
    /**
     * Handle query
     *
     * @param QueryInterface|GetListQry $query query
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        return [
            'languages' => Language::SUPPORTED_LANGUAGES
        ];
    }
}
