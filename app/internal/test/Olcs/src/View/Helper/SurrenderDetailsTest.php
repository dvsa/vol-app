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
    public function testGetDeclarationSignatureText($surrenderData, $expectedText)
    {
        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->getDeclarationSignatureText()
        );
    }

    public function dpTestGetDeclarationSignatureText()
    {
        return [
            'digital_signature' => [
                'surrenderData' => [
                    'signatureType' => [
                        'id' => RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE
                    ],
                    'digitalSignature' => [
                        'createdOn' => '01/01/2014',
                        'attributes' => json_encode(
                            [
                                'firstname' => 'SomeFirstName',
                                'surname' => 'SomeLastName'
                            ]
                        )
                    ]
                ],
                'expectedText' => 'Digitally signed by SomeFirstName SomeLastName on 1 Jan 2014'
            ],
            'physical_signature' => [
                'surrenderData' => [
                    'signatureType' => [
                        'id' => RefData::SIGNATURE_TYPE_PHYSICAL_SIGNATURE
                    ],
                ],
                'expectedText' => 'Physical signature',
            ]
        ];
    }

    /**
     * @dataProvider dpTestReturnCommunityLicenceDocumentDetailsText
     */
    public function testReturnCommunityLicenceDocumentDetailsText($surrenderData, $expectedText)
    {
        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->returnCommunityLicenceDocumentDetailsText()
        );
    }

    public function dpTestReturnCommunityLicenceDocumentDetailsText()
    {
        return [
            [
                'surrenderData' => [
                    'communityLicenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_STOLEN
                    ],
                ],
                'expected' => 'Details of stolen community licence document'
            ],
            [
                'surrenderData' => [
                    'communityLicenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_LOST
                    ],
                ],
                'expected' => 'Details of lost community licence document'
            ]
        ];
    }

    /**
     * @dataProvider dpTestReturnLicenceDocumentDetailsText
     */
    public function testReturnLicenceDocumentDetailsText($surrenderData, $expectedText)
    {
        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->returnLicenceDocumentDetailsText()
        );
    }

    public function dpTestReturnLicenceDocumentDetailsText()
    {
        return [
            [
                'surrenderData' => [
                    'licenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_STOLEN
                    ],
                ],
                'expected' => 'Details of stolen operator licence document'
            ],
            [
                'surrenderData' => [
                    'licenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_LOST
                    ],
                ],
                'expected' => 'Details of lost operator licence document'
            ]
        ];
    }
}
