<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\CreateAlert as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse\CreateAlert;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as CompanyRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as AlertEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 *  Companies House Create Alert Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateAlertTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateAlert();
        $this->mockRepo('CompaniesHouseAlert', CompanyRepo::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);

        $this->refData = [
            'reason_foo',
            'reason_bar',
        ];

        parent::setUp();
    }

    /**
     * Test handleCommand method
     */
    public function testHandleCommand(): void
    {
        $companyNumber = '01234567';

        $organisation1 = m::mock(OrganisationEntity::class)->makePartial();
        $organisation2 = m::mock(OrganisationEntity::class)->makePartial();

        // expectations
        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->once()
            ->with($companyNumber)
            ->andReturn([$organisation1, $organisation2]);

        /** @var AlertEntity $alert */
        $alert = null;
        $id = 69;
        $this->repoMap['CompaniesHouseAlert']
            ->shouldReceive('save')
            ->twice()
            ->with(m::type(AlertEntity::class))
            ->andReturnUsing(
                function (AlertEntity $a) use (&$alert, &$id) {
                    $alert = $a;
                    $a->setId($id++);
                }
            );

        // invoke
        $command = Cmd::create(
            [
                'companyNumber' => $companyNumber,
                'reasons' => ['reason_foo', 'reason_bar'],
            ]
        );
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(['companiesHouseAlert69' => 69, 'companiesHouseAlert70' => 70], $result->getIds());
        $this->assertEquals(
            [
                'Alert created: ["reason_foo","reason_bar"]',
                'Alert created: ["reason_foo","reason_bar"]'
            ],
            $result->getMessages()
        );

        $this->assertEquals($companyNumber, $alert->getCompanyOrLlpNo());
        $this->assertEquals(2, $alert->getReasons()->count());
        $this->assertEquals('reason_foo', $alert->getReasons()[0]->getReasonType()->getId());
        $this->assertEquals('reason_bar', $alert->getReasons()[1]->getReasonType()->getId());
    }

    public function testHandleCommandNoOrganisation(): void
    {
        $companyNumber = '01234567';

        // expectations
        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->once()
            ->with($companyNumber)
            ->andThrow(new NotFoundException('organisation not found'));

        // invoke
        $command = Cmd::create(
            [
                'companyNumber' => $companyNumber,
                'reasons' => ['reason_foo', 'reason_bar'],
            ]
        );
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(
            ['Organisation(s) not found for company 01234567, no alert created'],
            $result->getMessages()
        );
    }
}
