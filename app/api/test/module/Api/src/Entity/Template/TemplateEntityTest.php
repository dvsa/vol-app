<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Template;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Template\Template as Entity;
use Dvsa\Olcs\Api\Entity\Template\TemplateTestData;
use Mockery as m;
use RuntimeException;

/**
 * Template Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TemplateEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetDecodedTestData(): void
    {
        $decodedJson = [
            'Dataset 1' => [
                'var1' => 'value1',
                'var2' => 'value2'
            ]
        ];

        $templateTestData = m::mock(TemplateTestData::class);
        $templateTestData->shouldReceive('getDecodedJson')
            ->andReturn($decodedJson);

        $template = m::mock(Entity::class)->makePartial();
        $template->setTemplateTestData($templateTestData);

        $this->assertEquals(
            $decodedJson,
            $template->getDecodedTestData()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetComputedCategoryName')]
    public function testGetComputedCategoryName(mixed $categoryName, mixed $linkedCategoryEntity, mixed $expectedCategoryName): void
    {
        $template = m::mock(Entity::class)->makePartial();
        $template->setCategoryName($categoryName);
        $template->setCategory($linkedCategoryEntity);

        $this->assertEquals($expectedCategoryName, $template->getComputedCategoryName());
    }

    public static function dpTestGetComputedCategoryName(): array
    {
        $categoryName = 'Category name';
        $linkedCategoryName = 'Linked category name';

        $linkedCategoryEntity = m::mock(Category::class);
        $linkedCategoryEntity->shouldReceive('getDescription')
            ->andReturn($linkedCategoryName);

        return [
            [$categoryName, null, $categoryName],
            [null, $linkedCategoryEntity, $linkedCategoryName],
            [$categoryName, $linkedCategoryEntity, $categoryName],
        ];
    }

    public function testGetComputedCategoryNameException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid template data - category name and category id are both null');

        $template = m::mock(Entity::class)->makePartial();
        $template->setCategoryName(null);
        $template->setCategory(null);

        $template->getComputedCategoryName();
    }
}
