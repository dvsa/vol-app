<?php

/**
 * File extension formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

/**
 * File extension formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FileExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group FileExtensionFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\FileExtension())->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                ['documentStoreIdentifier' => 'foo'], [], ''
            ],
            [
                ['documentStoreIdentifier' => 'foo.txt'], [], 'TXT'
            ],
            [
                ['documentStoreIdentifier' => 'foo.bar.zip'], [], 'ZIP'
            ],
        ];
    }
}
