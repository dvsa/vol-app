<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Query\Util;

use Dvsa\Olcs\Cli\Domain\Query\Util\GetDbValue;

class GetDbValueTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $parameters = [
            'entityName' => 'Application\Application',
            'propertyName' => 'isVariation',
            'filterProperty' => 'id',
            'filterValue' => '1'
        ];

        $command = GetDbValue::create($parameters);

        $this->assertEquals($parameters['entityName'], $command->getEntityName());
        $this->assertEquals($parameters['propertyName'], $command->getPropertyName());
        $this->assertEquals($parameters['filterProperty'], $command->getFilterProperty());
        $this->assertEquals($parameters['filterValue'], $command->getFilterValue());
    }
}
