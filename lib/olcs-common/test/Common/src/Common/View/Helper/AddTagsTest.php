<?php

/**
 * Test AddTags view helper
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\AddTags;

/**
 * Test AddTags view helper
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class AddTagsTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideStrings')]
    public function testInvoke($input, $expected): void
    {
        $helper = new AddTags();
        $output = $helper->__invoke($input);
        $this->assertEquals($expected, $output);
    }

    /**
     * @return \Iterator<(int | string), array<string>>
    *
    * @psalm-return list{list{'no text to replace', 'no text to replace'}, list{'text to replace (if applicable)', 'text to replace <span class=js-hidden>(if applicable)</span>'}, list{'multiline to replace (if
               applicable)', 'multiline to replace <span class=js-hidden>(if applicable)</span>'}}
    */
    public static function provideStrings(): \Iterator
    {
        yield ['no text to replace', 'no text to replace'];
        yield ['text to replace (if applicable)', 'text to replace <span class=js-hidden>(if applicable)</span>'];
        yield [
            'multiline to replace (if
                applicable)',
            'multiline to replace <span class=js-hidden>(if applicable)</span>'
        ];
    }
}
