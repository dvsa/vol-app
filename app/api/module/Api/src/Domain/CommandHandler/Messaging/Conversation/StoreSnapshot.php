<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCreateSnapshotHandler;
use Dvsa\Olcs\Api\Domain\Repository\Conversation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Messaging\Generator;

final class StoreSnapshot extends AbstractCreateSnapshotHandler
{
    protected $repoServiceName = Conversation::class;
    protected $generatorClass = Generator::class;
    protected $documentCategory = Category::CATEGORY_LICENSING;
    protected $documentSubCategory = SubCategory::DOC_SUB_CATEGORY_LICENCING_OTHER_DOCUMENTS;
    protected $documentDescription = 'Conversation Snapshot';
    protected $documentLinkId = 'messagingConversation';

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function getDocumentDescription($entity): string
    {
        return $this->documentDescription;
    }
}
