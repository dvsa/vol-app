<?php

/**
 * Data Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\DataHelperService;

/**
 * Data Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DataHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Holds the SUT
     *
     * @var \Common\Service\Helper\DataHelperService
     */
    private $sut;

    /**
     * Setup the sut
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new DataHelperService();
    }

    /**
     * @group helper_service
     * @group data_helper_service
     */
    public function testArrayRepeat(): void
    {
        $input = [
            'foo' => 'bar',
            'cake' => [
                'nested' => true
            ]
        ];

        $expected = [$input, $input, $input];

        $this->assertEquals($expected, $this->sut->arrayRepeat($input, 3));
    }

    /**
     * @group helper_service
     * @group data_helper_service
     */
    public function testProcessDataMapWithoutMap(): void
    {
        $input = [
            'foo' => 'bar'
        ];

        $output = $this->sut->processDataMap($input);

        $this->assertEquals($input, $output);
    }

    /**
     * @group helper_service
     * @group data_helper_service
     */
    public function testProcessDataMap(): void
    {
        $input = [
            'foo' => [
                'a' => 'A',
                'b' => 'B'
            ],
            'bar' => [
                'c' => 'C',
                'd' => 'D'
            ],
            'bob' => [
                'e' => 'E',
                'f' => 'F'
            ]
        ];

        $map = [
            'main' => [
                'mapFrom' => ['foo', 'bar'],
                'values' => ['cake' => 'cats'],
                'children' => [
                    'bobs' => [
                        'mapFrom' => ['bob']
                    ]
                ]
            ]
        ];

        $expected = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'cake' => 'cats',
            'bobs' => [
                'e' => 'E',
                'f' => 'F'
            ]
        ];

        $output = $this->sut->processDataMap($input, $map);

        $this->assertEquals($expected, $output);
    }

    /**
     * @group helper_service
     * @group data_helper_service
     */
    public function testProcessDataMapWithAddress(): void
    {
        $input = [
            'foo' => [
                'a' => 'A',
                'b' => 'B'
            ],
            'bar' => [
                'c' => 'C',
                'd' => 'D'
            ],
            'someAddress' => [
                'addressLine1' => '123',
                'addressLine2' => '456',
                'searchPostcode' => 'foo',
                'countryCode' => 'uk'
            ]
        ];

        $map = [
            '_addresses' => [
                'someAddress'
            ],
            'main' => [
                'mapFrom' => ['foo', 'bar', 'addresses'],
            ]
        ];

        $expected = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'someAddress' => [
                'addressLine1' => '123',
                'addressLine2' => '456',
                'countryCode' => 'uk'
            ]
        ];

        $output = $this->sut->processDataMap($input, $map);

        $this->assertEquals($expected, $output);
    }

    public function testReplaceIds(): void
    {
        $data = [
            'foo' => 'bar',
            'bar' => [
                'cake'
            ],
            'cake' => [
                'id' => 124,
                'blah' => 'blap'
            ]
        ];
        $expectedData = [
            'foo' => 'bar',
            'bar' => [
                'cake'
            ],
            'cake' => 124
        ];

        $this->assertEquals($expectedData, $this->sut->replaceIds($data));
    }

    public function testFetchNestedData(): void
    {
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => 'hi!'
                ]
            ]
        ];

        $this->assertEquals(
            'hi!',
            $this->sut->fetchNestedData($data, 'foo->bar->baz')
        );
    }
}
