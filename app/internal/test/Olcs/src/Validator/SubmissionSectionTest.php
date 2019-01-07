<?php

namespace OlcsTest\Validator;

use Olcs\Validator\SubmissionSection;

/**
 * Class SubmissionSection Test
 * @package OlcsTest\Validator
 */
class SubmissionSectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideIsValid
     * @param $expected
     * @param $value
     */
    public function testIsValid($value, $expected)
    {
        $sut = new SubmissionSection();

        $this->assertEquals($expected, $sut->isValid($value));
    }

    public function provideIsValid()
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
