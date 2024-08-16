<?php

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

    public function testConstructor()
    {
        $licence = $this->createMock(Licence::class);
        $sut = new Entity($licence);
        $this->assertSame($licence, $sut->getLicence());
    }

    /**
     * @dataProvider dataProviderTestGetCategoryPrefix
     */
    public function testGetCategoryPrefix($expected, $goodsOrPsv)
    {
        $refData = new RefData($goodsOrPsv);
        $this->assertSame($expected, Entity::getCategoryPrefix($refData));
    }

    public function dataProviderTestGetCategoryPrefix()
    {
        return [
            ['P', Licence::LICENCE_CATEGORY_PSV],
            ['O', Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
            ['O', 'Foo'],
        ];
    }
}
