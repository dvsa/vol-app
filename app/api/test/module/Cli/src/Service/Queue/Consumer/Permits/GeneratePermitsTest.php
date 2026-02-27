<?php

declare(strict_types=1);

/**
 * Generate Permits Test
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\GeneratePermits as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Generate Permits Test
 */
class GeneratePermitsTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(json_encode(['ids' => [1, 2, 3], 'user' => 456]));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['ids' => [1, 2, 3], 'user' => 456], $result);
    }
}
