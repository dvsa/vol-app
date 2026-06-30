<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterSectionVariant;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterSectionVariant by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterSectionVariant';

    protected $bundle = [
        'currentVersion',
        'goodsOrPsv',
        'organisationType',
        'letterChoice',
    ];
}
