<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get a list of document templates
 *
 * @author
 */
class FullList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'DocTemplateSearchView';
}
