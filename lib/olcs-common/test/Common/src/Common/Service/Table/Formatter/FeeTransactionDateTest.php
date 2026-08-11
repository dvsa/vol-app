<?php

/**
 * Fee Transaction Date formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeTransactionDate;
use Common\Service\Table\Formatter\StackValue;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Fee Transaction Date formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class FeeTransactionDateTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new FeeTransactionDate(new StackValue(new StackHelperService()), new Date());
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $data = [
            'child' => [
                'someDate' => '2015-09-01',
            ]
        ];

        $column = [
            'stack' => 'child->someDate',
        ];

        $expected = '01/09/2015';

        $this->assertEquals($expected, $this->sut->format($data, $column));
    }
}
