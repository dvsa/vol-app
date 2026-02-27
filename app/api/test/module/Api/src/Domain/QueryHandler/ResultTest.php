<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\Result
 */
class ResultTest extends MockeryTestCase
{
    public function testResultWithoutEntity(): void
    {
        $sut = new Result(null);

        static::assertNull($sut->serialize());
    }

    public function testResultWithEntityAndBundle(): void
    {
        $expected = ['foo' => 'bar', 'cake' => 'bar'];

        /** @var BundleSerializableInterface $entity */
        $entity = m::mock(BundleSerializableInterface::class);

        $entity->shouldReceive('serialize')->once()->with(['blah' => 'blah'])
            ->andReturn(['foo' => 'bar', 'cake' => 'bar']);

        $result = new Result($entity, ['blah' => 'blah']);
        $this->assertEquals($expected, $result->serialize());
    }

    public function testResultWithEntityAndBundleAndData(): void
    {
        $data = [
            'cake' => [
                'subcakeA' => 'expect',
            ],
            'stuff' => 'foo',
        ];
        $expected = [
            'foo' => 'bar',
            'cake' => [
                'subcakeA' => 'expect',
                'subcakeB' => 'shouldExists',
            ],
            'stuff' => 'foo',
        ];

        /** @var BundleSerializableInterface $entity */
        $entity = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->with(['blah' => 'blah'])
            ->andReturn(
                [
                    'foo' => 'bar',
                    'cake' => [
                        'subcakeA' => 'replaceMe',
                        'subcakeB' => 'shouldExists',
                    ],
                ]
            )
            ->getMock();

        $result = new Result($entity, ['blah' => 'blah'], $data);
        $this->assertEquals($expected, $result->serialize());
    }

    public function testResultWithEntityAndBundleAndDataSetValue(): void
    {
        $data = ['cake' => 'mix', 'stuff' => 'foo'];
        $expected = ['foo' => 'bar', 'cake' => 'choc', 'stuff' => 'foo'];

        /** @var BundleSerializableInterface $entity */
        $entity = m::mock(BundleSerializableInterface::class);

        $entity->shouldReceive('serialize')->once()->with(['blah' => 'blah'])
            ->andReturn(['foo' => 'bar', 'cake' => 'bar']);

        $result = new Result($entity, ['blah' => 'blah'], $data);
        $result->setValue('cake', 'choc');
        $this->assertEquals($expected, $result->serialize());
    }

    public function testResult(): void
    {
        $result = new Result(m::mock(BundleSerializableInterface::class));
        $this->assertFalse($result->isEmpty());
    }
}
