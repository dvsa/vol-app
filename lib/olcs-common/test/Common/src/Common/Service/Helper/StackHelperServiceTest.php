<?php

/**
 * Stack Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Helper;

use Common\Service\Helper\StackHelperService;

/**
 * Stack Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class StackHelperServiceTest extends \PHPUnit\Framework\TestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetStackValue')]
    public function testGetStackValue($stack, $stackReference, $expected): void
    {
        $this->assertEquals($expected, $this->sut->getStackValue($stack, $stackReference));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<(array<string> | string)> | string)> | string | null)>>
     *
     * @psalm-return array{'top level': list{array{foo: 'bar'}, list{'foo'}, 'bar'}, 'nested top level': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo'}, array{bar: array{cake: 'baz'}}}, 'nested mid level': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'bar'}, array{cake: 'baz'}}, 'nested deepest level': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'bar', 'cake'}, 'baz'}, 'missing reference 1': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'bar', 'cake', 'foo'}, null}, 'missing reference 2': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'baz', 'cake', 'foo'}, null}, 'missing reference 3': list{array{foo: array{bar: array{cake: 'baz'}}}, list{'foo', 'baz'}, null}}
     */
    public static function providerGetStackValue(): \Iterator
    {
        yield 'top level' => [
            ['foo' => 'bar'],
            ['foo'],
            'bar'
        ];
        yield 'nested top level' => [
            ['foo' => ['bar' => ['cake' => 'baz']]],
            ['foo'],
            ['bar' => ['cake' => 'baz']]
        ];
        yield 'nested mid level' => [
            ['foo' => ['bar' => ['cake' => 'baz']]],
            ['foo', 'bar'],
            ['cake' => 'baz']
        ];
        yield 'nested deepest level' => [
            ['foo' => ['bar' => ['cake' => 'baz']]],
            ['foo', 'bar', 'cake'],
            'baz'
        ];
        yield 'missing reference 1' => [
            ['foo' => ['bar' => ['cake' => 'baz']]],
            ['foo', 'bar', 'cake', 'foo'],
            null
        ];
        yield 'missing reference 2' => [
            ['foo' => ['bar' => ['cake' => 'baz']]],
            ['foo', 'baz', 'cake', 'foo'],
            null
        ];
        yield 'missing reference 3' => [
            ['foo' => ['bar' => ['cake' => 'baz']]],
            ['foo', 'baz'],
            null
        ];
    }
}
