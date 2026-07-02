<?php

/**
 * Stack Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\StackHelperService;

/**
 * Stack Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StackHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Holds the SUT
     *
     * @var \Common\Service\Helper\StackHelperService
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new StackHelperService();
    }

    /**
     * @dataProvider providerGetStackValue
     */
    public function testGetStackValue($stack, $stackReference, $expected): void
    {
        $this->assertEquals($expected, $this->sut->getStackValue($stack, $stackReference));
    }

    /**
     * @return (((string|string[])[]|string)[]|null|string)[][]
     *
     * @psalm-return array{'top level': list{array{foo: 'bar'}, list{'foo'}, 'bar'}, 'nested top level': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo'}, array{bar: array{cake: 'baz'}}}, 'nested mid level': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'bar'}, array{cake: 'baz'}}, 'nested deepest level': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'bar', 'cake'}, 'baz'}, 'missing reference 1': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'bar', 'cake', 'foo'}, null}, 'missing reference 2': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'baz', 'cake', 'foo'}, null}, 'missing reference 3': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'baz'}, null}}
     */
    public function providerGetStackValue(): array
    {
        return [
            'top level' => [
                ['foo' => 'bar'],
                ['foo'],
                'bar'
            ],
            'nested top level' => [
                ['foo' => ['bar' => ['cake' => 'baz']]],
                ['foo'],
                ['bar' => ['cake' => 'baz']]
            ],
            'nested mid level' => [
                ['foo' => ['bar' => ['cake' => 'baz']]],
                ['foo', 'bar'],
                ['cake' => 'baz']
            ],
            'nested deepest level' => [
                ['foo' => ['bar' => ['cake' => 'baz']]],
                ['foo', 'bar', 'cake'],
                'baz'
            ],
            'missing reference 1' => [
                ['foo' => ['bar' => ['cake' => 'baz']]],
                ['foo', 'bar', 'cake', 'foo'],
                null
            ],
            'missing reference 2' => [
                ['foo' => ['bar' => ['cake' => 'baz']]],
                ['foo', 'baz', 'cake', 'foo'],
                null
            ],
            'missing reference 3' => [
                ['foo' => ['bar' => ['cake' => 'baz']]],
                ['foo', 'baz'],
                null
            ]
        ];
    }
}
