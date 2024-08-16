<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Category list currently associated with templates
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class TemplateCategories extends AbstractQueryHandler
{
    protected $repoServiceName = 'Template';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $categories = $this->getRepo()->fetchDistinctCategories();
        return [
            'result' => $categories,
            'count' => count($categories),
        ];
    }
}
