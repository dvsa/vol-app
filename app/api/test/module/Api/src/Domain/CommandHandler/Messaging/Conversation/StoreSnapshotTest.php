<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation\StoreSnapshot as Sut;
use Dvsa\Olcs\Api\Domain\Command\Messaging\Conversation\StoreSnapshot as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Conversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Messaging\Generator;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCreateSnapshotHandlerTestCase;

class StoreSnapshotTest extends AbstractCreateSnapshotHandlerTestCase
{
    protected $cmdClass = Cmd::class;
    protected $sutClass = Sut::class;
    protected $repoServiceName = Conversation::class;
    protected $repoClass = Conversation::class;
    protected $entityClass = MessagingConversation::class;
    protected $documentCategory = Category::CATEGORY_LICENSING;
    protected $documentSubCategory = SubCategory::DOC_SUB_CATEGORY_LICENCING_OTHER_DOCUMENTS;
    protected $documentDescription = 'Conversation Snapshot';
    protected $documentLinkId = 'messagingConversation';
    public $documentLinkValue = 999;
    protected $generatorClass = Generator::class;
}
