<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\TableRequiredValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class TableRequiredValidatorTest extends MockeryTestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new TableRequiredValidator();
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
        // With action
        yield [null, ['action' => 'foo', 'rows' => 0], true];
        yield [null, ['action' => 'foo', 'rows' => 1], true];
        yield [null, ['action' => 'foo', 'rows' => 10], true];
        // Without action
        yield [null, ['rows' => 0], false];
        yield [null, ['rows' => 1], true];
        yield [null, ['rows' => 10], true];
    }

    public function testGetSetRowsRequired(): void
    {
        $validator = new TableRequiredValidator(['rowsRequired' => 2]);

        $this->assertEquals(true, $validator->isValid(null, ['rows' => 2]));
        $this->assertEquals(false, $validator->isValid(null, ['rows' => 1]));
    }
}
