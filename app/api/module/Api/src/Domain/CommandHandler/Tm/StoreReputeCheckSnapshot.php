<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCreateSnapshotHandler;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck\Generator;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

final class StoreReputeCheckSnapshot extends AbstractCreateSnapshotHandler
{
    protected $repoServiceName = 'IrhpApplication';
    protected $generatorClass = Generator::class;
    protected $documentCategory = Category::CATEGORY_TRANSPORT_MANAGER;
    protected $documentSubCategory = Category::DOC_SUB_CATEGORY_NR;
    protected $documentDescription = 'TM repute check snapshot (%s)';
    protected $documentLinkId = 'transportManager';

    /**
     * @inheritDoc
     */
    protected function getDocumentDescription(mixed $entity): string
    {
        /** @var TransportManager $entity */
        return sprintf(
            $this->documentDescription,
            $entity->getFullName()
        );
    }
}
