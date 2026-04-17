<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitWindow\ById as IrhpPermitWindowByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as IrhpPermitWindowEntity;

/**
 * ById Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = IrhpPermitWindowByIdHandler::class;
    protected $sutRepo = 'IrhpPermitWindow';
    protected $bundle = ['irhpPermitStock'];
    protected $qryClass = QryClass::class;
    protected $repoClass = IrhpPermitWindowRepo::class;
    protected $entityClass = IrhpPermitWindowEntity::class;
}
