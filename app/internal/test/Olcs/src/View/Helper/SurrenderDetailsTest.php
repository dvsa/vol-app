<?php

namespace OlcsTest\View\Helper;

use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Helper\SurrenderDetails;

class SurrenderDetailsTest extends MockeryTestCase
{
    /**
     * @var SurrenderDetails
     */
    protected $sut;

    public function setUp()
    {
        $surrender = [
            'id' => 4,
            'status' => RefData::SURRENDER_STATUS_SIGNED,
            'signatureType' => [
                'id' => RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE
            ]
        ];

        $this->sut = new SurrenderDetails();
        parent::setUp();
    }

    public function testInvoke()
    {
        $surrenderData = [
            'id' => 4,
            'status' => RefData::SURRENDER_STATUS_SIGNED,
            'signatureType' => [
                'id' => RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE
            ]
        ];

        $this->assertInstanceOf(SurrenderDetails::class, $this->sut->__invoke($surrenderData));
    }

    /**
     * @dataProvider dpTestGetDeclarationSignatureText
     */
    public function testGetDeclarationSignatureText($sigType, $expectedText)
    {
        $attributes = [
            'firstname' => 'SomeFirstName',
            'lastname' => 'SomeLastName'
        ];

        $surrenderData = [
            'id' => 4,
            'status' => RefData::SURRENDER_STATUS_SIGNED,
            'signatureType' => [
                'id' => $sigType
            ],
            'digitalSignature' => [
                'createdOn' => '01/01/2014',
                'attributes' => json_encode($attributes)
            ]

        ];

        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->getDeclarationSignatureText()
        );
    }

    public function dpTestGetDeclarationSignatureText()
    {
        return [
            'digital_signature' => [
                'sigType' => RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE,
                'expectedText' => 'Digitally signed by SomeFirstName SomeLastName at 1 Jan 2014'
            ],
            'physical_signature' => [
                'sigType' => RefData::SIGNATURE_TYPE_PHYSICAL_SIGNATURE,
                'expectedText' => 'Physical signature'
            ]
        ];
    }
}
