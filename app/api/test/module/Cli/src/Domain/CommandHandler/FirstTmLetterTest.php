<?php

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Cli\Domain\CommandHandler\FirstTmLetter;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Transfer\Command\Task\CreateTask;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class FirstTmLetterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FirstTmLetter();

        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('User', Repository\User::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('DocTemplate', Repository\DocTemplate::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('TransportManagerLicence', Repository\TransportManagerLicence::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody');

        parent::setUp();
    }

    public function dpHandleCommand()
    {
        $sideEffectResults = [
            'GenerateAndStore' => [
                'ids' => [
                    'documents' => [
                        '123' => [
                            'metadata' => json_encode([
                                'details' => [
                                    'category' => Category::CATEGORY_TRANSPORT_MANAGER,
                                    'documentSubCategory' => Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE,
                                    'documentTemplate' => 1285,
                                    'allowEmail' => 'Y',
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
            'CreateTask' => [
                'ids' => [
                    'assignedToUser' => 111,
                ],
            ],
        ];

        return [
            'no_licences' => [
                'data' => [
                    'licence' => [],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [],
                    'messages' => [],
                ],
            ],

            'licence_with_removed_tm_no_correspondence_cd' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'translateToWelsh' => 'N',
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'name' => 'Test Operator Ltd',
                        ],
                        'correspondenceCd' => null,
                    ],
                    'user' => [],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                    ],
                    'messages' => [],
                ],
            ],

            'licence_with_removed_tm_correspondence_cd_email_null' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'translateToWelsh' => 'N',
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'name' => 'Test Operator Ltd',
                        ],
                        'correspondenceCd' => [
                            'emailAddress' => null,
                        ],
                    ],
                    'user' => [],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                    ],
                    'messages' => [],
                ],
            ],

            'licence_with_removed_tm_email_not_registered_user' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'translateToWelsh' => 'N',
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'name' => 'Test Operator Ltd',
                        ],
                        'correspondenceCd' => [
                            'emailAddress' => 'test@email.com',
                        ],
                    ],
                    'user' => [
                        'fetchFirstByEmailOrFalse' => false,
                    ],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                    ],
                    'messages' => [],
                ],
            ],

            'licence_with_removed_tm_email_registered_user' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'translateToWelsh' => 'N',
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'name' => 'Test Operator Ltd',
                        ],
                        'correspondenceCd' => [
                            'emailAddress' => 'test@email.com',
                        ],
                    ],
                    'user' => [
                        'fetchFirstByEmailOrFalse' => m::mock(UserEntity::class),
                    ],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                    ],
                    'messages' => [],
                ],
            ],

            'licence_ni_template_path' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => true,
                        'isPsv' => false,
                        'translateToWelsh' => 'N',
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'name' => 'Test Operator Ltd',
                        ],
                        'correspondenceCd' => null,
                    ],
                    'user' => [],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                    ],
                    'messages' => [],
                ],
            ],

            'licence_psv_template_path' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => true,
                        'translateToWelsh' => 'N',
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'name' => 'Test Operator Ltd',
                        ],
                        'correspondenceCd' => null,
                    ],
                    'user' => [],
                    'sideEffectResults' => $sideEffectResults,
                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                    ],
                    'messages' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dpHandleCommand
     */
    public function testHandleCommand($dataProvider, $expectedResult)
    {
        // SystemParameter selection differs for NI/GB
        if (!empty($dataProvider['licence']) && array_key_exists('isNi', $dataProvider['licence'])) {
            $this->repoMap['SystemParameter']
                ->shouldReceive('fetchValue')
                ->with(
                    $dataProvider['licence']['isNi']
                        ? SystemParameter::LAST_TM_1st_LETTER_NI_TASK_OWNER
                        : SystemParameter::LAST_TM_1st_LETTER_GB_TASK_OWNER
                )
                ->andReturn(1)
                ->once();
        }

        $licenceRepo = $this->repoMap['Licence'];

        $licence = empty($dataProvider['licence']) ? null : m::mock(LicenceEntity::class);

        if ($licence !== null) {
            $this->mockLicence($licence, $dataProvider);
            $this->mockCorrespondenceCd($licence, $dataProvider);
        }

        $eligibleLicences = $licence === null ? [] : [$licence];

        // NOTE: FirstTmLetter uses LETTER_FIRST
        $licenceRepo->shouldReceive('fetchForLastTmAutoLetter')->andReturn($eligibleLicences);

        if (!empty($eligibleLicences)) {
            $this->caseLicenceWithRemovedTmTest($dataProvider, $eligibleLicences);
        }

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\FirstTmLetter::create([]));

        $this->assertEquals($expectedResult, $response->toArray());
    }

    private function mockLicence(m\MockInterface $licence, array $dataProvider): void
    {
        $licenceBundle = ['trafficArea'];

        $licence->shouldReceive('getId')->andReturn($dataProvider['licence']['id']);
        $licence->shouldReceive('getLicNo')->andReturn($dataProvider['licence']['licNo']);
        $licence->shouldReceive('isNi')->andReturn($dataProvider['licence']['isNi']);
        $licence->shouldReceive('isPsv')->andReturn($dataProvider['licence']['isPsv']);
        $licence->shouldReceive('getTranslateToWelsh')->andReturn($dataProvider['licence']['translateToWelsh']);

        // organisation chain used in both generateDocuments + email
        $licence->shouldReceive('getOrganisation->getAllowEmail')
            ->andReturn($dataProvider['licence']['organisation']['allowEmail']);
        $licence->shouldReceive('getOrganisation->getName')
            ->andReturn($dataProvider['licence']['organisation']['name']);

        $licence->shouldReceive('serialize')->with($licenceBundle)->andReturn([]);
    }

    private function mockCorrespondenceCd(m\MockInterface $licence, array $dataProvider): void
    {
        $correspondenceCd = $dataProvider['licence']['correspondenceCd'] ?? null;

        if ($correspondenceCd === null) {
            $licence->shouldReceive('getCorrespondenceCd')->andReturn(null);
            return;
        }

        $mockCd = m::mock(ContactDetails::class);
        $mockCd->shouldReceive('getEmailAddress')->andReturn($correspondenceCd['emailAddress']);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($mockCd);
    }

    private function mockUserForDocGeneration(): UserEntity
    {
        $caseworkerDetailsBundle = [
            'contactDetails' => [
                'address',
                'phoneContacts' => [
                    'phoneContactType',
                ],
                'person',
            ],
            'team' => [
                'trafficArea' => [
                    'contactDetails' => [
                        'address',
                    ],
                ],
            ],
        ];

        $caseworkerNameBundle = [
            'contactDetails' => [
                'person',
            ],
        ];

        $user = m::mock(UserEntity::class);
        $user->shouldReceive('serialize')->with($caseworkerDetailsBundle)->once()->andReturn([]);
        $user->shouldReceive('serialize')->with($caseworkerNameBundle)->once()->andReturn([]);

        return $user;
    }

    /**
     * Sets up removed TM mocks + expected side effects:
     * - CreateTask
     * - GenerateAndStore
     * - updateLastTmFirstEmailDate() -> setLastTmFirstEmailDate + save()
     * - optional SendEmail side effect when correspondence email present (tests where user array includes fetchFirstByEmailOrFalse)
     */
    private function caseLicenceWithRemovedTmTest(array $dataProvider, array $eligibleLicences): void
    {
        $this->mockUserRepo($dataProvider);

        $tmlRepo = $this->repoMap['TransportManagerLicence'];

        foreach ($eligibleLicences as $eligibleLicence) {
            $tmlEntity = m::mock(TransportManagerLicence::class);
            $tmlEntity->shouldReceive('getId')->andReturn(5);

            $tm = m::mock(TransportManager::class);
            $tm->shouldReceive('getId')->andReturn(1);
            $tmlEntity->shouldReceive('getTransportManager')->andReturn($tm);

            $tmlEntity->shouldReceive('setLastTmFirstEmailDate')->once();
            $tmlRepo->shouldReceive('save')->with($tmlEntity)->once();

            $tmlRepo->shouldReceive('fetchRemovedTmForLicence')
                ->with($eligibleLicence->getId())
                ->andReturn([$tmlEntity]);
            $tmlRepo->shouldReceive('fetchRemovedTmForLicence')
                ->with($eligibleLicence->getId())
                ->andReturn([$tmlEntity]);
        }

        $documentsData = $dataProvider['sideEffectResults']['GenerateAndStore']['ids']['documents'];
        $generateAndStoreResult = $this->getGenerateAndStoreResult($documentsData);

        $this->expectedSideEffect(GenerateAndStore::class, [], $generateAndStoreResult);

        $createTaskResult = $this->getCreateTaskResult($dataProvider);
        $this->expectedSideEffect(CreateTask::class, [], $createTaskResult);
    }

    private function mockUserRepo(array $dataProvider): void
    {
        $userRepo = $this->repoMap['User'];

        $userRepo->shouldReceive('fetchById')
            ->with($dataProvider['sideEffectResults']['CreateTask']['ids']['assignedToUser'])
            ->andReturn($this->mockUserForDocGeneration());

        if (array_key_exists('fetchFirstByEmailOrFalse', $dataProvider['user'])) {
            $fetchedUser = $dataProvider['user']['fetchFirstByEmailOrFalse'];

            if ($fetchedUser instanceof m\MockInterface) {
                $fetchedUser->shouldReceive('getTranslateToWelsh')->andReturn('N');
            }

            $userRepo->shouldReceive('fetchFirstByEmailOrFalse')->andReturn($fetchedUser);

            $this->expectedSideEffect(SendEmail::class, [], new Result());
        }
    }

    private function getGenerateAndStoreResult(array $documents): Result
    {
        $result = new Result();
        foreach ($documents as $id => $data) {
            $result->addId('document', $id);
        }
        return $result;
    }

    private function getCreateTaskResult(array $dataProvider): Result
    {
        $result = new Result();
        $result->addId('assignedToUser', $dataProvider['sideEffectResults']['CreateTask']['ids']['assignedToUser']);
        return $result;
    }
}