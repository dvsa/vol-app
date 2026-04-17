<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\RemoveDeleteDocuments;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\RemoveDeleteDocuments::class)]
class RemoveDeletedDocumentsTest extends AbstractConsumerTestCase
{
    protected $consumerClass = RemoveDeleteDocuments::class;

    /** @var  RemoveDeleteDocuments */
    protected $sut;

    public function testGetCommandData(): void
    {
        $mockQueue = m::mock(Queue::class);
        $this->assertSame([], $this->sut->getCommandData($mockQueue));
    }
}
