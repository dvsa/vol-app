<?php

declare(strict_types=1);

namespace OlcsTest\Validator;

use Olcs\Validator\SubmissionSection;

/**
 * Class SubmissionSection Test
 * @package OlcsTest\Validator
 */
class SubmissionSectionTest extends \PHPUnit\Framework\TestCase
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

    public static function provideIsValid(): array
    {
        return [
            [[], false],
            ['', false],
            [false, false],
            [['submissionType' => ''], false],
            [['submissionType' => 'test'], true],
            [['submissionType' => 'hey-im-set'], true],
        ];
    }
}
