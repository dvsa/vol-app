<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Scan;

use Dvsa\Olcs\Api\Domain\CommandHandler\Scan\CreateContinuationSeparatorSheet as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Scan\CreateContinuationSeparatorSheet as Cmd;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * CreateContinuationSeparatorSheetTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateContinuationSeparatorSheetTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Scan', \Dvsa\Olcs\Api\Domain\Repository\Scan::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        $this->categoryReferences = [
        ];

        $this->subCategoryReferences = [
        ];

        parent::initReferences();
    }

    public function testHandle(): void
    {
        $command = Cmd::create(
            [
                'licNo' => 'LIC0001',
            ]
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet::class,
            [
                'categoryId' => 1,
                'subCategoryId' => 74,
                'entityIdentifier' => 'LIC0001',
                'descriptionId' => 112,
                'description' => null,
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }
}
