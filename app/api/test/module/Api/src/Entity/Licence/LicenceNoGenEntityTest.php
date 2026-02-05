<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\LicenceNoGen as Entity;

/**
 * LicenceNoGen Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class LicenceNoGenEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstructor(): void
    {
        $licence = $this->createStub(Licence::class);
        $sut = new Entity($licence);
        $this->assertSame($licence, $sut->getLicence());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetCategoryPrefix')]
    public function testGetCategoryPrefix(mixed $expected, mixed $goodsOrPsv): void
    {
        $refData = new RefData($goodsOrPsv);
        $this->assertSame($expected, Entity::getCategoryPrefix($refData));
    }

    public static function dataProviderTestGetCategoryPrefix(): array
    {
        return [
            ['P', Licence::LICENCE_CATEGORY_PSV],
            ['O', Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
            ['O', 'Foo'],
        ];
    }
}
