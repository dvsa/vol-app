<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\CreateSnapshot;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\CreateSnapshot as CreateSnapshotCmd;
use Dvsa\Olcs\Api\Entity\System\Category;
use Mockery as m;

/**
 * Queue letters test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateSnapshotTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateSnapshot();
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);
        $this->mockedSmServices['ContinuationReview'] = m::mock();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = ['id' => 1];
        $command = CreateSnapshotCmd::create($data);

        $mockContinuationDetail = m::mock(ContinuationDetailEntity::class)
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(2)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($mockContinuationDetail)
            ->once()
            ->getMock();

        $this->mockedSmServices['ContinuationReview']
            ->shouldReceive('generate')
            ->with($mockContinuationDetail)
            ->andReturn('CONTENT')
            ->once()
            ->getMock();

        $uploadResult = new Result();
        $uploadResult->addMessage('Document uploaded');

        $params = [
            'content' => base64_encode('CONTENT'),
            'licence' => 2,
            'description' => CreateSnapshot::SNAPSHOT_DESCRIPTION,
            'filename' => CreateSnapshot::SNAPSHOT_DESCRIPTION . '.html',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isDigital' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(UploadCmd::class, $params, $uploadResult);

        $result = $this->sut->handleCommand($command);
        $messages = ['Snapshot generated', 'Document uploaded'];
        $this->assertEquals($messages, $result->getMessages());
    }
}
