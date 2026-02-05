<?php

declare(strict_types=1);

/**
 * UpdateLicenceStatusRuleTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as StatusRuleRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule\UpdateLicenceStatusRule;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\UpdateLicenceStatusRule as Cmd;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Class UpdateLicenceStatusRuleTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\GracePeriod
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class UpdateLicenceStatusRuleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateLicenceStatusRule();
        $this->mockRepo('LicenceStatusRule', StatusRuleRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => 1,
            'status' => 'lsts_curtailed',
            'startDate' => '2015-01-01 00:00:00',
            'endDate' => '2015-02-01 00:00:00'
        ];

        $command = Cmd::create($data);

        $this->repoMap['Licence']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(LicenceEntity::class));

        $this->repoMap['LicenceStatusRule']
            ->shouldReceive('fetchById')
            ->once()
            ->andReturn(
                m::mock(LicenceStatusRule::class)
                    ->shouldReceive('setStartDate')
                    ->once()
                    ->shouldReceive('setEndDate')
                    ->once()
                    ->shouldReceive('getId')
                    ->once()
                    ->shouldReceive('getLicence')
                    ->once()
                    ->andReturn(
                        m::mock(LicenceEntity::class)
                            ->shouldReceive('setDecisions')
                            ->once()
                            ->with(m::type(ArrayCollection::class))->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('save')
            ->once()
            ->with(m::type(LicenceStatusRule::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'licence-status-rule' => null
            ],
            'messages' => [
                'Licence status rule updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
