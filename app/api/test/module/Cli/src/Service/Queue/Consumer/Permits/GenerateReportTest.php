<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\GenerateReport as GenerateReportConsumer;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Mockery as m;

/**
 * @see GenerateReportConsumer
 */
class GenerateReportTest extends AbstractConsumerTestCase
{
    protected $consumerClass = GenerateReportConsumer::class;

    public function testGetCommandData(): void
    {
        $options = '{"id":"cert_roadworthiness","startDate":"2020-12-25","endDate":"2020-12-31"}';
        $user = 291;

        $item = m::mock(QueueEntity::class);
        $item->expects('getOptions')->withNoArgs()->andReturn($options);
        $item->expects('getCreatedBy->getId')->withNoArgs()->andReturn($user);

        $expectedData = [
            'id' => 'cert_roadworthiness',
            'startDate' => '2020-12-25',
            'endDate' => '2020-12-31',
            'user' => 291,
        ];

        $this->assertEquals($expectedData, $this->sut->getCommandData($item));
    }
}
