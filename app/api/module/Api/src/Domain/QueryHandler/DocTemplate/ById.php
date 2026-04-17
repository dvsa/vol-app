<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a doc template by id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'DocTemplate';
    protected $bundle = ['document', 'category', 'subCategory' => ['category'], 'letterType'];
}
