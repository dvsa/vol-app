<?php

declare(strict_types=1);

/**
 * RemoveLicenceStatusRulesForLicenceTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\DeleteLicenceStatusRule;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as LicenceStatusRuleRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule\RemoveLicenceStatusRulesForLicence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\RemoveLicenceStatusRulesForLicence as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Class RemoveLicenceStatusRulesForLicenceTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class RemoveLicenceStatusRulesForLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RemoveLicenceStatusRulesForLicence();
        $this->mockRepo('LicenceStatusRule', LicenceStatusRuleRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => m::mock(Licence::class)->shouldReceive('getId')->getMock()
        ];

        $command = Cmd::create($data);

        $this->repoMap['Licence']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(LicenceEntity::class));

        $this->repoMap['LicenceStatusRule']
            ->shouldReceive('fetchForLicence')
            ->once()
            ->andReturn(
                [
                    m::mock(LicenceStatusRule::class)
                        ->shouldReceive('getId')
                        ->once()
                        ->andReturn(1)
                        ->shouldReceive('getLicence')
                        ->once()
                        ->andReturn(
                            m::mock(LicenceEntity::class)
                                ->shouldReceive('setDecisions')
                                ->once()
                                ->with(m::type(ArrayCollection::class))->getMock()
                        )->getMock()
                ]
            );

        $this->expectedSideEffect(DeleteLicenceStatusRule::class, ['id' => 1], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
            ],
            'messages' => [
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
