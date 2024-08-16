<?php

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion
 * @covers Dvsa\Olcs\Api\Entity\Application\AbstractApplicationCompletion
 */
class ApplicationCompletionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct()
    {
        $application = m::mock(Application::class);

        $ac = new Entity($application);

        $this->assertSame($application, $ac->getApplication());
    }

    /**
     * @dataProvider dpVariationSectionUpdated
     *
     * test variation section updated (use the two sections likely to be tested in the real world as examples)
     */
    public function testVariationSectionUpdated($status, $expected)
    {
        $entity = $this->instantiate(Entity::class);

        $entity->setTypeOfLicenceStatus($status);
        $entity->setOperatingCentresStatus($status);

        $this->assertEquals($expected, $entity->variationSectionUpdated('operatingCentres'));
        $this->assertEquals($expected, $entity->variationSectionUpdated('typeOfLicence'));
    }

    public function dpVariationSectionUpdated()
    {
        return [
            [Entity::STATUS_NOT_STARTED, false],
            [Entity::STATUS_VARIATION_REQUIRES_ATTENTION, false],
            [Entity::STATUS_VARIATION_UPDATED, true]
        ];
    }

    public function testGetCalculatedValues()
    {
        /** @var Application $mockApp */
        $mockApp = m::mock(Application::class);

        $actual = (new Entity($mockApp))->jsonSerialize();
        static::assertEquals(null, $actual['application']);
    }

    public function testIsCompleteEmpty()
    {
        $required = [];

        /** @var Entity $ac */
        $ac = $this->instantiate(Entity::class);

        $this->assertTrue($ac->isComplete($required));
    }

    public function testIsComplete()
    {
        $required = [
            'businessType'
        ];

        /** @var Entity $ac */
        $ac = $this->instantiate(Entity::class);
        $ac->setBusinessTypeStatus(Entity::STATUS_INCOMPLETE);

        $this->assertFalse($ac->isComplete($required));
    }

    public function testIsCompleteWhenComplete()
    {
        $required = [
            'businessType'
        ];

        /** @var Entity $ac */
        $ac = $this->instantiate(Entity::class);
        $ac->setBusinessTypeStatus(Entity::STATUS_COMPLETE);

        $this->assertTrue($ac->isComplete($required));
    }
}
