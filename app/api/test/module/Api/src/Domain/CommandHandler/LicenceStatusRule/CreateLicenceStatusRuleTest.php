<?php

declare(strict_types=1);

/**
 * CreateLicenceStatusRuleTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceStatusRule;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as LicenceStatusRuleRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceStatusRule\CreateLicenceStatusRule;
use Dvsa\Olcs\Transfer\Command\LicenceStatusRule\CreateLicenceStatusRule as Cmd;

/**
 * Class CreateLicenceStatusRuleTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CreateLicenceStatusRuleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateLicenceStatusRule();
        $this->mockRepo('LicenceStatusRule', LicenceStatusRuleRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->references = [
            Licence::class => [
                1 => m::mock(Licence::class)
            ]
        ];

        $this->refData = ['lsts_curtailed'];

        parent::initReferences();
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
            ->with($this->references[Licence::class][1]);

        $this->repoMap['LicenceStatusRule']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(LicenceStatusRule::class))
            ->andReturnUsing(
                function (LicenceStatusRule $rule) use ($data) {
                    $this->assertEquals($rule->getLicence(), $this->references[Licence::class][1]);
                    $this->assertEquals($rule->getLicenceStatus(), $this->refData['lsts_curtailed']);
                    $this->assertInstanceOf('DateTime', $rule->getStartDate());
                    $this->assertEquals($rule->getStartDate()->format('Y-m-d H:i:s'), $data['startDate']);
                    $this->assertInstanceOf('DateTime', $rule->getEndDate());
                    $this->assertEquals($rule->getEndDate()->format('Y-m-d H:i:s'), $data['endDate']);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            $result->toArray(),
            [
                'id' => [
                    'licence-status-rule' => null
                ],
                'messages' => [
                    'Licence status rule created successfully'
                ]
            ]
        );
    }
}
