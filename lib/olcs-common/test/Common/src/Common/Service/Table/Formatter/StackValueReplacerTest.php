<?php

/**
 * StackValueReplacer formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\StackValueReplacer;

/**
 * StackValueReplacer formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class StackValueReplacerTest extends \PHPUnit\Framework\TestCase
{
    public function testFormat(): void
    {
        $data = [
            'foo' => [
                'bar' => [
                    'cake' => 123
                ],
                'carrot' => 'cake'
            ]
        ];
        $column = [
            'stringFormat' => '{foo->bar->cake} {foo->carrot}(s)'
        ];
        $expected = '123 cake(s)';

        $this->assertEquals($expected, new StackValueReplacer(new StackValue(new StackHelperService()))->format($data, $column));
    }
}
