<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterAppendixs
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterAppendix';

    protected $bundle = [
        'currentVersion'
    ];
}
