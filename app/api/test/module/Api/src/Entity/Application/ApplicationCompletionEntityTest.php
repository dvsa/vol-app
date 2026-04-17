<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\AbstractApplicationCompletion::class)]
class ApplicationCompletionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $application = m::mock(Application::class);

        $ac = new Entity($application);

        $this->assertSame($application, $ac->getApplication());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpVariationSectionUpdated')]
    public function testVariationSectionUpdated(mixed $status, mixed $expected): void
    {
        $entity = $this->instantiate(Entity::class);

        $entity->setTypeOfLicenceStatus($status);
        $entity->setOperatingCentresStatus($status);

        $this->assertEquals($expected, $entity->variationSectionUpdated('operatingCentres'));
        $this->assertEquals($expected, $entity->variationSectionUpdated('typeOfLicence'));
    }

    public static function dpVariationSectionUpdated(): array
    {
        return [
            [Entity::STATUS_NOT_STARTED, false],
            [Entity::STATUS_VARIATION_REQUIRES_ATTENTION, false],
            [Entity::STATUS_VARIATION_UPDATED, true]
        ];
    }

    public function testGetCalculatedValues(): void
    {
        /** @var Application $mockApp */
        $mockApp = m::mock(Application::class);

        $actual = (new Entity($mockApp))->jsonSerialize();
        static::assertEquals(null, $actual['application']);
    }

    public function testIsCompleteEmpty(): void
    {
        $required = [];

        /** @var Entity $ac */
        $ac = $this->instantiate(Entity::class);

        $this->assertTrue($ac->isComplete($required));
    }

    public function testIsComplete(): void
    {
        $required = [
            'businessType'
        ];

        /** @var Entity $ac */
        $ac = $this->instantiate(Entity::class);
        $ac->setBusinessTypeStatus(Entity::STATUS_INCOMPLETE);

        $this->assertFalse($ac->isComplete($required));
    }

    public function testIsCompleteWhenComplete(): void
    {
        $required = [
            'businessType'
        ];

        /** @var Entity $ac */
        $ac = $this->instantiate(Entity::class);
        $ac->setBusinessTypeStatus(Entity::STATUS_COMPLETE);

        $this->assertTrue($ac->isComplete($required));
    }

    public function testClearVehiclesSizeSectionsForApplication(): void
    {
        $entity = $this->instantiate(Entity::class);
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvOperateLargeStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvOperateSmallStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvSmallConditionsStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvDocumentaryEvidenceLargeStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvDocumentaryEvidenceSmallStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvMainOccupationUndertakingsStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvSmallPartWrittenStatus());
        $this->assertNotSame(Entity::STATUS_NOT_STARTED, $entity->getPsvOperateNoveltyStatus());

        $entity->clearVehiclesSizeSectionsForApplication();

        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvOperateLargeStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvOperateSmallStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvSmallConditionsStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvDocumentaryEvidenceLargeStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvDocumentaryEvidenceSmallStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvMainOccupationUndertakingsStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvSmallPartWrittenStatus());
        $this->assertSame(Entity::STATUS_NOT_STARTED, $entity->getPsvOperateNoveltyStatus());
    }
}
