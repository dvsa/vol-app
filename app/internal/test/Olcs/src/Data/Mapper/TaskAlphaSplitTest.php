<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TaskAlphaSplit as Sut;

/**
 * TaskAlphaSplitTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAlphaSplitTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $data = [
            'taskAllocationRule' => 1404,
            'id' => 1404,
            'version' => 33,
            'user' => 'USER',
            'letters' => 'LETTERS',
        ];
        $expected = [
            'taskAllocationRule' => 1404,
            'taskAlphaSplit' => [
                'id' => 1404,
                'version' => 33,
                'user' => 'USER',
                'letters' => 'LETTERS',
            ]
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromResultNew(): void
    {
        $data = [
            'taskAllocationRule' => 1404,
        ];
        $expected = [
            'taskAllocationRule' => 1404,
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromForm(): void
    {
        $data = [
            'taskAllocationRule' => 1404,
            'taskAlphaSplit' => [
                'id' => 1404,
                'version' => 33,
                'user' => 'USER',
                'letters' => 'LETTERS',
            ]
        ];
        $expected = [
            'taskAllocationRule' => 1404,
            'id' => 1404,
            'version' => 33,
            'user' => 'USER',
            'letters' => 'LETTERS',
        ];
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }
}
