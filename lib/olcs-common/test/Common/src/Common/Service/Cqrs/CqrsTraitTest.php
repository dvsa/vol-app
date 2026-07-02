<?php

namespace CommonTest\Common\Service\Cqrs;

use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use CommonTest\Common\Service\Cqrs\Stub\CqrsTraitStub;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Http\Response as HttpResponse;

class CqrsTraitTest extends MockeryTestCase
{
    /** @var  m\MockInterface */
    private $mockHttpResp;

    /** @var  m\MockInterface */
    private $mockCqrsResp;

    /** @var  m\MockInterface */
    private $mockFlashMsngr;

    /** @var  CqrsTraitStub */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockHttpResp = m::mock(HttpResponse::class);

        $this->mockCqrsResp = m::mock(Response::class);
        $this->mockCqrsResp->shouldReceive('getHttpResponse')->andReturn($this->mockHttpResp);

        $this->mockFlashMsngr = m::mock(FlashMessengerHelperService::class);

        $this->sut = new CqrsTraitStub();
        $this->sut->setFlashMessenger($this->mockFlashMsngr);
    }

    public function testShowApiMessagesFromResponseStream(): void
    {
        self::expectNotToPerformAssertions();

        $mockCqrsResp = m::mock(Response::class)
            ->shouldReceive('getHttpResponse')->andReturn(m::mock(HttpResponse\Stream::class))
            ->getMock();

        $this->sut->testShowApiMessagesFromResponse($mockCqrsResp);
    }

    public function testShowApiMessagesFromResponseMssg(): void
    {
        $messages = [
            'messages' => ['EXPECT', 'EXPECT2'],
        ];

        json_decode('aaaaa');

        $this->mockFlashMsngr
            ->shouldReceive('addErrorMessage')->once()->with('DEBUG: Error decoding json response: EXPECT_BODY')
            ->shouldReceive('addErrorMessage')->times(2)->with(m::anyOf('DEBUG: EXPECT', 'DEBUG: EXPECT2'));

        $this->mockCqrsResp
            ->shouldReceive('getStatusCode')->once()->andReturn(200)
            ->shouldReceive('getBody')->once()->andReturn('EXPECT_BODY')
            ->shouldReceive('isOk')->once()->andReturn(false)
            ->shouldReceive('getResult')->once()->andReturn($messages);

        $this->sut->testShowApiMessagesFromResponse($this->mockCqrsResp);
    }
}
