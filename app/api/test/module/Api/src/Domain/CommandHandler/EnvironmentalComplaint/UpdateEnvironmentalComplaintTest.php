<?php

declare(strict_types=1);

/**
 * Update Environmental Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\EnvironmentalComplaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\EnvironmentalComplaint\UpdateEnvironmentalComplaint;
use Dvsa\Olcs\Api\Domain\Repository\Complaint;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\UpdateEnvironmentalComplaint as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;

/**
 * Update Environmental Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateEnvironmentalComplaintTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateEnvironmentalComplaint();
        $this->mockRepo('Complaint', Complaint::class);
        $this->mockRepo('ContactDetails', ContactDetails::class)->makePartial();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ComplaintEntity::COMPLAIN_STATUS_OPEN,
            ContactDetailsEntity::CONTACT_TYPE_COMPLAINANT,
        ];

        $this->references = [
            Country::class => [
                'UK' => m::mock(Country::class)
            ],
            OperatingCentre::class => [
                101 => m::mock(OperatingCentre::class),
                102 => m::mock(OperatingCentre::class),
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 99,
            'version' => 1,
            "complaintDate" => "2015-01-16",
            "description" => "Some major complaint about condition of vehicle",
            "status" => ComplaintEntity::COMPLAIN_STATUS_OPEN,
            'operatingCentres' => [101, 102],
            'complainantContactDetails' => [
                'person' => [
                    'forename' => 'David',
                    'familyName' => 'Anthony',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
            ],
        ];

        $command = Cmd::create($data);

        /** @var ContactDetailsEntity $complainantContactDetails */
        $complainantContactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $complainantContactDetails->shouldReceive('update')
            ->once()
            ->with($data['complainantContactDetails'])
            ->andReturnSelf();

        /** @var ComplaintEntity $complaint */
        $complaint = m::mock(ComplaintEntity::class)->makePartial();
        $complaint->setId($command->getId());
        $complaint->setIsCompliance(false);
        $complaint->setComplainantContactDetails($complainantContactDetails);

        $this->repoMap['Complaint']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($complaint)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(ComplaintEntity::class))
            ->andReturnUsing(
                function (ComplaintEntity $complaint) {
                    $complaint->setId(99);
                }
            )
            ->once();

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['complainantContactDetails'])
            ->andReturn($data['complainantContactDetails']);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Environmental Complaint updated', $result->getMessages());
    }

    public function testHandleCommandNoExistingContactDetailsEntity(): void
    {
        $data = [
            'id' => 99,
            'version' => 1,
            "complaintDate" => "2015-01-16",
            "description" => "Some major complaint about condition of vehicle",
            "status" => ComplaintEntity::COMPLAIN_STATUS_OPEN,
            'operatingCentres' => [101, 102],
            'complainantContactDetails' => [
                'person' => [
                    'forename' => 'David',
                    'familyName' => 'Anthony',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => 'UK',
                ],
            ],
        ];

        $command = Cmd::create($data);

        /** @var ComplaintEntity $complaint */
        $complaint = m::mock(ComplaintEntity::class)->makePartial();
        $complaint->setId($command->getId());
        $complaint->setIsCompliance(false);

        $this->repoMap['Complaint']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->once()
            ->andReturn($complaint)
            ->shouldReceive('save')
            ->with(m::type(ComplaintEntity::class))
            ->once()
            ->andReturnUsing(
                function (ComplaintEntity $complaint) {
                    $complaint->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            ContactDetailsEntity::CONTACT_TYPE_COMPLAINANT,
            (string) $complaint->getComplainantContactDetails()->getContactType()
        );
        $this->assertEquals(new DateTime('2015-01-16'), $complaint->getComplaintDate());
        $this->assertEquals('Some major complaint about condition of vehicle', $complaint->getDescription());
        $this->assertSame(ComplaintEntity::COMPLAIN_STATUS_OPEN, (string) $complaint->getStatus());
        $this->assertCount(2, $complaint->getOperatingCentres());
        $this->assertSame($this->references[OperatingCentre::class][101], $complaint->getOperatingCentres()[0]);
        $this->assertSame($this->references[OperatingCentre::class][102], $complaint->getOperatingCentres()[1]);
        $this->assertSame('David', $complaint->getComplainantContactDetails()->getPerson()->getForename());
        $this->assertSame('Anthony', $complaint->getComplainantContactDetails()->getPerson()->getFamilyName());
        $this->assertSame('a12', $complaint->getComplainantContactDetails()->getAddress()->getAddressLine1());
        $this->assertSame('a23', $complaint->getComplainantContactDetails()->getAddress()->getAddressLine2());
        $this->assertSame('a34', $complaint->getComplainantContactDetails()->getAddress()->getAddressLine3());
        $this->assertSame('a45', $complaint->getComplainantContactDetails()->getAddress()->getAddressLine4());
        $this->assertSame('town', $complaint->getComplainantContactDetails()->getAddress()->getTown());
        $this->assertSame('LS1 2AB', $complaint->getComplainantContactDetails()->getAddress()->getPostcode());
        $this->assertSame(
            $this->references[Country::class]['UK'],
            $complaint->getComplainantContactDetails()->getAddress()->getCountryCode()
        );

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Environmental Complaint updated', $result->getMessages());
    }
}
