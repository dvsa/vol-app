<?php

declare(strict_types=1);

/**
 * Run Scoring Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\RunScoring as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Run Scoring Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RunScoringTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setEntityId(135);
        $item->setOptions(json_encode(['deviation' => 1.5]));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['id' => 135, 'deviation' => 1.5], $result);
    }
}
