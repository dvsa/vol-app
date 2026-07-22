<?php

/**
 * Test no of permits max validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\NoOfPermitsMax;
use Laminas\Validator\LessThan;

/**
 * Test No of permits max validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class NoOfPermitsMaxTest extends \PHPUnit\Framework\TestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new NoOfPermitsMax(15);
    }

    /**
     * Test isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($value, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, null));
    }

    public function testMessageTemplates(): void
    {
        $expectedValue = [
            LessThan::NOT_LESS_INCLUSIVE => 'permits.page.no-of-permits.error.max-exceeded'
        ];

        $this->assertEquals(
            $expectedValue,
            $this->validator->getMessageTemplates()
        );
    }

    /**
     * Provider for isValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function providerIsValid(): \Iterator
    {
        yield [14, true];
        yield [15, true];
        yield [16, false];
        yield [0, true];
        yield [7, true];
        yield [70, false];
    }
}
