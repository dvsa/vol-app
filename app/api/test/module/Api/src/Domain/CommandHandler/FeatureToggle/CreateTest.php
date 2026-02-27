<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\FeatureToggle;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\FeatureToggle\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeatureToggle as FeatureToggleRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\FeatureToggle\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle as FeatureToggleEntity;

/**
 * Create FeatureToggle Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    protected $refData = [FeatureToggleEntity::ACTIVE_STATUS];

    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('FeatureToggle', FeatureToggleRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'friendlyName' => 'friendly name',
            'configName' => 'config name',
            'status' => FeatureToggleEntity::ACTIVE_STATUS
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['FeatureToggle']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(FeatureToggleEntity::class))
            ->andReturnUsing(
                function (FeatureToggleEntity $featureToggle) use (&$savedFeatureToggle) {
                    $featureToggle->setId(999);
                    $savedFeatureToggle = $featureToggle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['FeatureToggle' => 999],
            'messages' => ["Feature toggle '999' created"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
