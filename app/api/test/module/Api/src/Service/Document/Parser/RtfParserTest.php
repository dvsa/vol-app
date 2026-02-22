<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Parser;

use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;

/**
 * RTF parser test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class RtfParserTest extends \PHPUnit\Framework\TestCase
{
    public function testExtension(): void
    {
        $parser = new RtfParser();
        $this->assertEquals('rtf', $parser->getFileExtension());
    }

    public function testExtractTokens(): void
    {
        $content = <<<TXT
Bookmark 1: {\*\bkmkstart bookmark_one}{\*\bkmkend bookmark_one}
Bookmark 2: {\*\bkmkstart bookmark_two} {\*\bkmkend bookmark_two}
Bookmark 3: {\*\bkmkstart bookmark_three}
{\*\bkmkend bookmark_three}
Bookmark 4: {\*\bkmkstart bookmark_four}\tab \tab \tab {\*\bkmkend bookmark_four}
TXT;

        $parser = new RtfParser();

        $tokens = [
            'bookmark_one',
            'bookmark_two',
            'bookmark_three',
            'bookmark_four'
        ];

        $this->assertEquals($tokens, $parser->extractTokens($content));
    }

    public function testReplace(): void
    {
        $content = <<<TXT
Bookmark 1: {\*\bkmkstart bookmark_one}{\*\bkmkend bookmark_one}
Bookmark 2: {\*\bkmkstart bookmark_two} {\*\bkmkend bookmark_two}
Bookmark 3: {\*\bkmkstart bookmark_three}
{\*\bkmkend bookmark_three}
Bookmark 3 Repeat: {\*\bkmkstart bookmark_three}
{\*\bkmkend bookmark_three}
Bookmark 4: {\*\bkmkstart bookmark_four}\tab \tab \tab {\*\bkmkend bookmark_four}
Date: {\*\bkmkstart letter_date_add_14_days}
{\*\bkmkend letter_date_add_14_days}
TXT;

        $expected = <<<TXT
Bookmark 1: Some Content\par With newlines
Bookmark 2: {\*\bkmkstart bookmark_two} {\*\bkmkend bookmark_two}
Bookmark 3: Three
Bookmark 3 Repeat: Three
Bookmark 4: Four
Date: Today
TXT;

        $parser = new RtfParser();

        $data = [
            "bookmark_one" => "Some Content\nWith newlines",
            "bookmark_three" => "Three",
            "bookmark_four" => "Four",
            "letter_date_add_14_days" => "Today"
        ];

        $this->assertEquals(
            $expected,
            $parser->replace($content, $data)
        );
    }

    public function testReplaceWhenDataIsPreformatted(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart bookmark_one}{\*\bkmkend bookmark_one}";
        $expected = "Bookmark 1: Some Content\nWith newlines";

        $parser = new RtfParser();

        $data = [
            "bookmark_one" => [
                "content" => "Some Content\nWith newlines",
                "preformatted" => true
            ]
        ];

        $this->assertEquals(
            $expected,
            $parser->replace($content, $data)
        );
    }

    public function testRenderImage(): void
    {
        $parser = new RtfParser();
        $result = $parser->renderImage('', 100, 50, 'jpeg');
        $this->assertEquals(
            "{\pict\jpegblip\picw100\pich50\picwgoal1500\pichgoal750 }",
            $result
        );
    }

    public function testGetEntitiesAndQuote(): void
    {
        $startText = "a1'`~škėąčęėįšųūžĄČĘĖĮŠŲŪŽкийтехтàcôté■\{}";
        $endText = "a1'`~\uc0{\u353}k\uc0{\u279}\uc0{\u261}\uc0{\u269}\uc0{\u281}\uc0{\u279}\uc0{\u303}\uc0{\u353}\uc0"
        . "{\u371}\uc0{\u363}\uc0{\u382}\uc0{\u260}\uc0{\u268}\uc0{\u280}\uc0{\u278}\uc0{\u302}\uc0{\u352}\uc0{\u370}"
        . "\uc0{\u362}\uc0{\u381}\uc0{\u1082}\uc0{\u1080}\uc0{\u1081}\uc0{\u1090}\uc0{\u1077}\uc0{\u1093}\uc0{\u1090}"
        . "\uc0{\u224}c\uc0{\u244}t\uc0{\u233}\uc0{\u9632}\\\\\{\}";

        $parser = new RtfParser();
        $this->assertEquals($endText, $parser->getEntitiesAndQuote($startText));
    }
}
