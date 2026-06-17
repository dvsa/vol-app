<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\QuestionAnswer;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\QuestionAnswer
 */

class QuestionAnswerTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = QuestionAnswer::create(
            [
              'id' => 1,
            ]
        );
        $this->assertEquals(1, $sut->getId());
        $this->assertEquals(
            [
                'id' => 1,
            ],
            $sut->getArrayCopy()
        );
    }
}
