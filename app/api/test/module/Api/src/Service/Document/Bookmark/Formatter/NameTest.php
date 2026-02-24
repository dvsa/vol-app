<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Name;

/**
 * Name formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class NameTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('nameProvider')]
    public function testFormat(mixed $input, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            Name::format($input)
        );
    }

    public static function nameProvider(): array
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
