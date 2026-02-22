<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\TemplateSource as TemplateSourceHandler;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Transfer\Query\Template\TemplateSource as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Template\Template as TemplateEntity;

/**
 * Template Source Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class TemplateSourceTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = TemplateSourceHandler::class;
    protected $sutRepo = 'Template';
    protected $qryClass = QryClass::class;
    protected $repoClass = TemplateRepo::class;
    protected $entityClass = TemplateEntity::class;
}
