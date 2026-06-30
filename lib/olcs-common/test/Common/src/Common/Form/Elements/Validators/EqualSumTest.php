<?php

/**
 * Equal Sum Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\EqualSum;

/**
 * Equal Sum Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class EqualSumTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function testIsValidWhenValid(): void
    {
        $options = [
            'errorPrefix' => 'prefix',
            'fields' => [
                'foo',
                'bar'
            ]
        ];
        $value = 10;
        $context = [
            'foo' => 5,
            'bar' => 5
        ];

        $this->sut = new EqualSum($options);

        $this->assertTrue($this->sut->isValid($value, $context));

        $this->assertEmpty($this->sut->getMessages());
    }

    public function testIsValidWhenInValid(): void
    {
        $options = [
            'errorPrefix' => 'prefix-',
            'fields' => [
                'foo',
                'bar'
            ]
        ];
        $value = 10;
        $context = [
            'foo' => 5,
            'bar' => 6
        ];

        $this->sut = new EqualSum($options);

        $this->assertFalse($this->sut->isValid($value, $context));

        $messages = $this->sut->getMessages();

        $this->assertCount(1, $messages);

        $this->assertEquals('prefix-foo-bar', current($messages));
    }

    public function testIsValidWhenInValidWithMissingContext(): void
    {
        $options = [
            'errorPrefix' => 'prefix-',
            'fields' => [
                'foo',
                'bar'
            ]
        ];
        $value = 10;
        $context = [
            'foo' => 5
        ];

        $this->sut = new EqualSum($options);

        $this->assertFalse($this->sut->isValid($value, $context));

        $messages = $this->sut->getMessages();

        $this->assertCount(1, $messages);

        $this->assertEquals('prefix-foo', current($messages));
    }
}
