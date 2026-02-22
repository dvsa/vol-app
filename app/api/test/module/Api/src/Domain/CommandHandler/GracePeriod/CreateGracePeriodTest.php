<?php

declare(strict_types=1);

/**
 * CreateTrailerTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GracePeriod;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\GracePeriod as GracePeriodRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\GracePeriod\CreateGracePeriod;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod;
use Dvsa\Olcs\Transfer\Command\GracePeriod\CreateGracePeriod as Cmd;

/**
 * Class UpdateTypeOfLicenceTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CreateGracePeriodTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateGracePeriod();
        $this->mockRepo('GracePeriod', GracePeriodRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'startDate' => '2015-01-01',
            'endDate' => '2015-01-02',
            'description' => 'description'
        ];

        $command = Cmd::create($data);

        $this->repoMap['GracePeriod']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(GracePeriod::class))
            ->andReturnUsing(
                function (GracePeriod $gracePeriod) use ($data) {
                    $this->assertEquals($gracePeriod->getStartDate()->format('Y-m-d'), $data['startDate']);
                    $this->assertEquals($gracePeriod->getEndDate()->format('Y-m-d'), $data['endDate']);
                    $this->assertEquals($gracePeriod->getDescription(), $data['description']);
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'graceperiod' => null
            ],
            'messages' => [
                'Grace period created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
