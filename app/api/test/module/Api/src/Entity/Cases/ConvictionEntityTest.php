<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Conviction as Entity;
use Mockery as m;

/**
 * Conviction Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ConvictionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdateConvictionCategory(): void
    {
        $refData = m::mock(RefData::class);

        $sut = new Entity();
        $sut->updateConvictionCategory($refData, null);

        $this->assertSame($refData, $sut->getConvictionCategory());
    }

    public function testUpdateConvictionCategoryError(): void
    {
        $sut = new Entity();
        $thrown = false;
        try {
            $sut->updateConvictionCategory(null, '');
        } catch (ValidationException $e) {
            $this->assertEquals(
                ['convictionCategory' => [Entity::ERROR_CON_CAT => 'You must specify a conviction category']],
                $e->getMessages()
            );
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Exception not thrown');
    }

    public function testUpdateConvictionCategoryCustom(): void
    {
        $sut = new Entity();
        $description = 'Conviction category';
        $sut->updateConvictionCategory(null, $description);

        $this->assertSame($description, $sut->getCategoryText());
    }
}
