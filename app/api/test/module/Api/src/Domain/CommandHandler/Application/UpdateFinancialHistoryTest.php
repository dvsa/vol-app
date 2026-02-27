<?php

declare(strict_types=1);

/**
 * Update Financial History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateFinancialHistory;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateFinancialHistory as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Update Financial History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateFinancialHistoryTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateFinancialHistory();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = $this->getCommand();

        $application = $this->getApplication()
            ->shouldReceive('updateFinancialHistory')
            ->with('Y', 'Y', 'Y', 'Y', 'Y', str_repeat('X', 200), '1')
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once()
            ->getMock();

        $data = [
            'id' => 1,
            'section' => 'financialHistory'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Financial history section has been updated',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getCommand(): mixed
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'bankrupt' => 'Y',
            'liquidation' => 'Y',
            'receivership' => 'Y',
            'administration' => 'Y',
            'disqualified' => 'Y',
            'insolvencyDetails' => str_repeat('X', 200),
            'insolvencyConfirmation' => '1'
        ];

        return Cmd::create($data);
    }

    protected function getApplication(): m\MockInterface
    {
        return m::mock(ApplicationEntity::class)->makePartial();
    }
}
