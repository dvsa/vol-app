<?php

declare(strict_types=1);

/**
 * Create Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\EnvironmentalComplaint;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\EnvironmentalComplaint\CreateEnvironmentalComplaint;
use Dvsa\Olcs\Api\Domain\Repository\Complaint;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\Cases;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\CreateEnvironmentalComplaint as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Environmental Complaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateEnvironmentalComplaintTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateEnvironmentalComplaint();
        $this->mockRepo('Complaint', Complaint::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);
        $this->mockRepo('Cases', Cases::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

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
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $userId = 1;

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId($userId);
        $mockUser->setTeam($mockTeam);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $data = [
            'case' => 24,
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

        /** @var ComplaintEntity $app */
        $comp = null;

        $this->repoMap['Complaint']
            ->shouldReceive('save')
            ->with(m::type(ComplaintEntity::class))
            ->andReturnUsing(
                function (ComplaintEntity $complaint) use (&$comp) {
                    $comp = $complaint;
                    $complaint->setId(99);
                }
            )
            ->once();

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['complainantContactDetails'])
            ->andReturn($data['complainantContactDetails']);

        /** @var CasesEntity $case */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(999);
        $licence->setReviewDate(new \DateTime('2015-02-10'));

        /** @var CasesEntity $case */
        $case = m::mock(CasesEntity::class)->makePartial();
        $case->setId($command->getCase());
        $case->setLicence($licence);

        $this->repoMap['Cases']->shouldReceive('fetchById')
            ->with($command->getCase(), Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($case);

        $result1 = new Result();
        $result1->addId('task', 333);
        $taskData = [
            'category' => Task::CATEGORY_ENVIRONMENTAL,
            'subCategory' => Task::SUBCATEGORY_REVIEW_COMPLAINT,
            'description' => 'Review complaint',
            'actionDate' => new \DateTime('2015-02-10'),
            'assignedToUser' => 1,
            'assignedToTeam' => 2,
            'isClosed' => false,
            'urgent' => false,
            'case' => 24,
            'application' => null,
            'licence' => null,
            'busReg' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];
        $this->expectedSideEffect(CreateTask::class, $taskData, $result1);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Environmental Complaint created', $result->getMessages());
    }
}
