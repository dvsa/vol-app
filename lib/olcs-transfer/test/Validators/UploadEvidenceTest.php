<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\UploadEvidence;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Upload evidence validator test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UploadEvidenceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(UploadEvidence::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    /**
     * @dataProvider isNotValidProvider
     */
    public function testIsNotValid($value, $expected, $context)
    {
        $this->sut->shouldReceive('getTranslator')
            ->andReturn(
                m::mock()
                    ->shouldReceive('translate')
                    ->with('upload_evidence_validator_please_upload_advert')
                    ->andReturn('translated')
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('setMessage')
            ->with('translated', UploadEvidence::UPLOAD_ADVERT)
            ->once()
            ->shouldReceive('error')
            ->with(UploadEvidence::UPLOAD_ADVERT)
            ->once()
            ->getMock();
        $this->assertEquals($expected, $this->sut->isValid($value, $context));
    }

    public function isNotValidProvider()
    {
        return [
            [
                '',
                false,
                [
                    'adPlacedIn' => 'foo'
                ]
            ],
            [
                '',
                false,
                [
                    'adPlacedIn' => 'foo',
                    'file'
                ]
            ],
            [
                '',
                false,
                [
                    'adPlacedIn' => 'foo',
                    'file' => ['list']
                ]
            ],
            [
                '',
                false,
                [
                    'adPlacedDate' => ['day' => '1']
                ]
            ],
            [
                '',
                false,
                [
                    'adPlacedDate' => ['month' => '1']
                ]
            ],
            [
                '',
                false,
                [
                    'adPlacedDate' => ['year' => '2000']
                ]
            ],
        ];
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $expected, $context)
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $context));
    }

    public function isValidProvider()
    {
        return [
            [
                '',
                true,
                [
                    'adPlacedIn' => 'foo',
                    'file' => ['list' => ['bar']]
                ]
            ],
            [
                '',
                true,
                []
            ],
        ];
    }
}
