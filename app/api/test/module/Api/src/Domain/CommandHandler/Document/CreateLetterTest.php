<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplate;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\CreateLetter;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Document\CreateLetter as Cmd;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Letter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateLetterTest extends AbstractCommandHandlerTestCase
{
    protected const LICENCE_ID = 2;
    protected const APPLICATION_ID = 4;
    protected const USER_ID = 123;

    public function setUp(): void
    {
        $this->sut = new CreateLetter();
        $this->mockRepo('DocTemplate', DocTemplate::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $queryData = ['details' => ['category' => '123', 'documentSubCategory' => '321']];
        $expectedQueryData = ['details' => ['category' => '123', 'documentSubCategory' => '321']];

        $data = [
            'template' => 111,
            'data' => $queryData,
            'meta' => 'foo',
            'disableBookmarks' => true
        ];
        $command = Cmd::create($data);
        $this->setupDocTemplateRepo('Foo-Bar_Cake Cheese.rtf');

        $result = new Result();
        $result->addMessage('GenerateAndStore');
        $data = $this->getGenerateAndStoreData('Foo-Bar_Cake Cheese.rtf', $expectedQueryData);
        $this->expectedSideEffect(GenerateAndStore::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('followUpTaskForApplicationOrVariationFirstLetterProvider')]
    public function testHandleCommandWithFollowUpTaskForApplicationOrVariationFirstLetter(mixed $templateIdentifier): void
    {
        $this->setupDocTemplateRepo($templateIdentifier);
        $this->setupAuthService();

        $cmdData = [
            'template' => 111,
            'data' => [
                'details' => [
                    'category' => '123',
                    'documentSubCategory' => '321'
                ],
                'application' => static::APPLICATION_ID,
                'licence' => static::LICENCE_ID
            ],
            'meta' => 'foo',
            'disableBookmarks' => true
        ];
        $command = Cmd::create($cmdData);

        $expectedQueryData = [
            'details' => [
                'category' => '123',
                'documentSubCategory' => '321'
            ],
            'application' => static::APPLICATION_ID,
            'licence' => static::LICENCE_ID
        ];
        $generateAndStoreResult = new Result();
        $generateAndStoreResult->addMessage('GenerateAndStore');
        $generateAndStoreData = $this->getGenerateAndStoreData($templateIdentifier, $expectedQueryData);
        $this->expectedSideEffect(GenerateAndStore::class, $generateAndStoreData, $generateAndStoreResult);

        $createTaskResult = new Result();
        $createTaskResult->addMessage('CreateTask');
        $actionDate = new DateTime();
        $actionDate->add(new \DateInterval('P14D'));
        $createTaskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FIRST_REQUEST,
            'description' => 'Check response to first letters',
            'application' => static::APPLICATION_ID,
            'licence' => static::LICENCE_ID,
            'assignedToUser' => static::USER_ID,
            'assignedByUser' => static::USER_ID,
            'urgent' => 'N',
            'actionDate' => $actionDate->format('Y-m-d')
        ];
        $this->expectedSideEffect(CreateTask::class, $createTaskData, $createTaskResult);

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore',
                'CreateTask'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('followUpTaskForApplicationOrVariationFirstLetterExceptionProvider')]
    public function testHandleCommandWithFollowUpTaskForApplicationOrVariationFirstLetterException(
        array $commandData,
        string $templateIdentifier,
        string $expectedExceptionMessage
    ): void {
        $this->setupDocTemplateRepo($templateIdentifier);

        $command = Cmd::create($commandData);

        $generateAndStoreResult = new Result();
        $generateAndStoreResult->addMessage('GenerateAndStore');
        $generateAndStoreData = $this->getGenerateAndStoreData($templateIdentifier, $commandData['data']);
        $this->expectedSideEffect(GenerateAndStore::class, $generateAndStoreData, $generateAndStoreResult);

        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectException(\Exception::class);

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore',
                'CreateTask'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('followUpTaskForApplicationOrVariationFinalLetterProvider')]
    public function testHandleCommandWithFollowUpTaskForApplicationOrVariationFinalLetter(mixed $templateIdentifier): void
    {
        $this->setupDocTemplateRepo($templateIdentifier);
        $this->setupAuthService();

        $cmdData = [
            'template' => 111,
            'data' => [
                'details' => [
                    'category' => '123',
                    'documentSubCategory' => '321'
                ],
                'application' => static::APPLICATION_ID,
                'licence' => static::LICENCE_ID
            ],
            'meta' => 'foo',
            'disableBookmarks' => true
        ];
        $command = Cmd::create($cmdData);

        $expectedQueryData = [
            'details' => [
                'category' => '123',
                'documentSubCategory' => '321'
            ],
            'application' => static::APPLICATION_ID,
            'licence' => static::LICENCE_ID
        ];
        $generateAndStoreResult = new Result();
        $generateAndStoreResult->addMessage('GenerateAndStore');
        $generateAndStoreData = $this->getGenerateAndStoreData($templateIdentifier, $expectedQueryData);
        $this->expectedSideEffect(GenerateAndStore::class, $generateAndStoreData, $generateAndStoreResult);

        $createTaskResult = new Result();
        $createTaskResult->addMessage('CreateTask');
        $actionDate = new DateTime();
        $actionDate->add(new \DateInterval('P14D'));
        $createTaskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FINAL_REQUEST,
            'description' => 'Check response to final letters',
            'application' => static::APPLICATION_ID,
            'licence' => static::LICENCE_ID,
            'assignedToUser' => static::USER_ID,
            'assignedByUser' => static::USER_ID,
            'urgent' => 'N',
            'actionDate' => $actionDate->format('Y-m-d')
        ];
        $this->expectedSideEffect(CreateTask::class, $createTaskData, $createTaskResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore',
                'CreateTask'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('followUpTaskForApplicationOrVariationFirstLetterExceptionProvider')]
    public function testHandleCommandWithFollowUpTaskForApplicationOrVariationFinalLetterException(
        array $commandData,
        string $templateIdentifier,
        string $expectedExceptionMessage
    ): void {
        $this->setupDocTemplateRepo($templateIdentifier);

        $command = Cmd::create($commandData);

        $generateAndStoreResult = new Result();
        $generateAndStoreResult->addMessage('GenerateAndStore');
        $generateAndStoreData = $this->getGenerateAndStoreData($templateIdentifier, $commandData['data']);
        $this->expectedSideEffect(GenerateAndStore::class, $generateAndStoreData, $generateAndStoreResult);

        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectException(\Exception::class);

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore',
                'CreateTask'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Error generating document');
        $queryData = ['details' => ['category' => '123', 'documentSubCategory' => '321']];
        $expectedQueryData = ['details' => ['category' => '123', 'documentSubCategory' => '321']];

        $data = [
            'template' => 111,
            'data' => $queryData,
            'meta' => 'foo',
            'disableBookmarks' => false
        ];

        $command = Cmd::create($data);

        /** @var Entity\Doc\DocTemplate $template */
        $template = m::mock(Entity\Doc\DocTemplate::class)->makePartial();
        $template->setDescription('Foo-:Bar_Cake Cheese');
        $template->shouldReceive('getDocument->getIdentifier')
            ->andReturn('Foo-Bar_Cake Cheese.rtf');

        $this->repoMap['DocTemplate']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($template);

        $result = new Result();
        $result->addMessage('GenerateAndStore');
        $data = [
            'template' => 'Foo-Bar_Cake Cheese.rtf',
            'query' => $expectedQueryData,
            'description' => 'Foo-:Bar_Cake Cheese',
            'category' => '123',
            'subCategory' => '321',
            'isExternal' => false,
            'isScan' => false,
            'metadata' => 'foo',
            'disableBookmarks' => false
        ];
        $this->expectedSideEffectThrowsException(
            GenerateAndStore::class,
            $data,
            new \Exception('Error generating document')
        );
        $this->sut->handleCommand($command);
    }

    protected function getGenerateAndStoreData(string $templateIdentifier, array $expectedQueryData): array
    {
        $generateAndStoreData = [
            'template' => $templateIdentifier,
            'query' => $expectedQueryData,
            'description' => 'Foo-:Bar_Cake Cheese',
            'category' => '123',
            'subCategory' => '321',
            'isExternal' => false,
            'isScan' => false,
            'metadata' => 'foo',
            'disableBookmarks' => true
        ];
        return $generateAndStoreData;
    }

    protected function setupDocTemplateRepo(string $templateIdentifier): void
    {
        /** @var Entity\Doc\DocTemplate $template */
        $template = m::mock(Entity\Doc\DocTemplate::class)->makePartial();
        $template->setDescription('Foo-:Bar_Cake Cheese');
        $template->shouldReceive('getDocument->getIdentifier')
            ->andReturn($templateIdentifier);

        $this->repoMap['DocTemplate']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($template);
    }

    protected function setupAuthService(): void
    {
        $mockUser = m::mock()
            ->shouldReceive('getId')
            ->andReturn(static::USER_ID)
            ->once()
            ->getMock();
        $mockIdentity = m::mock()
            ->shouldReceive('getUser')
            ->andReturn($mockUser)
            ->once()
            ->getMock();
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($mockIdentity)
            ->once();
    }

    public static function followUpTaskForApplicationOrVariationFinalLetterProvider(): array
    {
        return array_map(fn($v) => [$v], CreateLetter::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FINAL);
    }

    public static function followUpTaskForApplicationOrVariationFirstLetterProvider(): array
    {
        return array_map(fn($v) => [$v], CreateLetter::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FIRST);
    }

    public static function followUpTaskForApplicationOrVariationFirstLetterExceptionProvider(): array
    {
        return [
            'missing_application' => [
                'commandData' => [
                    'template' => 111,
                    'data' => [
                        'details' => [
                            'category' => '123',
                            'documentSubCategory' => '321'
                        ],
                        'licence' => static::LICENCE_ID
                    ],
                    'meta' => 'foo',
                    'disableBookmarks' => true
                ],
                'templateIdentifier' => CreateLetter::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FIRST[0],
                'expectedExceptionMessage' => 'Expected `applicationId` when creating a task for first letter.'
            ],
            'missing_licence' => [
                'commandData' => [
                    'template' => 111,
                    'data' => [
                        'details' => [
                            'category' => '123',
                            'documentSubCategory' => '321'
                        ],
                        'application' => static::APPLICATION_ID,
                    ],
                    'meta' => 'foo',
                    'disableBookmarks' => true
                ],
                'templateIdentifier' => CreateLetter::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FIRST[0],
                'expectedExceptionMessage' => 'Expected `licenceId` when creating a task for first letter.'
            ]
        ];
    }

    public function followUpTaskForApplicationOrVariationFinalLetterExceptionProvider(): array
    {
        return [
            'missing_application' => [
                'commandData' => [
                    'template' => 111,
                    'data' => [
                        'details' => [
                            'category' => '123',
                            'documentSubCategory' => '321'
                        ],
                        'licence' => static::LICENCE_ID
                    ],
                    'meta' => 'foo',
                    'disableBookmarks' => true
                ],
                'templateIdentifier' => CreateLetter::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FINAL[0],
                'expectedExceptionMessage' => 'Expected `applicationId` when creating a task for final letter.'
            ],
            'missing_licence' => [
                'commandData' => [
                    'template' => 111,
                    'data' => [
                        'details' => [
                            'category' => '123',
                            'documentSubCategory' => '321'
                        ],
                        'application' => static::APPLICATION_ID,
                    ],
                    'meta' => 'foo',
                    'disableBookmarks' => true
                ],
                'templateIdentifier' => CreateLetter::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FINAL[0],
                'expectedExceptionMessage' => 'Expected `licenceId` when creating a task for final letter.'
            ]
        ];
    }
}
