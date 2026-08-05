<?php

/**
 * File extension formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

/**
 * File extension formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class FileExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('FileExtensionFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, new \Common\Service\Table\Formatter\FileExtension()->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [
            ['documentStoreIdentifier' => 'foo'], [], ''
        ];
        yield [
            ['documentStoreIdentifier' => 'foo.txt'], [], 'TXT'
        ];
        yield [
            ['documentStoreIdentifier' => 'foo.bar.zip'], [], 'ZIP'
        ];
    }
}
