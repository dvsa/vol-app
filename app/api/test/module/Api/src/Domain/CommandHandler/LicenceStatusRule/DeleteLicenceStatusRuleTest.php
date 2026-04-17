<?php

declare(strict_types=1);

/**
 * DeleteLicenceStatusRuleTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as LicenceStatusRuleRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule\DeleteLicenceStatusRule;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\DeleteLicenceStatusRule as Cmd;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Class DeleteGracePeriodTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class DeleteLicenceStatusRuleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteLicenceStatusRule();
        $this->mockRepo('LicenceStatusRule', LicenceStatusRuleRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1
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
                    ->shouldReceive('getLicence')
                    ->once()
                    ->andReturn(
                        m::mock(LicenceEntity::class)
                            ->shouldReceive('setDecisions')
                            ->once()
                            ->with(m::type(ArrayCollection::class))->getMock()
                    )->getMock()
            )
            ->shouldReceive('delete')
            ->once()
            ->with(m::type(LicenceStatusRule::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'licence-status-rule' => 1
            ],
            'messages' => [
                'Licence status rule deleted.'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
