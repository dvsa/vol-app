<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterAppendix by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterAppendix';

    protected $bundle = [
        'currentVersion'
    ];
}
