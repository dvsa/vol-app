<?php

declare(strict_types=1);

/**
 * Result Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command;

use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Result Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResultTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Result();
    }

    public function testResultId(): void
    {
        $this->sut->addId('application', 111);
        $this->sut->addId('licence', 333);
        $this->sut->addId('application', 222);

        $this->assertEquals(333, $this->sut->getId('licence'));
        $this->assertEquals(222, $this->sut->getId('application'));

        $this->assertEquals(['application' => 222, 'licence' => 333], $this->sut->getIds());
    }

    public function testResultIdMultiple(): void
    {
        $this->sut->addId('application', 111);
        $this->sut->addId('licence', 333);
        $this->sut->addId('application', 222, true);

        $this->assertEquals(333, $this->sut->getId('licence'));
        $this->assertEquals([111, 222], $this->sut->getId('application'));

        $this->assertEquals(['application' => [111, 222], 'licence' => 333], $this->sut->getIds());
    }

    public function testResultMessage(): void
    {
        $this->sut->addMessage('foo');
        $this->sut->addMessage('bar');

        $this->assertEquals(['foo', 'bar'], $this->sut->getMessages());
    }

    public function testToArray(): void
    {
        $this->sut->addId('foo', 111);
        $this->sut->addId('bar', 222);
        $this->sut->addId('baz', 333, true);
        $this->sut->addId('baz', 444, true);
        $this->sut->addMessage('foo was successful');
        $this->sut->addMessage('bar failed');
        $this->sut->setFlag('foo', 'bar');

        $expected = [
            'id' => ['foo' => 111, 'bar' => 222, 'baz' => [333, 444]],
            'messages' => ['foo was successful', 'bar failed'],
            'flags' => ['foo' => 'bar']
        ];

        $this->assertEquals($expected, $this->sut->toArray());
    }

    public function testMerge(): void
    {
        $this->sut->addId('foo', 111);
        $this->sut->addId('bar', 222);
        $this->sut->addMessage('foo was successful');
        $this->sut->addMessage('bar failed');
        $this->sut->setFlag('foo', 'bar');

        $result = new Result();
        $result->addId('foo', 333);
        $result->addMessage('foo was updated');
        $result->setFlag('cake', 'buz');

        $this->sut->merge($result);

        $expected = [
            'id' => ['foo' => 333, 'bar' => 222],
            'messages' => ['foo was successful', 'bar failed', 'foo was updated'],
            'flags' => ['foo' => 'bar', 'cake' => 'buz']
        ];

        $this->assertEquals($expected, $this->sut->toArray());
    }

    public function testMergeRecursive(): void
    {
        $this->sut->addId('foo', 111);
        $this->sut->addId('bar', 222);
        $this->sut->addMessage('foo was successful');
        $this->sut->addMessage('bar failed');
        $this->sut->setFlag('foo', 'bar');

        $result = new Result();
        $result->addId('foo', 333);
        $result->addMessage('foo was updated');
        $result->setFlag('cake', 'buz');

        $this->sut->merge($result, true);

        $expected = [
            'id' => ['foo' => [111, 333], 'bar' => 222],
            'messages' => ['foo was successful', 'bar failed', 'foo was updated'],
            'flags' => ['foo' => 'bar', 'cake' => 'buz']
        ];

        $this->assertEquals($expected, $this->sut->toArray());
    }
}
