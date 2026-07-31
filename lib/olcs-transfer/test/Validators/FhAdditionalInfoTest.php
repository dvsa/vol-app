<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo::class)]
final class FhAdditionalInfoTest extends MockeryTestCase
{
    /** @var FhAdditionalInfo */
    private $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new FhAdditionalInfo();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsValid')]
    public function testIsValid($context, $value, $expect, $errMsg = null)
    {
        $this->assertEquals($expect, $this->sut->isValid($value, $context));

        if ($errMsg !== null) {
            $this->assertEquals($errMsg, $this->sut->getMessages());
        }
    }

    public static function dpTestIsValid(): \Iterator
    {
        yield 'not need details text' => [
            'context' => [
                'bankrupt' => 'N',
                'liquidation' => 'N',
            ],
            'value' => '',
            'expect' => true,
        ];
        yield 'not need details text is empty' => [
            'context' => [
                'bankrupt' => 'N',
                'liquidation' => 'Y',
            ],
            'value' => '',
            'expect' => false,
            'errMsg' => [
                FhAdditionalInfo::IS_EMPTY => 'FhAdditionalInfo.validation.is_empty',
            ],
        ];
        yield 'not need details text is short' => [
            'context' => [
                'bankrupt' => 'Y',
                'liquidation' => 'N',
            ],
            'value' => 'to short message',
            'expect' => false,
            'errMsg' => [
                FhAdditionalInfo::TOO_SHORT => 'FhAdditionalInfo.validation.too_short',
            ],
        ];
        yield 'need details text meets minimum length' => [
            'context' => [
                'bankrupt' => 'Y',
                'liquidation' => 'N',
            ],
            'value' => str_repeat('a', FhAdditionalInfo::MIN_LEN),
            'expect' => true,
        ];
    }
}
