<?php

declare(strict_types=1);

/**
 * Post scoring email test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\PostScoringEmail as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Post scoring email test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PostScoringEmailTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(json_encode(['identifier' => 'identifierXYZ123']));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['documentIdentifier' => 'identifierXYZ123'], $result);
    }
}
