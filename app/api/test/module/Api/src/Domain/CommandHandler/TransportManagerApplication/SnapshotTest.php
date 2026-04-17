<?php

declare(strict_types=1);

/**
 * Snapshot Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\Snapshot as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication\Snapshot as Command;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Generator;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Snapshot Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SnapshotTest extends AbstractCommandHandlerTestCase
{
    protected $loggedInUser;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManagerApplication', Repository\TransportManagerApplication::class);

        $this->mockedSmServices['TmReviewSnapshot'] = m::mock(Generator::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $tmaStatus, mixed $expectedString): void
    {
        $command = Command::create(['id' => 111, 'user' => 1]);

        $tma = m::mock(TransportManagerApplication::class);

        $tma->shouldReceive('getTransportManager->getId')
            ->andReturn(222);

        $tma->shouldReceive('getApplication->getId')
            ->andReturn(333);

        $tma->shouldReceive('getApplication->getLicence->getId')
            ->andReturn(444);

        $tma->shouldReceive('getTmApplicationStatus->getId')
            ->andReturn($tmaStatus);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($tma);

        $this->mockedSmServices['TmReviewSnapshot']->shouldReceive('generate')
            ->once()
            ->with($tma)
            ->andReturn('<markup>');

        $result = new Result();
        $result->addMessage('Upload');
        $data = [
            'content' => base64_encode('<markup>'),
            'filename' => 'TM222 snapshot for application 333 (at ' . $expectedString . ').html',
            'category' => Category::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL,
            'isExternal' => false,
            'isScan' => false,
            'transportManager' => 222,
            'application' => 333,
            'licence' => 444,
            'user' => 1
        ];
        $this->expectedSideEffect(Upload::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Snapshot generated',
                'Upload'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            [
                'tmaStatus' => TransportManagerApplication::STATUS_OPERATOR_SIGNED,
                'expectedString' => 'submission'
            ],
            [
                'tmaStatus' => TransportManagerApplication::STATUS_AWAITING_SIGNATURE,
                'expectedString' => 'grant'
            ],
            [
                'tmaStatus' => TransportManagerApplication::STATUS_INCOMPLETE,
                'expectedString' => 'grant'
            ]
        ];
    }
}
