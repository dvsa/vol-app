<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\UpdateSubmission;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmission as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TmApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TmLicenceRepo;

class UpdateSubmissionTest extends AbstractCommandHandlerTestCase
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
        $this->sut = new UpdateSubmission();
        $this->mockRepo('Submission', SubmissionRepo::class);
        $this->mockRepo('TransportManagerApplication', TmApplicationRepo::class);
        $this->mockRepo('TransportManagerLicence', TmLicenceRepo::class);
        $this->mockRepo('User', \Dvsa\Olcs\Api\Domain\Repository\User::class);

        $mockSubmissionGenerator = m::mock(SubmissionGenerator::class);
        $mockSubmissionGenerator
            ->shouldReceive('generateSubmission')
            ->andReturn(m::mock(SubmissionEntity::class)->shouldReceive('getId')->andReturn(111)->getMock());

        $this->mockedSmServices = [
            'config' => $this->submissionConfig,
            SubmissionGenerator::class => $mockSubmissionGenerator,
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

        $mockSubmissionEntity = m::mock(SubmissionEntity::class);
        $mockSubmissionEntity->shouldReceive('setSubmissionType')->once()->getMock();
        $this->repoMap['Submission']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->andReturn($mockSubmissionEntity);
        $this->repoMap['Submission']
            ->shouldReceive('save')
            ->once();

        $command = Cmd::create($data);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => 111,
            ],
            'messages' => [
                'Submission updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
