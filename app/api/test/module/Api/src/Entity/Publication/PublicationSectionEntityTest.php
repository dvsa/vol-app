<?php

namespace Dvsa\OlcsTest\Api\Entity\Publication;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as Entity;

/**
 * PublicationSection Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PublicationSectionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @dataProvider dataProviderTestIsSection3
     *
     * @param bool $isSection3
     * @param int  $section
     */
    public function testIsSection3($isSection3, $section)
    {
        $sut = new Entity();
        $sut->setId($section);

        $this->assertSame($isSection3, $sut->isSection3());
    }

    public function dataProviderTestIsSection3()
    {
        for ($i = 1; $i < 35; $i++) {
            $params[$i] = [false, $i];
        }

        $params[Entity::SCHEDULE_1_NI_NEW] = [true, Entity::SCHEDULE_1_NI_NEW];
        $params[Entity::SCHEDULE_1_NI_TRUE] = [true, Entity::SCHEDULE_1_NI_TRUE];
        $params[Entity::SCHEDULE_1_NI_UNTRUE] = [true, Entity::SCHEDULE_1_NI_UNTRUE];
        $params[Entity::SCHEDULE_4_NEW] = [true, Entity::SCHEDULE_4_NEW];
        $params[Entity::SCHEDULE_4_TRUE] = [true, Entity::SCHEDULE_4_TRUE];
        $params[Entity::SCHEDULE_4_UNTRUE] = [true, Entity::SCHEDULE_4_UNTRUE];

        return $params;
    }

    /**
     * @dataProvider dataProviderTestIsDecision
     *
     * @param bool $isDecisionSection
     * @param int  $section
     */
    public function testIsDecision($isDecisionSection, $section)
    {
        $sut = new Entity();
        $sut->setId($section);

        $this->assertSame($isDecisionSection, $sut->isDecisionSection());
    }

    public function dataProviderTestIsDecision()
    {
        for ($i = 1; $i < 35; $i++) {
            $params[$i] = [false, $i];
        }

        $params[Entity::APP_GRANTED_SECTION] = [true, Entity::APP_GRANTED_SECTION];
        $params[Entity::APP_REFUSED_SECTION] = [true, Entity::APP_REFUSED_SECTION];
        $params[Entity::APP_WITHDRAWN_SECTION] = [true, Entity::APP_WITHDRAWN_SECTION];
        $params[Entity::APP_GRANT_NOT_TAKEN_SECTION] = [true, Entity::APP_GRANT_NOT_TAKEN_SECTION];
        $params[Entity::VAR_GRANTED_SECTION] = [true, Entity::VAR_GRANTED_SECTION];
        $params[Entity::VAR_REFUSED_SECTION] = [true, Entity::VAR_REFUSED_SECTION];

        return $params;
    }
}
