<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\StoreSnapshot as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\StoreSnapshot as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Permits\IrhpGenerator;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCreateSnapshotHandlerTestCase;
use Mockery as m;

/**
 * StoreSnapshotTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class StoreSnapshotTest extends AbstractCreateSnapshotHandlerTestCase
{
    protected $cmdClass = Cmd::class;
    protected $sutClass = Sut::class;
    protected $repoServiceName = 'IrhpApplication';
    protected $repoClass = IrhpApplicationRepo::class;
    protected $entityClass = IrhpApplicationEntity::class;
    protected $documentCategory = Category::CATEGORY_PERMITS;
    protected $documentSubCategory = SubCategory::DOC_SUB_CATEGORY_PERMIT_APPLICATION;
    protected $documentDescription = 'Annual Irhp (EU and EEA) Application OG9654321/3 Snapshot (app submitted)';
    protected $documentLinkId = 'irhpApplication';
    public $documentLinkValue = 999;
    protected $generatorClass = IrhpGenerator::class;

    /**
     * Override this method in case of needing specific entity assertions i.e. for a permit application reference
     */
    #[\Override]
    protected function extraEntityAssertions(m\MockInterface $entity): mixed
    {
        $entity->shouldReceive('getApplicationRef')->once()->withNoArgs()->andReturn('OG9654321/3');
        $entity->shouldReceive('getIrhpPermitType->getName->getDescription')
            ->once()
            ->withNoArgs()
            ->andReturn('Annual Irhp (EU and EEA)');

        return $entity;
    }
}
