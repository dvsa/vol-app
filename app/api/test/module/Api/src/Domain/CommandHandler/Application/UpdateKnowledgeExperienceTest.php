<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateKnowledgeExperience;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateKnowledgeExperience as Cmd;
use Mockery as m;

final class UpdateKnowledgeExperienceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateKnowledgeExperience();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create([
            'id' => 1,
            'version' => 1,
            'evidenceUploadType' => (string) ApplicationEntity::FINANCIAL_EVIDENCE_UPLOADED,
            'knowledgeExperienceOlat' => 'N',
        ]);

        $application = m::mock(ApplicationEntity::class)->makePartial();

        $application
            ->shouldReceive('setKnowledgeExperienceEvidenceUploaded')
            ->with((string) ApplicationEntity::FINANCIAL_EVIDENCE_UPLOADED)
            ->once();

        $application
            ->shouldReceive('setKnowledgeExperienceOlat')
            ->with('N')
            ->once();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with(1, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once()
            ->getMock();

        $updateData = [
            'id' => 1,
            'section' => 'knowledgeExperience',
        ];

        $sideEffectResult = new Result();
        $sideEffectResult->addMessage('Section updated');

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            $updateData,
            $sideEffectResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Application' => 1,
            ],
            'messages' => [
                'Section updated',
                'knowledge experience updated',
            ],
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
}
