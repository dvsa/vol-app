<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\TranslationKey\GetList::class)]
final class GetListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = GetList::create(
            [
                'translationSearch' => 'search term',
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );
        $this->assertEquals([
            'translationSearch' => 'search term',
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => []
        ], $sut->getArrayCopy());
    }
}
