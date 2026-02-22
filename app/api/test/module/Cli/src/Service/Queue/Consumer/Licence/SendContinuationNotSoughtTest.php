<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Licence;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence\SendContinuationNotSought as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Send CNS Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SendContinuationNotSoughtTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(['licences' => 'licences', 'date' => ['date' => '2016-01-01']])
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['licences' => 'licences', 'date' => new DateTime('2016-01-01')], $result);
    }
}
