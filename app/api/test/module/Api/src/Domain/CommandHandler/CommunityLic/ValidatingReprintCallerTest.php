<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateCoverLetter as GenerateCoverLetterCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\ValidatingReprintCaller;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Reprint as ReprintCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use RuntimeException;

class ValidatingReprintCallerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ValidatingReprintCaller();

        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $licenceId = 71;
        $userId = 491;

        $communityLicences = [
            [
                'communityLicenceId' => 53,
                'communityLicenceIssueNo' => 7,
            ],
            [
                'communityLicenceId' => 87,
                'communityLicenceIssueNo' => 9,
            ]
        ];

        $licence71 = m::mock(Licence::class);
        $licence71->shouldReceive('getId')
            ->andReturn(71);
        $licence71->shouldReceive('hasStatusRequiredForCommunityLicenceReprint')
            ->andReturn(true);

        $communityLicence53 = m::mock(CommunityLic::class);
        $communityLicence53->shouldReceive('getIssueNo')
            ->andReturn(7);
        $communityLicence53->shouldReceive('isActive')
            ->andReturn(true);
        $communityLicence53->shouldReceive('getLicence')
            ->andReturn($licence71);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(53)
            ->andReturn($communityLicence53);

        $communityLicence87 = m::mock(CommunityLic::class);
        $communityLicence87->shouldReceive('getIssueNo')
            ->andReturn(9);
        $communityLicence87->shouldReceive('isActive')
            ->andReturn(true);
        $communityLicence87->shouldReceive('getLicence')
            ->andReturn($licence71);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(87)
            ->andReturn($communityLicence87);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getLicence')
            ->andReturn($licenceId);
        $command->shouldReceive('getCommunityLicences')
            ->andReturn($communityLicences);
        $command->shouldReceive('getUser')
            ->andReturn($userId);

        $this->expectedSideEffect(
            ReprintCmd::class,
            [
                'isBatchReprint' => true,
                'user' => $userId,
                'licence' => $licenceId,
                'communityLicenceIds' => [53, 87]
            ],
            new Result()
        );

        $this->expectedSideEffect(
            GenerateCoverLetterCmd::class,
            [
                'user' => $userId,
                'licence' => $licenceId,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }

    public function testSomeValidCommunityLicences(): void
    {
        $licenceId = 42;
        $userId = 612;

        $communityLicences = [
            [
                'communityLicenceId' => 42,
                'communityLicenceIssueNo' => 3,
            ],
            [
                'communityLicenceId' => 99,
                'communityLicenceIssueNo' => 11,
            ],
            [
                'communityLicenceId' => 76,
                'communityLicenceIssueNo' => 1,
            ],
            [
                'communityLicenceId' => 72,
                'communityLicenceIssueNo' => 2,
            ],
            [
                'communityLicenceId' => 101,
                'communityLicenceIssueNo' => 4,
            ],
            [
                'communityLicenceId' => 109,
                'communityLicenceIssueNo' => 7,
            ],
            [
                'communityLicenceId' => 131,
                'communityLicenceIssueNo' => 9,
            ],
        ];

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(42)
            ->andThrow(NotFoundException::class, 'Not found');

        $communityLicence99 = m::mock(CommunityLic::class);
        $communityLicence99->shouldReceive('getIssueNo')
            ->andReturn(13);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($communityLicence99);

        $communityLicence76 = m::mock(CommunityLic::class);
        $communityLicence76->shouldReceive('getIssueNo')
            ->andReturn(1);
        $communityLicence76->shouldReceive('isActive')
            ->andReturn(false);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(76)
            ->andReturn($communityLicence76);

        $licence57 = m::mock(Licence::class);
        $licence57->shouldReceive('getId')
            ->andReturn(57);

        $communityLicence72 = m::mock(CommunityLic::class);
        $communityLicence72->shouldReceive('getIssueNo')
            ->andReturn(2);
        $communityLicence72->shouldReceive('isActive')
            ->andReturn(true);
        $communityLicence72->shouldReceive('getLicence')
            ->andReturn($licence57);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(72)
            ->andReturn($communityLicence72);

        $licence42IncorrectStatus = m::mock(Licence::class);
        $licence42IncorrectStatus->shouldReceive('getId')
            ->andReturn(42);
        $licence42IncorrectStatus->shouldReceive('hasStatusRequiredForCommunityLicenceReprint')
            ->andReturn(false);
        $licence42IncorrectStatus->shouldReceive('getStatus->getId')
            ->andReturn(Licence::LICENCE_STATUS_NOT_TAKEN_UP);

        $communityLicence101 = m::mock(CommunityLic::class);
        $communityLicence101->shouldReceive('getIssueNo')
            ->andReturn(4);
        $communityLicence101->shouldReceive('isActive')
            ->andReturn(true);
        $communityLicence101->shouldReceive('getLicence')
            ->andReturn($licence42IncorrectStatus);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(101)
            ->andReturn($communityLicence101);

        $licence42CorrectStatus = m::mock(Licence::class);
        $licence42CorrectStatus->shouldReceive('getId')
            ->andReturn(42);
        $licence42CorrectStatus->shouldReceive('hasStatusRequiredForCommunityLicenceReprint')
            ->andReturn(true);

        $communityLicence109 = m::mock(CommunityLic::class);
        $communityLicence109->shouldReceive('getIssueNo')
            ->andReturn(7);
        $communityLicence109->shouldReceive('isActive')
            ->andReturn(true);
        $communityLicence109->shouldReceive('getLicence')
            ->andReturn($licence42CorrectStatus);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(109)
            ->andReturn($communityLicence109);

        $communityLicence131 = m::mock(CommunityLic::class);
        $communityLicence131->shouldReceive('getIssueNo')
            ->andReturn(9);
        $communityLicence131->shouldReceive('isActive')
            ->andReturn(true);
        $communityLicence131->shouldReceive('getLicence')
            ->andReturn($licence42CorrectStatus);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(131)
            ->andReturn($communityLicence131);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getLicence')
            ->andReturn($licenceId);
        $command->shouldReceive('getCommunityLicences')
            ->andReturn($communityLicences);
        $command->shouldReceive('getUser')
            ->andReturn($userId);

        $this->expectedSideEffectThrowsException(
            ReprintCmd::class,
            [
                'isBatchReprint' => true,
                'user' => $userId,
                'licence' => $licenceId,
                'communityLicenceIds' => [109, 131]
            ],
            new RuntimeException('Something went wrong')
        );

        $this->expectedSideEffectThrowsException(
            GenerateCoverLetterCmd::class,
            [
                'user' => $userId,
                'licence' => $licenceId,
            ],
            new RuntimeException('Something failed')
        );

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'No community licence exists with id 42',
            'Community licence with id 99 exists but has an issue number of 13 instead of the expected 11',
            'Community licence with id 76 exists but is not active',
            'Licence id 57 associated with community licence id 72 does not match expected value of 42',
            'Licence id 42 associated with community licence id 101 does not have the correct status (currently lsts_ntu)',
            'Error calling Reprint command with licence id 42 and community licence ids 109, 131: Something went wrong',
            'Error calling GenerateCoverLetter command with licence id 42: Something failed',
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }

    public function testNoValidCommunityLicencesNoReprint(): void
    {
        $licenceId = 67;
        $userId = 155;

        $communityLicences = [
            [
                'communityLicenceId' => 53,
                'communityLicenceIssueNo' => 1,
            ],
            [
                'communityLicenceId' => 11,
                'communityLicenceIssueNo' => 2,
            ],
        ];

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(53)
            ->andThrow(NotFoundException::class, 'Not found');

        $communityLicence11 = m::mock(CommunityLic::class);
        $communityLicence11->shouldReceive('getIssueNo')
            ->andReturn(3);

        $this->repoMap['CommunityLic']->shouldReceive('fetchById')
            ->with(11)
            ->andReturn($communityLicence11);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getLicence')
            ->andReturn($licenceId);
        $command->shouldReceive('getCommunityLicences')
            ->andReturn($communityLicences);
        $command->shouldReceive('getUser')
            ->andReturn($userId);

        $this->expectedSideEffect(
            GenerateCoverLetterCmd::class,
            [
                'user' => $userId,
                'licence' => $licenceId,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'No community licence exists with id 53',
            'Community licence with id 11 exists but has an issue number of 3 instead of the expected 2',
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }
}
