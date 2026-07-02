<?php

namespace CommonTest\Service\Cqrs;

use Common\Service\Cqrs\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Http\Response as HttpResponse;

/**
 * @covers \Common\Service\Cqrs\Response
 */
class ResponseTest extends MockeryTestCase
{
    /** @var  Response */
    private $sut;

    /** @var  m\MockInterface|HttpResponse */
    private $mockHttpResp;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockHttpResp = m::mock(HttpResponse::class);

        $this->sut = new Response($this->mockHttpResp);
    }

    public function testGetSet(): void
    {
        $expectResult = ['foo' => 'unit_Val'];
        $expectBody = json_encode($expectResult);

        $this->mockHttpResp
            ->shouldReceive('isClientError')->once()->andReturn('unit_IsCliErr')
            ->shouldReceive('isServerError')->once()->andReturn('unit_isSrvError')
            ->shouldReceive('isNotFound')->once()->andReturn('unit_IsNotFnd')
            ->shouldReceive('isSuccess')->once()->andReturn('unit_isOk')
            ->shouldReceive('getStatusCode')->times(2)->andReturn('unit_Code')
            ->shouldReceive('getBody')->times(2)->andReturn($expectBody)
            ->shouldReceive('getReasonPhrase')->once()->andReturn('unit_Phrase');

        static::assertEquals('unit_IsCliErr', $this->sut->isClientError());
        static::assertEquals('unit_isSrvError', $this->sut->isServerError());
        static::assertEquals('unit_IsNotFnd', $this->sut->isNotFound());
        static::assertEquals('unit_isOk', $this->sut->isOk());
        static::assertEquals('unit_Code', $this->sut->getStatusCode());
        static::assertEquals($expectBody, $this->sut->getBody());
        static::assertEquals($expectResult, $this->sut->getResult());

        static::assertSame($this->mockHttpResp, $this->sut->getHttpResponse());

        //  test direct set of result
        $this->sut->setResult('unit_Result');
        static::assertEquals('unit_Result', $this->sut->getResult());

        //  test to string
        static::assertEquals(
            'Status = unit_Code unit_Phrase
Response = unit_Result',
            (string)$this->sut
        );
    }

    /**
     * @dataProvider dpTestGetResult
     */
    public function testGetResult($body, $expect): void
    {
        $this->mockHttpResp
            ->shouldReceive('getBody')->once()->andReturn($body);

        static::assertSame($expect, $this->sut->getResult());
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return list{array{body: '{"unit_Key": "unit_Value"}', expect: array{unit_Key: 'unit_Value'}}, array{body: 'not Json or broken', expect: array<never, never>}}
     */
    public function dpTestGetResult(): array
    {
        return [
            [
                'body' => '{"unit_Key": "unit_Value"}',
                'expect' => ['unit_Key' => 'unit_Value'],
            ],
            [
                'body' => 'not Json or broken',
                'expect' => [],
            ],
        ];
    }

    public function testIsForbidden(): void
    {
        $this->mockHttpResp->shouldReceive('getStatusCode')->andReturn(403)->once()->getMock();
        static::assertTrue($this->sut->isForbidden());
    }
}
