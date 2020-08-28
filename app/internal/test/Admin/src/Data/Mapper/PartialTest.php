<?php

namespace AdminTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Data\Mapper\Partial as Sut;

/**
 * Partial Mapper Test
 */
class PartialTest extends MockeryTestCase
{
    /**
     * @dataProvider fromFormProvider
     */
    public function testMapFromForm($params, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($params));
    }

    public function fromFormProvider()
    {
        return [
            [
                [
                    'fields' => [
                        'id' => 44,
                        'translationsArray' => ['en_GB' => 'this', 'cy_GB' => 'that']
                    ],
                ],
                [
                    'id' => 44,
                    'translationsArray' => ['en_GB' => base64_encode('this'), 'cy_GB' => base64_encode('that')]
                ]
            ]
        ];
    }
}
