<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\PublicHoliday as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * @covers Dvsa\Olcs\Api\Entity\System\PublicHoliday
 * @covers Dvsa\Olcs\Api\Entity\System\AbstractPublicHoliday
 */
final class PublicHolidayEntityTest extends EntityTester
{
    protected $entityClass = Entity::class;

    public function testCreate(): void
    {
        $expectDate = new DateTime();
        $isEngland = 'Y';
        $isWales = 'N';
        $isScotland = 'Y';
        $isNi = 'Y';

        $entity = new Entity($expectDate, $isEngland, $isWales, $isScotland, $isNi);

        $this->assertEquals($expectDate, $entity->getPublicHolidayDate());
        $this->assertEquals('Y', $entity->getIsEngland());
        $this->assertEquals('N', $entity->getIsWales());
        $this->assertEquals('Y', $entity->getIsScotland());
        $this->assertEquals('Y', $entity->getIsNi());
    }
}
