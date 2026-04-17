<?php

declare(strict_types=1);

/**
 * Update Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Complaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Complaint\UpdateComplaint;
use Dvsa\Olcs\Api\Domain\Repository\Complaint;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Complaint\UpdateComplaint as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Update Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateComplaintTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateComplaint();
        $this->mockRepo('Complaint', Complaint::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'ct_complainant',
            'ct_cov',
            'cs_ack'
        ];

        $this->references = [
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'id' => 99,
                'version' => 1,
                'case' => 24,
                "closedDate" => null,
                "complainantFamilyName" => "Anthony",
                "complainantForename" => "David",
                "complaintDate" => "2015-01-16",
                "closedDate" => "2015-02-26",
                "complaintType" => "ct_cov",
                "createdBy" => null,
                "description" => "Some major complaint about condition of vehicle",
                "driverFamilyName" => "Driver L Smith",
                "driverForename" => "Driver F John",
                "status" => "cs_ack",
                "vrm" => "VRM123T"
            ]
        );

        /** @var PersonEntity $person */
        $person = m::mock(PersonEntity::class)->makePartial();
        $person->setId(44);

        /** @var ContactDetailsEntity $complainantContactDetails */
        $complainantContactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $complainantContactDetails->setId(33);
        $complainantContactDetails->setPerson($person);

        /** @var ComplaintEntity $complaint */
        $complaint = m::mock(ComplaintEntity::class)->makePartial();
        $complaint->setId($command->getId());
        $complaint->setComplainantContactDetails($complainantContactDetails);
        $this->repoMap['Complaint']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->once()
            ->andReturn($complaint)
            ->shouldReceive('save')
            ->with(m::type(ComplaintEntity::class))
            ->once()
            ->andReturnUsing(
                function (ComplaintEntity $complaint) {
                    $this->assertEquals('ct_cov', (string) $complaint->getComplaintType());
                    $this->assertSame('cs_ack', (string) $complaint->getStatus());
                    $this->assertEquals(new DateTime('2015-01-16'), $complaint->getComplaintDate());
                    $this->assertEquals(new DateTime('2015-02-26'), $complaint->getClosedDate());
                    $this->assertSame('Some major complaint about condition of vehicle', $complaint->getDescription());
                    $this->assertSame('Driver L Smith', $complaint->getDriverFamilyName());
                    $this->assertSame('Driver F John', $complaint->getDriverForename());
                    $this->assertSame('VRM123T', $complaint->getVrm());
                    $this->assertSame('David', $complaint->getComplainantContactDetails()->getPerson()->getForename());
                    $this->assertSame(
                        'Anthony',
                        $complaint->getComplainantContactDetails()->getPerson()->getFamilyName()
                    );
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Complaint updated', $result->getMessages());
    }
}
