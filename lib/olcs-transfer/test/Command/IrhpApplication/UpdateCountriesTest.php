<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;

/**
 * UpdateCountries test
 */
final class UpdateCountriesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'countries' => ['DE', 'NL'],
        ];

        $command = UpdateCountries::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['DE', 'NL'], $command->getCountries());
        $this->assertEquals(
            [
                'id' => 111,
                'countries' => ['DE', 'NL'],
            ],
            $command->getArrayCopy()
        );
    }
}
