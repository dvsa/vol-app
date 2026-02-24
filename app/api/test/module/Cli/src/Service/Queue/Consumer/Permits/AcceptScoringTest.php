<?php

declare(strict_types=1);

/**
 * Accept Scoring Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\AcceptScoring as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Accept Scoring Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AcceptScoringTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setEntityId(188);

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['id' => 188], $result);
    }
}
