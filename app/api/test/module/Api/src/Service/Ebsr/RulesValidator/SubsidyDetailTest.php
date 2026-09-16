<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\SubsidyDetail;

final class SubsidyDetailTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('authorityNamesProvider')]
    public function testValidatesCombinedAuthorityNames(array $names, bool $valid): void
    {
        $value = (new Subsidy())->filter(['subsidyAuthorityNames' => $names]);
        $sut = new SubsidyDetail();

        $this->assertSame(implode("\n", $names), $value['subsidyDetail']);
        $this->assertSame($valid, $sut->isValid($value));
        if (!$valid) {
            $this->assertArrayHasKey(SubsidyDetail::RULES_ERROR, $sut->getMessages());
        }
    }

    public static function authorityNamesProvider(): array
    {
        return [
            'absent' => [[], true],
            'previous overflow' => [[str_repeat('a', 128), str_repeat('b', 128)], true],
            'at limit' => [[str_repeat('é', 499), str_repeat('b', 500)], true],
            'over limit' => [[str_repeat('a', 500), str_repeat('b', 500)], false],
        ];
    }
}
