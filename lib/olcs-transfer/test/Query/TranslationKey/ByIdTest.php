<?php

namespace Dvsa\OlcsTest\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\Query\TranslationKey\ById;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\TranslationKey\ById
 */


class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
              'id' => 2
            ]
        );
        static::assertEquals(
            [
            'id' => 2
            ],
            $sut->getArrayCopy()
        );
    }
}
