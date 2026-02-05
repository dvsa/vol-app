<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\CreateSubmission;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmission as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as CommentCommand;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TmApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TmLicenceRepo;

class CreateSubmissionTest extends AbstractCommandHandlerTestCase
{
    public $submissionConfig = [
        'submissions' => [
            'sections' => [
                'configuration' => [
                    'introduction' => [
                        'subcategoryId' => 115,
                        'config' => [],
                        'section_type' => ['text'],
                        'allow_comments' => true,
                        'allow_attachments' => true
                    ]
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        $this->sut = new CreateSubmission();
        $this->mockRepo('Submission', SubmissionRepo::class);
        $this->mockRepo('TransportManagerApplication', TmApplicationRepo::class);
        $this->mockRepo('TransportManagerLicence', TmLicenceRepo::class);
        $this->mockRepo('User', \Dvsa\Olcs\Api\Domain\Repository\User::class);

        $this->mockedSmServices = [
            'config' => $this->submissionConfig,
            SubmissionGenerator::class => m::mock(SubmissionGenerator::class),
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
            IdentityProviderInterface::class => m::mock(IdentityProviderInterface::class)
        ];

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'case-summary',
            'submission_type_o_mlh_otc'
        ];

        $this->references = [
            SubmissionEntity::class => [
                1 => m::mock(SubmissionEntity::class)
            ],
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'case' => '24',
            'submissionType' => 'submission_type_o_mlh_otc',
            'sections' => [
                'introduction'
            ]
        ];

        $submissionMock = m::mock(SubmissionEntity::class)->makePartial();

        $command = Cmd::create($data);
        $this->mockedSmServices[SubmissionGenerator::class]->shouldReceive('generateSubmission')->once()
            ->andReturnUsing(
                function (
                    SubmissionEntity $submission
                ) use (&$submissionMock) {
                    $submission->setId(111);
                    $submission->setSectionData(
                        'introduction',
                        [
                            'data' => [
                                'text' => 'test comment'
                            ]
                        ]
                    );
                    $submissionMock = $submission;
                    return $submissionMock;
                }
            );

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (
                    SubmissionEntity $submission
                ) use (&$savedSubmission) {
                    $submission->setId(111);
                }
            );

        $this->expectedSideEffect(
            CommentCommand::class,
            [
                'id' => '',
                'submission' => 111,
                'submissionSection' => 'introduction',
                'comment' => 'test comment',
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => 111,
            ],
            'messages' => [
                'Submission created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
