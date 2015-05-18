<?php

/**
 * ContinuationChecklistGenerateLettersTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace CliTest\Service\Queue\Consumer;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Queue\Consumer\ContinuationChecklistReminderGenerateLetter;
use Common\BusinessService\Response as BsResponse;

/**
 * ContinuationChecklistGenerateLettersTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationChecklistReminderGenerateLetterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->bsm = m::mock();
        $this->sm->setService('BusinessServiceManager', $this->bsm);

        $this->sut = new ContinuationChecklistReminderGenerateLetter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testProcessSuccess()
    {
        $mockQueueEntityService = m::mock();
        $this->sm->setService('Entity\Queue', $mockQueueEntityService);

        $mockGenerator = m::mock();

        $message = [
            'id' => 23,
            'entityId' => 425,
        ];

        $this->bsm->shouldReceive('get')->with('ContinuationChecklistReminderGenerateLetter')->once()
            ->andReturn($mockGenerator);

        $mockGenerator->shouldReceive('process')->with(['continuationDetailId' => 425])->once()
            ->andReturn(new BsResponse(BsResponse::TYPE_SUCCESS));

        $mockQueueEntityService->shouldReceive('complete')->with($message)->once();

        $this->assertContains('Success', $this->sut->processMessage($message));
    }

    public function testProcessFailed()
    {
        $mockQueueEntityService = m::mock();
        $this->sm->setService('Entity\Queue', $mockQueueEntityService);

        $mockGenerator = m::mock();

        $message = [
            'id' => 23,
            'entityId' => 425,
        ];

        $this->bsm->shouldReceive('get')->with('ContinuationChecklistReminderGenerateLetter')->once()
            ->andReturn($mockGenerator);

        $mockGenerator->shouldReceive('process')->with(['continuationDetailId' => 425])->once()
            ->andReturn(new BsResponse(BsResponse::TYPE_FAILED));

        $mockQueueEntityService->shouldReceive('failed')->with($message)->once();

        $this->assertContains('Failed', $this->sut->processMessage($message));
    }
}
