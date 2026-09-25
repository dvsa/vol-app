<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterChoice as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterChoice Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterChoiceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public static function goodsOrPsvProvider(): array
    {
        return [
            'choice for both, goods letter' => [null, 'lcat_gv', true],
            'choice for both, unknown letter' => [null, null, true],
            'goods choice, goods letter' => ['lcat_gv', 'lcat_gv', true],
            'goods choice, psv letter' => ['lcat_gv', 'lcat_psv', false],
            'goods choice, unknown letter' => ['lcat_gv', null, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('goodsOrPsvProvider')]
    public function testAppliesToGoodsOrPsv(?string $choiceSetting, ?string $letterGoodsOrPsv, bool $expected): void
    {
        $choice = new Entity();

        if ($choiceSetting !== null) {
            $refData = new RefData();
            $refData->setId($choiceSetting);
            $choice->setGoodsOrPsv($refData);
        }

        $this->assertSame($expected, $choice->appliesToGoodsOrPsv($letterGoodsOrPsv));
    }
}
