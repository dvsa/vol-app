<?php

declare(strict_types=1);

/**
 * Tests RestResponseTrait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Db\Traits;

use Dvsa\Olcs\Db\Traits\RestResponseTrait;
use Laminas\Http\Response;
use Mockery as m;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses, Squiz.Classes.ClassFileName.NoMatch
class RestResponseTraitStub
{
    use RestResponseTrait;
}

/**
 * Tests RestResponseTrait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
class RestResponseTraitTest extends \PHPUnit\Framework\TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Test that getNewResponse returns a new instance
     */
    #[\PHPUnit\Framework\Attributes\Group('Traits')]
    #[\PHPUnit\Framework\Attributes\Group('RestResponseTrait')]
    public function testGetNewResponse(): void
    {
        $trait = new RestResponseTraitStub();

        $response = $trait->getNewResponse();

        $this->assertTrue($response instanceof Response);

        $response2 = $trait->getNewResponse();

        $this->assertTrue($response2 instanceof Response);

        $this->assertFalse($response === $response2);
    }

    /**
     * Test respond
     */
    #[\PHPUnit\Framework\Attributes\Group('Traits')]
    #[\PHPUnit\Framework\Attributes\Group('RestResponseTrait')]
    #[\PHPUnit\Framework\Attributes\DataProvider('providerRespond')]
    public function testRespond(mixed $input, mixed $expected): void
    {
        $expectedContent = json_encode(
            [
                'Response' => [
                    'Code' => $expected['code'],
                    'Message' => $expected['reasonPhrase'],
                    'Summary' => $expected['summary'],
                    'Data' => $expected['data']
                ]
            ]
        );

        $mockResponse = m::mock(Response::class)->makePartial();

        $mockResponse->shouldReceive('setStatusCode')
            ->once()
            ->with($expected['code']);

        $mockResponse->shouldReceive('setContent')
            ->once()
            ->with($expectedContent);

        $mockResponse->shouldReceive('getReasonPhrase')
            ->andReturn($expected['reasonPhrase']);

        $trait = m::mock(RestResponseTraitStub::class)->makePartial();

        $trait->shouldReceive('getNewResponse')
            ->once()
            ->andReturn($mockResponse);

        $response = match (count($input)) {
            1 => $trait->respond($input[0]),
            2 => $trait->respond($input[0], $input[1]),
            3 => $trait->respond($input[0], $input[1], $input[2]),
        };

        $this->assertEquals($response, $mockResponse);
    }

    /**
     * Provider for respond
     *
     * @return array
     */
    public static function providerRespond(): array
    {
        return [
            [
                [Response::STATUS_CODE_200],
                [
                    'code' => Response::STATUS_CODE_200,
                    'reasonPhrase' => 'Some Phrase',
                    'summary' => null,
                    'data' => []
                ]
            ],
            [
                [Response::STATUS_CODE_400, 'Summary'],
                [
                    'code' => Response::STATUS_CODE_400,
                    'reasonPhrase' => 'Some Phrase',
                    'summary' => 'Summary',
                    'data' => []
                ]
            ],
            [
                [Response::STATUS_CODE_404, 'Summary', ['foo' => 'bar']],
                [
                    'code' => Response::STATUS_CODE_404,
                    'reasonPhrase' => 'Some Phrase',
                    'summary' => 'Summary',
                    'data' => ['foo' => 'bar']
                ]
            ]
        ];
    }
}
