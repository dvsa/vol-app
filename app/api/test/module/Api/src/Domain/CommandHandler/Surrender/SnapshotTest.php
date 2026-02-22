<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\Snapshot;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Snapshot as Command;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Generator;
use Mockery as m;
use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;

class SnapshotTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Snapshot();
        $this->mockRepo('Surrender', Repository\Surrender::class);

        $this->mockedSmServices[Generator::class] = m::mock(Generator::class);
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class] = m::mock(\LmcRbacMvc\Service\AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['id' => 111]);

        $mockSurrenderEntity = m::mock(Surrender::class);

        $this->mockedSmServices[Generator::class]->shouldReceive('generate')
            ->once()
            ->with($mockSurrenderEntity)
            ->andReturn('<markup>');

        $mockSurrenderEntity->shouldReceive('getId')->andReturn(222);

        $this->repoMap['Surrender']->shouldReceive('fetchOneByLicenceId')
            ->with($command->getId(), Query::HYDRATE_OBJECT)
            ->andReturn($mockSurrenderEntity);

        $result = new Result();
        $result->addMessage('Upload');

        $data = [
            'content' => base64_encode(trim('<markup>')),
            'filename' => 'Surrender Snapshot.html',
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
            'isExternal' => false,
            'isScan' => false,
            'licence' => 111,
            'surrender' => 222,
        ];

        $this->expectedSideEffect(Upload::class, $data, $result);

        $expected = [
            'id' => [],
            'messages' => [
                'Snapshot generated',
                'Upload'
            ]
        ];

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expected, $result->toArray());
    }
}
