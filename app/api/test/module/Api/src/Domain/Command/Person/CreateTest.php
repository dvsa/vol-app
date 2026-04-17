<?php

declare(strict_types=1);

/**
 * Create Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Person;

use Dvsa\Olcs\Api\Domain\Command\Person\Create;

/**
 * Create Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = Create::create(
            [
                'firstName' => 'fn',
                'lastName' => 'ln',
                'title' => 'mr',
                'birthDate' => '2015-01-01',
                'birthPlace' => 'bp'
            ]
        );

        $this->assertEquals('fn', $command->getFirstName());
        $this->assertEquals('ln', $command->getLastName());
        $this->assertEquals('mr', $command->getTitle());
        $this->assertEquals('2015-01-01', $command->getBirthDate());
        $this->assertEquals('bp', $command->getBirthPlace());
    }
}
