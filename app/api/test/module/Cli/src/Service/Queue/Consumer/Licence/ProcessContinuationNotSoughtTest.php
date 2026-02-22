<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Licence;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence\ProcessContinuationNotSought as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Process CNS Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ProcessContinuationNotSoughtTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(['id' => 1, 'version' => 2])
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['id' => 1, 'version' => 2], $result);
    }
}
