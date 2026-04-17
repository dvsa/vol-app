<?php

declare(strict_types=1);

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

    public function setUp(): void
    {
        $this->sut = new SurrenderDetails();
        parent::setUp();
    }

    public function testInvoke(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetDeclarationSignatureText')]
    public function testGetDeclarationSignatureText(mixed $surrenderData, mixed $expectedText): void
    {
        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->getDeclarationSignatureText()
        );
    }

    public static function dpTestGetDeclarationSignatureText(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestReturnCommunityLicenceDocumentDetailsText')]
    public function testReturnCommunityLicenceDocumentDetailsText(mixed $surrenderData, mixed $expectedText): void
    {
        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->returnCommunityLicenceDocumentDetailsText()
        );
    }

    public static function dpTestReturnCommunityLicenceDocumentDetailsText(): array
    {
        return [
            [
                'surrenderData' => [
                    'communityLicenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_STOLEN
                    ],
                ],
                'expectedText' => 'Details of stolen community licence document'
            ],
            [
                'surrenderData' => [
                    'communityLicenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_LOST
                    ],
                ],
                'expectedText' => 'Details of lost community licence document'
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestReturnLicenceDocumentDetailsText')]
    public function testReturnLicenceDocumentDetailsText(mixed $surrenderData, mixed $expectedText): void
    {
        $this->assertSame(
            $expectedText,
            $this->sut->__invoke($surrenderData)->returnLicenceDocumentDetailsText()
        );
    }

    public static function dpTestReturnLicenceDocumentDetailsText(): array
    {
        return [
            [
                'surrenderData' => [
                    'licenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_STOLEN
                    ],
                ],
                'expectedText' => 'Details of stolen operator licence document'
            ],
            [
                'surrenderData' => [
                    'licenceDocumentStatus' => [
                        'id' => RefData::SURRENDER_DOC_STATUS_LOST
                    ],
                ],
                'expectedText' => 'Details of lost operator licence document'
            ]
        ];
    }
}
