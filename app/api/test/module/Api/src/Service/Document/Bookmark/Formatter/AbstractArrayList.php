<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\FormatterInterface;

/**
 * AbstractArrayList extend this class to easily test formatters based on the abstract
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AbstractArrayList extends \PHPUnit\Framework\TestCase
{
    public const SUT_CLASS_NAME = '\Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\FORMATTER_CLASS_NAME';
    public const ARRAY_FIELD = '';
    public const EXPECTED_OUTPUT = '(3, abc, 2)'; //allows differing format to be configured for each

    /**
     * @dataProvider dpTestFormat
     */
    public function testFormat($input, $expected)
    {
        $class = static::SUT_CLASS_NAME;

        /** @var FormatterInterface $formatter */
        $formatter = new $class();

        $this->assertEquals($expected, $formatter::format($input));
    }

    /**
     * @return array
     */
    public function dpTestFormat()
    {
        return [
            [
                [],
                ''
            ],
            [
                [
                    0 => [
                        static::ARRAY_FIELD => 3
                    ],
                    1 => [
                        static::ARRAY_FIELD => 'abc'
                    ],
                    2 => [
                        static::ARRAY_FIELD => '2'
                    ]
                ],
                static::EXPECTED_OUTPUT
            ],
        ];
    }
}
