<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Comment;

/**
 * Class CommentTest
 * @package CommonTest\Service\Table\Formatter
 */
class CommentTest extends \PHPUnit\Framework\TestCase
{
    public function testFormat(): void
    {
        $sut = new Comment();
        $result = $sut->format(
            ['statusField' => "Test \nnote"],
            ['name' => 'statusField', 'formatter' => 'comment']
        );

        $this->assertEquals("Test <br />\nnote", $result);

        // test empty comment
        $result = $sut->format(
            ['statusField' => null],
            ['name' => 'statusField', 'formatter' => 'comment']
        );

        $this->assertEquals('', $result);

        // test comment with maxlength
        $result = $sut->format(
            ['statusField' =>  "Test \nnote"],
            ['name' => 'statusField', 'formatter' => 'comment', 'maxlength' => 8]
        );

        $this->assertEquals("Test <br />\nno...", $result);

        // test comment with maxlength and custom append
        $result = $sut->format(
            ['statusField' =>  "Test \nnote"],
            ['name' => 'statusField', 'formatter' => 'comment', 'maxlength' => 8, 'append' => '[cont]']
        );

        $this->assertEquals("Test <br />\nno[cont]", $result);
    }
}
