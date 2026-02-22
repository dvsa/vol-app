<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CompaniesHouse;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseOfficer as Entity;

/**
 * CompaniesHouseOfficer Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CompaniesHouseOfficerEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Any extra date properties to be tested.
     *
     * @var array
     */
    protected $extraDateProperties = [
       'dateOfBirth'
    ];
}
