<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterChoices
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterChoice';
}
