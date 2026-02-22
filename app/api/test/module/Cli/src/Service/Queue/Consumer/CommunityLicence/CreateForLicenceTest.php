<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\CommunityLicence;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence\CreateForLicence as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Create for licence test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateForLicenceTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(['licence' => 'OB123', 'totalLicences' => 1])
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['licence' => 'OB123', 'totalLicences' => 1], $result);
    }
}
