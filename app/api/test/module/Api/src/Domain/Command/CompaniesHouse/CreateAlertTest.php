<?php

declare(strict_types=1);

/**
 * Companies House CreateAlert command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\CreateAlert;

/**
 * Companies House CreateAlert command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateAlertTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CreateAlert::create(
            [
                'companyNumber' => '01234567',
                'reasons' => [
                    'foo',
                    'bar',
                ]
            ]
        );

        $this->assertEquals('01234567', $command->getCompanyNumber());
        $this->assertEquals(['foo', 'bar'], $command->getReasons());
        $this->assertEquals(
            [
                'companyNumber' => '01234567',
                'reasons' => [
                    'foo',
                    'bar',
                ],
            ],
            $command->getArrayCopy()
        );
    }
}
