<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Template;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Template\TemplateTestData as Entity;
use Mockery as m;

/**
 * TemplateTestData Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TemplateTestDataEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetDecodedJson(): void
    {
        $json = '{ "Dataset 1": { "var1": "value1", "var2": "value2" }}';

        $decodedJson = [
            'Dataset 1' => [
                'var1' => 'value1',
                'var2' => 'value2'
            ]
        ];

        $templateTestData = m::mock(Entity::class)->makePartial();
        $templateTestData->setJson($json);

        $this->assertEquals(
            $decodedJson,
            $templateTestData->getDecodedJson()
        );
    }
}
