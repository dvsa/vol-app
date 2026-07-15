<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa;

use Common\Service\Qa\DataTransformer\EcmtNoOfPermitsSingleDataTransformer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * EcmtNoOfPermitsSingleDataTransformerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class EcmtNoOfPermitsSingleDataTransformerTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new EcmtNoOfPermitsSingleDataTransformer();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetTransformed')]
    public function testGetTransformed($data, $expectedTransformedData): void
    {
        $this->assertEquals(
            $expectedTransformedData,
            $this->sut->getTransformed($data)
        );
    }

    /**
     * @return \Iterator<(int | string), array<array<string>>>
     *
     * @psalm-return list{list{array{emissionsCategory: 'euro5', permitsRequired: '12'}, array{euro5: '12', euro6: '0'}}, list{array{emissionsCategory: 'euro6', permitsRequired: '8'}, array{euro5: '0', euro6: '8'}}}
     */
    public static function dpGetTransformed(): \Iterator
    {
        yield [
            [
                'emissionsCategory' => 'euro5',
                'permitsRequired' => '12'
            ],
            [
                'euro5' => '12',
                'euro6' => '0'
            ]
        ];
        yield [
            [
                'emissionsCategory' => 'euro6',
                'permitsRequired' => '8'
            ],
            [
                'euro5' => '0',
                'euro6' => '8'
            ]
        ];
    }

    public function testGetTransformedNoPermitsRequiredKey(): void
    {
        $data = [
            'euro5' => '12',
            'euro6' => '6'
        ];

        $this->assertEquals(
            $data,
            $this->sut->getTransformed($data)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetTransformedUnexpectedData')]
    public function testGetTransformedUnexpectedData($data): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(EcmtNoOfPermitsSingleDataTransformer::ERR_UNEXPECTED_DATA);

        $this->sut->getTransformed($data);
    }

    /**
     * @return \Iterator<(int | string), array<array<string>>>
     *
     * @psalm-return list{list{array{permitsRequired: '7', euro5: '6'}}, list{array{permitsRequired: '8', euro5: '12'}}, list{array{permitsRequired: '10', euro5: '5', euro6: '7'}}}
     */
    public static function dpGetTransformedUnexpectedData(): \Iterator
    {
        yield [
            [
                'permitsRequired' => '7',
                'euro5' => '6'
            ]
        ];
        yield [
            [
                'permitsRequired' => '8',
                'euro5' => '12'
            ]
        ];
        yield [
            [
                'permitsRequired' => '10',
                'euro5' => '5',
                'euro6' => '7'
            ]
        ];
    }
}
