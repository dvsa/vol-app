<?php

/**
 * Test No of permits not empty validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\NoOfPermitsNotEmpty;
use Laminas\Validator\NotEmpty;

/**
 * Test No of permits not empty validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class NoOfPermitsNotEmptyTest extends \PHPUnit\Framework\TestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new NoOfPermitsNotEmpty();
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
            NotEmpty::IS_EMPTY => 'permits.page.no-of-permits.error.general',
            NotEmpty::INVALID => 'Invalid type given. String, integer, float, boolean or array expected'
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
        yield [' ', false];
        yield ['ZYZ', true];
        yield ['40', true];
        yield ['', false];
    }
}
