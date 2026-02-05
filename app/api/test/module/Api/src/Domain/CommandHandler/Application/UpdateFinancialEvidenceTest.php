<?php

declare(strict_types=1);

/**
 * Update Financial Evidence Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateFinancialEvidence;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateFinancialEvidence as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Update Financial Evidence Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UpdateFinancialEvidenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateFinancialEvidence();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = $this->getCommand();

        $application = $this->getApplication()
            ->shouldReceive('setFinancialEvidenceUploaded')
            ->with(\Dvsa\Olcs\Api\Entity\Application\Application::FINANCIAL_EVIDENCE_UPLOADED)
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

        $updateData = ['id' => 1, 'section' => 'financialEvidence'];
        $result2 = new Result();
        $result2->addMessage('Section updated');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $updateData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Financial evidence section has been updated',
                'Section updated',
            ],
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getCommand(): mixed
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'financialEvidenceUploaded' => \Dvsa\Olcs\Api\Entity\Application\Application::FINANCIAL_EVIDENCE_UPLOADED,
        ];

        return Cmd::create($data);
    }

    protected function getApplication(): m\MockInterface
    {
        return m::mock(ApplicationEntity::class)->makePartial();
    }
}
