<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo
 */
class FhAdditionalInfoTest extends MockeryTestCase
{
    /** @var FhAdditionalInfo */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new FhAdditionalInfo();
    }

    /**
     * @dataProvider dpTestIsValid
     */
    public function testIsValid($context, $value, $expect, $errMsg = null)
    {
        static::assertEquals($expect, $this->sut->isValid($value, $context));

        if ($errMsg !== null) {
            static::assertEquals($errMsg, $this->sut->getMessages());
        }
    }

    public function dpTestIsValid()
    {
        return [
            'not need details text' => [
                'context' => [
                    'bankrupt' => 'N',
                    'liquidation' => 'N',
                ],
                'value' => '',
                'expect' => true,
            ],
            'not need details text is empty' => [
                'context' => [
                    'bankrupt' => 'N',
                    'liquidation' => 'Y',
                ],
                'value' => '',
                'expect' => false,
                'errMsg' => [
                    FhAdditionalInfo::IS_EMPTY => 'FhAdditionalInfo.validation.is_empty',
                ],
            ],
            'not need details text is short' => [
                'context' => [
                    'bankrupt' => 'Y',
                    'liquidation' => 'N',
                ],
                'value' => 'to short message',
                'expect' => false,
                'errMsg' => [
                    FhAdditionalInfo::TOO_SHORT => 'FhAdditionalInfo.validation.too_short',
                ],
            ],
            '' => [
                'context' => [
                    'bankrupt' => 'Y',
                    'liquidation' => 'N',
                ],
                'value' => str_repeat('a', FhAdditionalInfo::MIN_LEN),
                'expect' => true,
            ],
        ];
    }
}
