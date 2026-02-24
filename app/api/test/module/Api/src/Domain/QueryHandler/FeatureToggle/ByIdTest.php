<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\FeatureToggle;

use Dvsa\Olcs\Api\Domain\QueryHandler\FeatureToggle\ById as ToggleByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeatureToggle as FeatureToggleRepo;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle as FeatureToggleEntity;

/**
 * ById Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = ToggleByIdHandler::class;
    protected $sutRepo = 'FeatureToggle';
    protected $qryClass = QryClass::class;
    protected $repoClass = FeatureToggleRepo::class;
    protected $entityClass = FeatureToggleEntity::class;
}
