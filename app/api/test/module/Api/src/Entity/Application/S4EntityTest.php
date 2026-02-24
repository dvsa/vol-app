<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Application\S4
 * @covers Dvsa\Olcs\Api\Entity\Application\AbstractS4
 */
class S4EntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity\Application\S4::class;

    public function testConstrunctor(): void
    {
        /** @var Entity\Application\Application $mockApp */
        $mockApp = m::mock(Entity\Application\Application::class);
        /** @var Entity\Licence\Licence $mockLic */
        $mockLic = m::mock(Entity\Licence\Licence::class);

        $sut = new Entity\Application\S4($mockApp, $mockLic);

        static::assertSame($mockApp, $sut->getApplication());
        static::assertSame($mockLic, $sut->getLicence());
        static::assertInstanceOf(ArrayCollection::class, $sut->getAocs());
    }
}
