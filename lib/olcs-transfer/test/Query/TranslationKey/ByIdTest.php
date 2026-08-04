<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\Query\TranslationKey\ById;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\TranslationKey\ById::class)]
final class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
              'id' => 2
            ]
        );
        $this->assertEquals([
        'id' => 2
        ], $sut->getArrayCopy());
    }
}
