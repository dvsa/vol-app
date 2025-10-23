<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterTodos
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterTodo';
    
    protected $bundle = [
        'currentVersion'
    ];
}
