<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\UploadEvidence;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Upload evidence validator test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UploadEvidenceTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = m::mock(UploadEvidence::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isNotValidProvider')]
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

    public static function isNotValidProvider(): \Iterator
    {
        yield [
            '',
            false,
            [
                'adPlacedIn' => 'foo'
            ]
        ];
        yield [
            '',
            false,
            [
                'adPlacedIn' => 'foo',
                'file'
            ]
        ];
        yield [
            '',
            false,
            [
                'adPlacedIn' => 'foo',
                'file' => ['list']
            ]
        ];
        yield [
            '',
            false,
            [
                'adPlacedDate' => ['day' => '1']
            ]
        ];
        yield [
            '',
            false,
            [
                'adPlacedDate' => ['month' => '1']
            ]
        ];
        yield [
            '',
            false,
            [
                'adPlacedDate' => ['year' => '2000']
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected, $context)
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $context));
    }

    public static function isValidProvider(): \Iterator
    {
        yield [
            '',
            true,
            [
                'adPlacedIn' => 'foo',
                'file' => ['list' => ['bar']]
            ]
        ];
        yield [
            '',
            true,
            []
        ];
    }
}
