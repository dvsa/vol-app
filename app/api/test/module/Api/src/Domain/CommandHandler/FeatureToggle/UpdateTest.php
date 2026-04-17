<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\FeatureToggle;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\FeatureToggle\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeatureToggle as FeatureToggleRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\FeatureToggle\Update as UpdateCmd;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle as FeatureToggleEntity;

/**
 * Update FeatureToggle Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    protected $refData = [FeatureToggleEntity::ACTIVE_STATUS];

    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('FeatureToggle', FeatureToggleRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 999;
        $configName = 'config name';
        $friendlyName = 'friendly name';
        $status = FeatureToggleEntity::ACTIVE_STATUS;

        $cmdData = [
            'id' => $id,
            'friendlyName' => $friendlyName,
            'configName' => $configName,
            'status' => FeatureToggleEntity::ACTIVE_STATUS
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(FeatureToggleEntity::class);
        $entity->shouldReceive('update')
            ->once()
            ->with($configName, $friendlyName, $this->refData[$status]);
        $entity->shouldReceive('getId')
            ->twice()
            ->andReturn($id);

        $this->repoMap['FeatureToggle']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($entity);

        $this->repoMap['FeatureToggle']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(FeatureToggleEntity::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['FeatureToggle' => $id],
            'messages' => ["Feature toggle '" . $id . "' updated"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
