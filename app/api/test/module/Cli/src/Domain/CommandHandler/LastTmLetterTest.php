<?php

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Cli\Domain\CommandHandler\LastTmLetter;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Dvsa\Olcs\Transfer\Command\Task\CreateTask;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Laminas\Mail\Transport\Sendmail;

class LastTmLetterTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {

        $this->sut = new LastTmLetter();

        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('User', Repository\User::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('DocTemplate', Repository\DocTemplate::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('TransportManagerLicence', Repository\TransportManagerLicence::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class)
        ];

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody');
        parent::setUp();
    }

    public function dpHandleCommand()
    {
        $sideEffectResultsWithAllowEmail = [
            'GenerateAndStore' => [
                'ids' => [
                    'documents' => [
                        '123' => [
                            'metadata' => json_encode([
                                'details' => [
                                    'category' => Category::CATEGORY_TRANSPORT_MANAGER,
                                    'documentSubCategory' => Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE,
                                    'documentTemplate' => 1,
                                    'allowEmail' => 'Y',
                                    'sendToAddress' => 'correspondenceAddress'
                                ]
                            ]),
                            'address' => 'correspondenceAddress'
                        ],
                    ]
                ]
            ],
            'CreateTask' => [
                'ids' => [
                    'assignedToUser' => 111
                ]

            ]
        ];

        return [
            'no_licences_with_removed_tm' => [
                'data' => [
                    'licence' => []
                ],
                'expect' => [
                    'id' => [],
                    'messages' => []
                ]
            ],
            'licence_with_removed_tm_allow_email_gb_gv' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'organisation' => [
                            'allowEmail' => 'Y'
                        ],
                        'correspondenceCd' => null
                    ],
                    'user' => [
                        'contactDetails ' => [
                            'address' => '12 Food Road'
                        ]
                    ],
                    'sideEffectResults' => $sideEffectResultsWithAllowEmail

                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                        'correspondenceAddress' => '123',
                    ],
                    'messages' => [
                        "Document id '123', queued for print",
                        "Correspondence record created",
                        "Email sent"
                    ]
                ]
            ],
            'licence_with_removed_tm_correspondenceCd_email_null' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'organisation' => [
                            'allowEmail' => 'Y'
                        ],
                        'correspondenceCd' => [
                            'emailAddress' => null
                        ]
                    ],
                    'user' => [
                        'contactDetails ' => [
                            'address' => '12 Food Road'
                        ]
                    ],
                    'sideEffectResults' => $sideEffectResultsWithAllowEmail

                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                        'correspondenceAddress' => '123',
                    ],
                    'messages' => [
                        "Document id '123', queued for print",
                        "Correspondence record created",
                        "Email sent",
                    ]
                ]
            ],
            'licence_with_removed_tm_correspondenceCd_with_email_not_existing_user' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'organisation' => [
                            'allowEmail' => 'Y'
                        ],
                        'correspondenceCd' => [
                            'emailAddress' => 'test@email.com'
                        ]
                    ],
                    'user' => [
                        'contactDetails ' => [
                            'address' => '12 Food Road'
                        ],
                        'fetchFirstByEmailOrFalse' => false
                    ],
                    'sideEffectResults' => $sideEffectResultsWithAllowEmail

                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                        'correspondenceAddress' => '123',
                    ],
                    'messages' => [
                        "Document id '123', queued for print",
                        "Correspondence record created",
                        "Email sent",]
                ]
            ],
            'licence_with_removed_tm_correspondenceCd_with_email_existing_user' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => false,
                        'organisation' => [
                            'allowEmail' => 'Y'
                        ],
                        'correspondenceCd' => [
                            'emailAddress' => 'test@email.com'
                        ]
                    ],
                    'user' => [
                        'contactDetails ' => [
                            'address' => '12 Food Road'
                        ],
                        'fetchFirstByEmailOrFalse' => m::mock(UserEntity::class)
                    ],
                    'sideEffectResults' => $sideEffectResultsWithAllowEmail

                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                        'correspondenceAddress' => '123',
                    ],
                    'messages' => [
                        "Document id '123', queued for print",
                        "Correspondence record created",
                        "Email sent"
                    ]
                ]
            ],
            'licence_with_removed_tm_allow_email_ni_gv' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => true,
                        'isPsv' => false,
                        'organisation' => [
                            'allowEmail' => 'Y'
                        ],
                        'correspondenceCd' => null
                    ],
                    'user' => [
                        'contactDetails ' => [
                            'address' => '12 Food Road'
                        ]
                    ],
                    'sideEffectResults' => $sideEffectResultsWithAllowEmail

                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                        'correspondenceAddress' => '123',
                    ],
                    'messages' => [
                        "Document id '123', queued for print",
                        "Correspondence record created",
                        "Email sent",
                    ]
                ]
            ],
            'licence_with_removed_tm_not_allow_email_gb_psv' => [
                'data' => [
                    'licence' => [
                        'id' => 1,
                        'licNo' => 'AB123',
                        'isNi' => false,
                        'isPsv' => true,
                        'organisation' => [
                            'allowEmail' => 'N'
                        ],
                        'correspondenceCd' => null
                    ],
                    'user' => [
                        'contactDetails ' => [
                            'address' => '12 Food Road'
                        ]
                    ],
                    'sideEffectResults' => [
                        'GenerateAndStore' => [
                            'ids' => [
                                'documents' => [
                                    '123' => [
                                        'metadata' => json_encode([
                                            'details' => [
                                                'category' => Category::CATEGORY_TRANSPORT_MANAGER,
                                                'documentSubCategory' => Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE,
                                                'documentTemplate' => 1,
                                                'allowEmail' => 'N',
                                                'sendToAddress' => 'correspondenceAddress'
                                            ]
                                        ]),
                                        'address' => 'correspondenceAddress'
                                    ],
                                ]
                            ]
                        ],
                        'CreateTask' => [
                            'ids' => [
                                'assignedToUser' => 111
                            ]

                        ]
                    ]

                ],
                'expect' => [
                    'id' => [
                        'assignedToUser' => 111,
                        'document' => 123,
                        'correspondenceAddress' => '123',
                    ],
                    'messages' => [
                        "Document id '123', queued for print",
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider dpHandleCommand
     */
    public function testHandleCommand($dataProvider, $expectedResult)
    {
        if (array_key_exists('isNi', $dataProvider['licence'])) {
            $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
                ->with($dataProvider['licence']['isNi'] ? SystemParameter::LAST_TM_NI_TASK_OWNER : SystemParameter::LAST_TM_GB_TASK_OWNER)
                ->andReturn(1)
                ->once();
        }

        $licenceRepo = $this->repoMap['Licence'];

        $licence = empty($dataProvider['licence']) ? null : m::mock(LicenceEntity::class);

        if ($licence !== null) {
            $this->mockLicence($licence, $dataProvider);
        }

        $eligibleLicences = $licence === null ? [] : [$licence];

        $licenceRepo->shouldReceive('fetchForLastTmAutoLetter')->andReturn($eligibleLicences);

        if (!empty($eligibleLicences)) {
            $this->mockCorrespondenceCd($licence, $dataProvider);
            $this->caseLicenceWithRemovedTmTest($dataProvider, $eligibleLicences);
        }

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\LastTmLetter::create([]));

        $this->assertEquals($expectedResult, $response->toArray());
    }

    public function mockCorrespondenceCd(m\MockInterface $licence, $dataProvider)
    {
        $correspondenceCdWithNullEmail = m::mock(ContactDetails::class);
        $correspondenceCdWithNullEmail->shouldReceive('getEmailAddress')->andReturn(null);
        $correspondenceCdWithNullEmail->shouldReceive('getId')->andReturn(1);
        $correspondenceCdWithEmail = m::mock(ContactDetails::class);
        $correspondenceCdWithEmail->shouldReceive('getEmailAddress')->andReturn("test@email.com");
        $correspondenceCdWithEmail->shouldReceive('getId')->andReturn(1);

        $correspondenceCd = $dataProvider['licence']['correspondenceCd'];

        $mockCorrespondenceCd = $correspondenceCd;
        if ($correspondenceCd !== null && $correspondenceCd['emailAddress'] === null) {
            $mockCorrespondenceCd = m::mock(ContactDetails::class);
            $mockCorrespondenceCd->shouldReceive('getEmailAddress')->andReturn(null);
            $mockCorrespondenceCd->shouldReceive('getId')->andReturn(1);
        } elseif ($correspondenceCd !== null && $correspondenceCd['emailAddress'] !== null) {
            $mockCorrespondenceCd = m::mock(ContactDetails::class);
            $mockCorrespondenceCd->shouldReceive('getEmailAddress')->andReturn($correspondenceCd['emailAddress']);
            $mockCorrespondenceCd->shouldReceive('getId')->andReturn(1);
            $licence->shouldReceive('getTranslateToWelsh')->andReturn('N');
        }

        $licence->shouldReceive('getCorrespondenceCd')->andReturn($mockCorrespondenceCd);
    }

    private function getGenerateAndStoreResult($documents)
    {
        $result = new Result();

        foreach ($documents as $id => $data) {
            $result->addId($data['address'], $id);
            $result->addId('document', $id);
        }

        return $result;
    }

    private function getCreateTaskResult($dataProvider)
    {
        $result = new Result();
        $result->addId('assignedToUser', $dataProvider['sideEffectResults']['CreateTask']['ids']['assignedToUser']);

        return $result;
    }

    private function getPrintLetterResult($documentId)
    {
        $result = new Result();
        $result->addMessage("Document id '$documentId', queued for print");
        return $result;
    }

    private function getPrintLetterEmailResult()
    {
        $result = new Result();
        $result->addMessage('Correspondence record created');
        $result->addMessage('Email sent');

        return $result;
    }

    private function mockUser()
    {
        $caseworkerDetailsBundle = [
            'contactDetails' => [
                'address',
                'phoneContacts' => [
                    'phoneContactType'
                ],
                'person'
            ],
            'team' => [
                'trafficArea' => [
                    'contactDetails' => [
                        'address'
                    ]
                ]
            ]
        ];

        $caseworkerNameBundle = [
            'contactDetails' => [
                'person'
            ]
        ];

        $user = m::mock(UserEntity::class);
        $user->shouldReceive('serialize')
            ->with($caseworkerDetailsBundle)
            ->once()
            ->andReturn([]);
        $user->shouldReceive('serialize')
            ->with($caseworkerNameBundle)
            ->once()
            ->andReturn([]);

        return $user;
    }

    private function mockLicence(m\MockInterface $licence, $dataProvider)
    {

        $licenceBundle = [
            'trafficArea',
        ];

        $licence->shouldReceive('getId')->andReturn($dataProvider['licence']['id']);
        $licence->shouldReceive('getLicNo')->andReturn($dataProvider['licence']['licNo']);
        $licence->shouldReceive('isNi')->andReturn($dataProvider['licence']['isNi']);
        $licence->shouldReceive('isPsv')->andReturn($dataProvider['licence']['isPsv']);
        $licence->shouldReceive('getOrganisation->getAllowEmail')
            ->andReturn($dataProvider['licence']['organisation']['allowEmail']);
        $licence->shouldReceive('getOrganisation->getId')->andReturn(1);
        $licence->shouldReceive('getOrganisation->getName')->andReturn('Test Organisation');
        $licence->shouldReceive('serialize')->with($licenceBundle)->andReturn([]);

        return $licence;
    }

    /**
     * @param $dataProvider
     * @param $eligibleLicences
     */
    private function caseLicenceWithRemovedTmTest($dataProvider, $eligibleLicences): void
    {
        $this->mockUserRepo($dataProvider);

        $tmlRepo = $this->repoMap['TransportManagerLicence'];
        foreach ($eligibleLicences as $eligibleLicence) {
            $tmlEntity = m::mock(TransportManagerLicence::class);
            $tmlEntity->shouldReceive('setLastTmLetterDate');
            $tmlRepo
                ->shouldReceive('fetchRemovedTmForLicence')
                ->with($eligibleLicence->getId())
                ->andReturn([$tmlEntity]);
            $tmlRepo->shouldReceive('save');
        }

        $documentsData = $dataProvider['sideEffectResults']['GenerateAndStore']['ids']['documents'];

        $generateAndStoreResult = $this->getGenerateAndStoreResult($documentsData);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [],
            $generateAndStoreResult
        );

        $createTaskResult = $this->getCreateTaskResult($dataProvider);
        $this->expectedSideEffect(CreateTask::class, [], $createTaskResult);

        $documents = [];
        $documentRepo = $this->repoMap['Document'];
        foreach ($documentsData as $id => $data) {
            $printLetterResult = $this->getPrintLetterResult($id);
            $this->expectedSideEffect(PrintLetter::class, [], $printLetterResult);

            $metadata = json_decode((string) $data['metadata'], true);
            if (
                $metadata['details']['sendToAddress'] === 'correspondenceAddress' &&
                $metadata['details']['allowEmail'] === 'Y'
            ) {
                $printLetterEmailResult = $this->getPrintLetterEmailResult();
                $this->expectedSideEffect(PrintLetter::class, [], $printLetterEmailResult);
            }

            $documents[$id] = m::mock(DocumentEntity::class);
            $documents[$id]->shouldReceive('getMetadata')->andReturn($data['metadata']);

            $documentRepo->shouldReceive('fetchById')->with($id)->once()->andReturn($documents[$id]);
        }
    }

    /**
     * @param $dataProvider
     */
    private function mockUserRepo($dataProvider): void
    {
        $userRepo = $this->repoMap['User'];
        $user = $this->mockUser();
        $userRepo->shouldReceive('fetchById')
            ->with($dataProvider['sideEffectResults']['CreateTask']['ids']['assignedToUser'])
            ->andReturn($user);

        if (array_key_exists('fetchFirstByEmailOrFalse', $dataProvider['user'])) {
            $fetchedUser = $dataProvider['user']['fetchFirstByEmailOrFalse'];
            if ($fetchedUser) {
                $fetchedUser->shouldReceive('getTranslateToWelsh')->andReturn('N');
            }
            $userRepo->shouldReceive('fetchFirstByEmailOrFalse')->andReturn($fetchedUser);
            $this->expectedSideEffect(SendEmail::class, [], new Result());
        }
    }
}
