<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\YesNoTableRequiredValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class YesNoTableRequiredValidatorTest extends MockeryTestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new YesNoTableRequiredValidator([
            'table' => 'testTable',
            'message' => 'testMessage'
        ]);
    }

    /**
     * Test isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, $context));
    }

    /**
     * Provider for isValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function providerIsValid(): \Iterator
    {
        yield [
            'Y',
            ['testTable' => ['rows' => 1]],
            true
        ];
        yield [
            'Y',
            ['testTable' => ['rows' => 0]],
            false
        ];
        yield [
            'N',
            ['testTable' => ['rows' => 1]],
            true
        ];
        yield [
            'N',
            ['testTable' => ['rows' => 0]],
            true
        ];
    }
}
