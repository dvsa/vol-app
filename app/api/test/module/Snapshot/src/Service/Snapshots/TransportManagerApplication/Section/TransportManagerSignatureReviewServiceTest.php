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
final class TransportManagerSignatureReviewServiceTest extends MockeryTestCase
{
    /** @var TransportManagerSignatureReviewService */
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    #[\Override]
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
        $tmDigitalSignature = $data['hasDigitalSignatures'] ? $this->createDigitalSignature() : null;
        $opDigitalSignature = $tmDigitalSignature;

        $this->mockTranslator
            ->shouldReceive('translate')
            ->with(TransportManagerSignatureReviewService::ADDRESS)
            ->once()
            ->andReturn('ADDRESS');

        $this->mockTranslator->shouldReceive('translate')->with($expected['label'])->once()
            ->andReturn($expected['label'] . 'translated');

        if (empty($tmDigitalSignature)) {
            $this->mockTranslator->shouldReceive('translate')->with($expected['markup'])->once()->
            andReturn(
                '%s_%s'
            );
        }

        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getTmDigitalSignature')->andReturn($tmDigitalSignature);

        $tma->shouldReceive('getOpDigitalSignature')->twice()->andReturn($opDigitalSignature);
        $tma->shouldReceive('getIsOwner')->once()->andReturn('N');

        $tma->shouldReceive('getApplication->getLicence->getOrganisation->getType->getId')->with()->once()
            ->andReturn($data['organisationType']);

        $expectedMarkup = $expected['label'] . 'translated_ADDRESS';

        if ($tmDigitalSignature) {
            $digitalSignatureDate = new DateTime('2018-01-01');
            $birthDate = new DateTime('1980-01-01')->format('d M Y');
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
        return [
            'case_01' => [
                [
                    'organisationType' => 'unknown',
                    'hasDigitalSignatures' => false,
                ],
                [
                    'label' => 'responsible-person-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_02' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_LLP,
                    'hasDigitalSignatures' => false,
                ],
                [
                    'label' => 'directors-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_03' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_REGISTERED_COMPANY,
                    'hasDigitalSignatures' => false,
                ],
                [
                    'label' => 'directors-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_04' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_PARTNERSHIP,
                    'hasDigitalSignatures' => false,
                ],
                [
                    'label' => 'partners-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_05' => [
                [
                    'organisationType' => Organisation::ORG_TYPE_SOLE_TRADER,
                    'hasDigitalSignatures' => false,
                ],
                [
                    'label' => 'owners-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE
                ]
            ],
            'case_06' => [
                [
                    'organisationType' => 'unknown',
                    'hasDigitalSignatures' => true,
                ],
                [
                    'label' => 'responsible-person-signature',
                    'markup' => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_BOTH
                ]
            ],
        ];
    }

    /**
     * The partial chosen for a digitally signed declaration depends on who signed: TM only, TM plus
     * operator, or an operator who is also the TM. The organisation is a sole trader throughout so the
     * owner label is constant.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('digitalSignatureDataProvider')]
    public function testAppropriateTemplateUsedForDigitalSignatures(
        bool $hasOpSignature,
        string $isOwner,
        string $partial,
        string $replacements,
        string $expectedMarkup
    ): void {
        $tmSignature = $this->createDigitalSignature('TmName', '1975-05-06', '2018-01-01');
        $opSignature = $hasOpSignature ? $this->createDigitalSignature('OpName', '1980-01-02', '2018-02-03') : null;

        $this->mockTranslator
            ->shouldReceive('translate')
            ->with(TransportManagerSignatureReviewService::ADDRESS)
            ->once()
            ->andReturn('ADDRESS');
        $this->mockTranslator
            ->shouldReceive('translate')
            ->with('owners-signature')
            ->once()
            ->andReturn('owners-signaturetranslated');
        $this->mockTranslator
            ->shouldReceive('translate')
            ->with($partial)
            ->once()
            ->andReturn($replacements);

        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getOpDigitalSignature')->twice()->andReturn($opSignature);
        $tma->shouldReceive('getIsOwner')->once()->andReturn($isOwner);
        $tma->shouldReceive('getTmDigitalSignature')->twice()->andReturn($tmSignature);
        $tma->shouldReceive('getApplication->getLicence->getOrganisation->getType->getId')->with()->once()
            ->andReturn(Organisation::ORG_TYPE_SOLE_TRADER);

        $this->assertEquals(['markup' => $expectedMarkup], $this->sut->getConfig($tma));
    }

    private function createDigitalSignature(
        string $name = 'Name',
        string $dateOfBirth = '01 Jan 1980',
        string $createdOn = '2018-01-01'
    ): DigitalSignature {
        $digitalSignature = m::mock(DigitalSignature::class);
        $digitalSignature->shouldReceive('getSignatureName')->andReturn($name);
        $digitalSignature->shouldReceive('getDateOfBirth')->andReturn($dateOfBirth);
        $digitalSignature->shouldReceive('getCreatedOn')->with(true)->andReturn(new DateTime($createdOn));

        return $digitalSignature;
    }

    public static function digitalSignatureDataProvider(): array
    {
        return [
            'Operator and TM' => [
                'hasOpSignature' => true,
                'isOwner' => 'N',
                'partial' => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_BOTH,
                'replacements' => '%s_%s_%s_%s_%s_%s_%s',
                'expectedMarkup' => 'TmName_06 May 1975_01 Jan 2018_owners-signaturetranslated'
                    . '_OpName_02 Jan 1980_03 Feb 2018',
            ],
            'as TM only' => [
                'hasOpSignature' => false,
                'isOwner' => 'N',
                'partial' => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL,
                'replacements' => '%s_%s_%s_%s_%s',
                'expectedMarkup' => 'TmName_06 May 1975_01 Jan 2018_owners-signaturetranslated_ADDRESS',
            ],
            'as OperatorTM' => [
                'hasOpSignature' => true,
                'isOwner' => 'Y',
                'partial' => TransportManagerSignatureReviewService::SIGNATURE_DIGITAL_OPERATOR_TM,
                'replacements' => '%s_%s_%s',
                'expectedMarkup' => 'OpName_02 Jan 1980_03 Feb 2018',
            ],
        ];
    }
}
