<?php

namespace Dvsa\OlcsTest\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\TranslationKey\GetList
 */
class GetListTest extends \PHPUnit\Framework\TestCase
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
        static::assertEquals(
            [
                'translationSearch' => 'search term',
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'sortWhitelist' => []
            ],
            $sut->getArrayCopy()
        );
    }
}
