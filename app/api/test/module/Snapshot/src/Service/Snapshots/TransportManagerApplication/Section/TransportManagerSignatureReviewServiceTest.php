<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerSignatureReviewService;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * TransportManagerSignatureReviewServiceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerSignatureReviewServiceTest extends MockeryTestCase
{
    /** @var TransportManagerSignatureReviewService */
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new TransportManagerSignatureReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getConfigProvider')]
    public function testGetConfig(mixed $data, mixed $expected): void
    {
        $this->mockTranslator
            ->shouldReceive('translate')
            ->with(TransportManagerSignatureReviewService::ADDRESS)
            ->once()
            ->andReturn('ADDRESS');

        $this->mockTranslator->shouldReceive('translate')->with($expected['label'])->once()
            ->andReturn($expected['label'] . 'translated');

        if (empty($data['TmDigitalSignature'])) {
            $this->mockTranslator->shouldReceive('translate')->with($expected['markup'])->once()->
            andReturn(
                '%s_%s'
            );
        }

        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getTmDigitalSignature')->andReturn($data['TmDigitalSignature']);

        $tma->shouldReceive('getOpDigitalSignature')->twice()->andReturn($data['OpDigitalSignature']);
        $tma->shouldReceive('getIsOwner')->once()->andReturn('N');

        $tma->shouldReceive('getApplication->getLicence->getOrganisation->getType->getId')->with()->once()
            ->andReturn($data['organisationType']);

        $expectedMarkup = $expected['label'] . 'translated_ADDRESS';

        if ($data['TmDigitalSignature']) {
            $digitalSignatureDate = new DateTime('2018-01-01');
            $birthDate = (new DateTime('1980-01-01'))->format('d M Y');
            $signatureName = 'Name';

            $this->mockTranslator
                ->shouldReceive('translate')
                ->with(TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_BOTH)
                ->once()
                ->andReturn('%s_%s_%s_%s_%s_%s_%s');

            $expectedMarkup = $signatureName .
                '_' . $birthDate .
                '_' . $digitalSignatureDate->format('d M Y') .
                '_' . $expected['label'] . 'translated' .
                '_' . $signatureName .
                '_' . $birthDate .
                '_' . $digitalSignatureDate->format('d M Y');
        }

        $this->assertEquals(['markup' => $expectedMarkup], $this->sut->getConfig($tma));
    }

    public static function getConfigProvider(): array
    {

        $digitalSignature = m::mock(DigitalSignature::class);
        $digitalSignature->shouldReceive('getSignatureName')->andReturn('Name');
        $digitalSignature->shouldReceive('getDateOfBirth')->andReturn('01 Jan 1980');
        $digitalSignatureDate = new DateTime('2018-01-01');
        $digitalSignature->shouldReceive('getCreatedOn')->with(true)->andReturn($digitalSignatureDate);

        return [
            'case_01' => [
                [
                    'organisationType' => 'unknown',
                    'TmDigitalSignature' => null,
                    'OpDigitalSignature' => null,
                ],
                [
                    'label' => 'responsible-person-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_02' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_LLP,
                    'TmDigitalSignature' => null,
                    'OpDigitalSignature' => null,
                ],
                [
                    'label' => 'directors-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_03' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_REGISTERED_COMPANY,
                    'TmDigitalSignature' => null,
                    'OpDigitalSignature' => null
                ],
                [
                    'label' => 'directors-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_04' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_PARTNERSHIP,
                    'TmDigitalSignature' => null,
                    'OpDigitalSignature' => null
                ],
                [
                    'label' => 'partners-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_05' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_SOLE_TRADER,
                    'TmDigitalSignature' => null,
                    'OpDigitalSignature' => null
                ],
                [
                    'label' => 'owners-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_06' => [
                [
                    'organisationType' => 'unknown',
                    'TmDigitalSignature' => $digitalSignature,
                    'OpDigitalSignature' => $digitalSignature
                ],
                [
                    'label' => 'responsible-person-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_BOTH
                ]
            ],
        ];
    }

    /**
     * testAppropriateTemplateUsedForDigitalSignatures
     *
     *
     * @param $conditions
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('digitalSignatureDataProvider')]
    public function notestAppropriateTemplateUsedForDigitalSignatures(mixed $conditions, mixed $expected): void
    {
        $this->mockTranslator
            ->shouldReceive('translate')
            ->with(TransportManagerSignatureReviewService::ADDRESS)
            ->once()
            ->andReturn('ADDRESS');

        $this->mockTranslator->shouldReceive('translate')->with($expected['label'])->once()
            ->andReturn($expected['label'] . 'translated');

        $tma = m::mock(TransportManagerApplication::class);

        $tma->shouldReceive('getOpDigitalSignature')->times($conditions['opTimes'])->andReturn($conditions['OpSignature']);
        $tma->shouldReceive('getIsOwner')->once()->andReturn($conditions['isOwner']);
        $tma->shouldReceive('getTmDigitalSignature')->times($conditions['tmTimes'])->andReturn($conditions['tmSignature']);
        $tma->shouldReceive('getApplication->getLicence->getOrganisation->getType->getId')->with()->once()
            ->andReturn(Organisation::ORG_TYPE_SOLE_TRADER);

        $this->mockTranslator
            ->shouldReceive('translate')
            ->with($conditions['markup'])
            ->once()
            ->andReturn($expected['replacements']);

        $actual = $this->sut->getConfig($tma);
        $this->assertEquals([
            'markup' => $expected['markup']
        ], $actual);
    }

    public static function digitalSignatureDataProvider(): array
    {
        $opDigitalSignature = m::mock(DigitalSignature::class);
        $opDigitalSignature->shouldReceive('getSignatureName')->andReturn('OpName');
        $opDigitalSignature->shouldReceive('getDateOfBirth')->andReturn('OpDob');
        $opSignatureDate = new DateTime();
        $opSignatureDateFormatted = $opSignatureDate->format('d-m-Y H:i:s');
        $opDigitalSignature->shouldReceive('getCreatedOn')->with(true)->andReturn($opSignatureDate);
        $tmDigitalSignature = m::mock(DigitalSignature::class);
        $tmDigitalSignature->shouldReceive('getSignatureName')->andReturn('TmName');
        $tmDigitalSignature->shouldReceive('getDateOfBirth')->andReturn('TmDob');
        $tmSignatureDate = new DateTime();
        $tmSignatureDateFormatted = $tmSignatureDate->format('d-m-Y H:i:s');
        $tmDigitalSignature->shouldReceive('getCreatedOn')->with(true)->andReturn($tmSignatureDate);

        return [

            "Operator and TM" => [
                [
                    'OpSignature' => $opDigitalSignature,
                    'isOwner' => 'N',
                    'tmSignature' => $tmDigitalSignature,
                    "markup" => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_BOTH,
                    "tmTimes" => 2,
                    "opTimes" => 2
                ],
                [
                    'label' => 'owners-signature',
                    'markup' => 'TmName_TmDob_' . $tmSignatureDateFormatted . '_owners-signaturetranslated_OpName_OpDob_' . $opSignatureDateFormatted,
                    'replacements' => '%s_%s_%s_%s_%s_%s_%s',
                ]
            ],
            "as TM only" => [
                [
                    'OpSignature' => null,
                    'isOwner' => 'N',
                    'tmSignature' => $tmDigitalSignature,
                    "markup" => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL,
                    "tmTimes" => 2,
                    "opTimes" => 2
                ],
                [
                    'label' => 'owners-signature',
                    'replacements' => '%s_%s_%s_%s_%s',
                    'markup' => 'TmName_TmDob_' . $tmSignatureDateFormatted . '_owners-signaturetranslated_ADDRESS'
                ]
            ],
            "as OperatorTM" => [
                [
                    'OpSignature' => $opDigitalSignature,
                    'isOwner' => 'Y',
                    'tmSignature' => $tmDigitalSignature,
                    "markup" => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_OPERATOR_TM,
                    "tmTimes" => 2,
                    "opTimes" => 2
                ],
                [
                    'label' => 'owners-signature',
                    'markup' => 'OpName_OpDob_' . $opSignatureDateFormatted,
                    'replacements' => '%s_%s_%s',
                ]
            ],

        ];
    }
}
