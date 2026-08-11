<?php

declare(strict_types=1);

namespace CommonTest\Service\Cqrs;

use Common\Service\Cqrs\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Http\Response as HttpResponse;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Cqrs\Response::class)]
final class ResponseTest extends MockeryTestCase
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

        $this->assertEquals('unit_IsCliErr', $this->sut->isClientError());
        $this->assertEquals('unit_isSrvError', $this->sut->isServerError());
        $this->assertEquals('unit_IsNotFnd', $this->sut->isNotFound());
        $this->assertEquals('unit_isOk', $this->sut->isOk());
        $this->assertEquals('unit_Code', $this->sut->getStatusCode());
        $this->assertEquals($expectBody, $this->sut->getBody());
        $this->assertEquals($expectResult, $this->sut->getResult());

        $this->assertSame($this->mockHttpResp, $this->sut->getHttpResponse());

        //  test direct set of result
        $this->sut->setResult('unit_Result');
        $this->assertEquals('unit_Result', $this->sut->getResult());

        //  test to string
        $this->assertSame('Status = unit_Code unit_Phrase
Response = unit_Result', (string)$this->sut);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetResult')]
    public function testGetResult($body, $expect): void
    {
        $this->mockHttpResp
            ->shouldReceive('getBody')->once()->andReturn($body);

        $this->assertSame($expect, $this->sut->getResult());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | string)>>
     *
     * @psalm-return list{array{body: '{"unit_Key": "unit_Value"}', expect: array{unit_Key: 'unit_Value'}}, array{body: 'not Json or broken', expect: array<never, never>}}
     */
    public static function dpTestGetResult(): \Iterator
    {
        yield [
            'body' => '{"unit_Key": "unit_Value"}',
            'expect' => ['unit_Key' => 'unit_Value'],
        ];
        yield [
            'body' => 'not Json or broken',
            'expect' => [],
        ];
    }

    public function testIsForbidden(): void
    {
        $this->mockHttpResp->shouldReceive('getStatusCode')->andReturn(403)->once()->getMock();
        $this->assertTrue($this->sut->isForbidden());
    }
}
