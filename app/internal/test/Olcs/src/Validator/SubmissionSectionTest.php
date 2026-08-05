<?php

declare(strict_types=1);

namespace OlcsTest\Validator;

use Olcs\Validator\SubmissionSection;

/**
 * Class SubmissionSection Test
 * @package OlcsTest\Validator
 */
final class SubmissionSectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $expected
     * @param $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
    public function testIsValid(mixed $value, mixed $expected): void
    {
        $sut = new SubmissionSection();

        $this->assertEquals($expected, $sut->isValid($value));
    }

    public static function provideIsValid(): \Iterator
    {
        yield [[], false];
        yield ['', false];
        yield [false, false];
        yield [['submissionType' => ''], false];
        yield [['submissionType' => 'test'], true];
        yield [['submissionType' => 'hey-im-set'], true];
    }
}
