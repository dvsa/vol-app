<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PrintScheduler;

use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\PrintScheduler\Enqueue as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * EnqueueTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class EnqueueTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Document', \Dvsa\Olcs\Api\Domain\Repository\Document::class);
        $this->mockRepo('User', \Dvsa\Olcs\Api\Domain\Repository\User::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandMissingDocumentsParam')]
    public function testHandleCommandMissingDocumentsParam(mixed $params): void
    {
        $command = Cmd::create($params);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('List of documents to be printed must be provided.');
        $this->sut->handleCommand($command);
    }

    public static function dpHandleCommandMissingDocumentsParam(): array
    {
        return [
            'no params' => [
                [],
            ],
            'documents empty' => [
                ['documents' => []],
            ],
            'documentId not number - backward compatibility' => [
                ['documentId' => 'x'],
            ],
        ];
    }

    public function testHandleCommandUserWithNoTeamPrinter(): void
    {
        $command = Cmd::create(['documentId' => 200116]);

        $user = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $team = new \Dvsa\Olcs\Api\Entity\User\Team();
        $user->setTeam($team);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')->andReturn($user);

        $this->expectException(
            \Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class
        );
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandUserWithNoTeam(): void
    {
        $command = Cmd::create(['documentId' => 200116, 'jobName' => 'JOBNAME']);

        $user = new \Dvsa\Olcs\Api\Entity\User\User('PID', 'TYPE');
        $user->setId(10);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')->andReturn($user);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Create::class,
            [
                'type' => Queue::TYPE_PRINT,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode(
                    array_filter(
                        [
                            'documents' => [200116],
                            'userId' => 10,
                            'jobName' => 'JOBNAME',
                        ]
                    )
                ),
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            ['JOBNAME queued for print (document ids: 200116)'],
            $result->getMessages()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $cmdData, mixed $expectedData, mixed $expectedMsgs): void
    {
        $command = Cmd::create($cmdData);

        $team = new \Dvsa\Olcs\Api\Entity\User\Team();
        $team->addTeamPrinters('PRINTER 1');
        $user = new \Dvsa\Olcs\Api\Entity\User\User('PID', 'TYPE');
        $user->setTeam($team);
        $user->setId(10);
        $this->repoMap['User']->shouldReceive('fetchById')
            ->with(10)
            ->andReturn($user)
            ->getMock();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Create::class,
            $expectedData,
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            $expectedMsgs,
            $result->getMessages()
        );
    }

    public static function dpHandleCommand(): array
    {
        return [
            'with list of documents' => [
                'cmdData' => [
                    'documents' => [200116, 200117],
                    'type' => Queue::TYPE_PERMIT_PRINT,
                    'jobName' => 'JOBNAME',
                    'user' => 10,
                ],
                'expectedData' => [
                    'type' => Queue::TYPE_PERMIT_PRINT,
                    'status' => Queue::STATUS_QUEUED,
                    'options' => json_encode(
                        array_filter(
                            [
                                'documents' => [200116, 200117],
                                'userId' => 10,
                                'jobName' => 'JOBNAME',
                            ]
                        )
                    ),
                ],
                'expectedMsgs' => ['JOBNAME queued for print (document ids: 200116, 200117)'],
            ],
            'with one document' => [
                'cmdData' => [
                    'documents' => [200116],
                    'jobName' => 'JOBNAME',
                    'user' => 10,
                    'copies' => 999,
                ],
                'expectedData' => [
                    'type' => Queue::TYPE_PRINT,
                    'status' => Queue::STATUS_QUEUED,
                    'options' => json_encode(
                        array_filter(
                            [
                                'documents' => [200116],
                                'userId' => 10,
                                'jobName' => 'JOBNAME',
                                'copies' => 999,
                            ]
                        )
                    ),
                ],
                'expectedMsgs' => ['JOBNAME queued for print (document ids: 200116)'],
            ],
            'with documentId - backward compatibility' => [
                'cmdData' => [
                    'documentId' => 200116,
                    'jobName' => 'JOBNAME',
                    'user' => 10,
                ],
                'expectedData' => [
                    'type' => Queue::TYPE_PRINT,
                    'status' => Queue::STATUS_QUEUED,
                    'options' => json_encode(
                        array_filter(
                            [
                                'documents' => [200116],
                                'userId' => 10,
                                'jobName' => 'JOBNAME',
                            ]
                        )
                    ),
                ],
                'expectedMsgs' => ['JOBNAME queued for print (document ids: 200116)'],
            ],
            'disc printing with documentId - backward compatibility' => [
                'cmdData' => [
                    'documentId' => 200116,
                    'jobName' => 'JOBNAME',
                    'user' => 10,
                    'isDiscPrinting' => true,
                ],
                'expectedData' => [
                    'type' => Queue::TYPE_DISC_PRINTING_PRINT,
                    'status' => Queue::STATUS_QUEUED,
                    'options' => json_encode(
                        array_filter(
                            [
                                'documents' => [200116],
                                'userId' => 10,
                                'jobName' => 'JOBNAME',
                            ]
                        )
                    ),
                ],
                'expectedMsgs' => ['JOBNAME queued for print (document ids: 200116)'],
            ],
        ];
    }
}
