<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\ProcessReminder as Command;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\ProcessReminder as CommandHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;

/**
 * Process reminder test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ProcessReminderTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('detailsProvider')]
    public function testHandleCommand(mixed $isGoods, mixed $template): void
    {
        $command = Command::create(['id' => 1, 'user' => 2]);

        $mockLicence = m::mock()
            ->shouldReceive('getId')
            ->andReturn(3)
            ->twice()
            ->shouldReceive('isGoods')
            ->andReturn($isGoods)
            ->once()
            ->getMock();

        $mockContinuationDetail = m::mock(ContinuationDetailEntity::class)
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->times(3)
            ->getMock();

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchWithLicence')
            ->with(1)
            ->andReturn($mockContinuationDetail)
            ->getMock();

        $docResult = new Result();
        $docResult->addId('document', 101);
        $docResult->addMessage('Document dispatched');
        $dtoData = [
            'template' => $template,
            'query' => [
                'licence' => 3,
                'user' => 2
            ],
            'description' => 'Checklist reminder',
            'licence' => 3,
            'category' => CategoryEntity::CATEGORY_LICENSING,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isExternal'  => false,
            'isScan' => false,
            'dispatch' => true
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $docResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'messages' => [
                'Document dispatched',
                'Continuation checklist reminder letter generated'
            ],
            'ids' => [
                'document' => 101
            ]
        ];

        $this->assertEquals($expected['messages'], $result->getMessages());
        $this->assertEquals(['document' => 101], $result->getIds());
    }

    public static function detailsProvider(): array
    {
        return [
            [true, 'LIC_CONTD_NO_CHECKLIST_GV'],
            [false, 'LIC_CONTD_NO_CHECKLIST_PSV']
        ];
    }
}
