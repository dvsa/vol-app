<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Name;

/**
 * Name formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class NameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider nameProvider
     */
    public function testFormat($input, $expected)
    {
        $this->assertEquals(
            $expected,
            Name::format($input)
        );
    }

    public function nameProvider()
    {
        return [
            [
                [
                    'forename' => 'Forename',
                    'familyName' => 'Surname'
                ],
                'Forename Surname'
            ]
        ];
    }
}
