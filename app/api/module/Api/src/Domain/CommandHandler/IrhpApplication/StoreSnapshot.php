<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCreateSnapshotHandler;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Permits\IrhpGenerator;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Store a snapshot for Irhp application
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class StoreSnapshot extends AbstractCreateSnapshotHandler
{
    protected $repoServiceName = 'IrhpApplication';
    protected $generatorClass = IrhpGenerator::class;
    protected $documentCategory = Category::CATEGORY_PERMITS;
    protected $documentSubCategory = SubCategory::DOC_SUB_CATEGORY_PERMIT_APPLICATION;
    protected $documentDescription = '%s Application %s Snapshot (app submitted)';
    protected $documentLinkId = 'irhpApplication';

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function getDocumentDescription($entity): string
    {
        /** @var IrhpApplication $entity */
        return sprintf(
            $this->documentDescription,
            $entity->getIrhpPermitType()->getName()->getDescription(),
            $entity->getApplicationRef()
        );
    }
}
