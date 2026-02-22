<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\SignatureReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\Translator\TranslatorInterface;

class SignatureReviewServiceTest extends MockeryTestCase
{
    /**
     * @var SignatureReviewService
     */
    protected $sut;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function setUp(): void
    {
        $this->translator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->translator);

        $this->sut = new SignatureReviewService($abstractReviewServiceServices);
    }

    public function testPhysicalSignature(): void
    {
        $surrender = $this->mockSurrender(RefData::SIG_PHYSICAL_SIGNATURE);

        $this->translator->shouldReceive('translate')
            ->with('markup-signature-physical')
            ->andReturn('%s__%s__translated');

        $this->translator->shouldReceive('translate')
            ->with('directors-signature')
            ->andReturn('directors-signature-translated');

        $this->translator->shouldReceive('translate')
            ->with('return-address')
            ->andReturn('return-address-translated');

        $markup = $this->sut->getConfigFromData($surrender);
        $expected = [
            'markup' => "directors-signature-translated__return-address-translated__translated"
        ];

        $this->assertEquals($expected, $markup);
    }

    public function testDigitalSignature(): void
    {
        $date = new \DateTime("2019-01-29");
        $signature = m::mock(DigitalSignature::class);
        $signature->shouldReceive('getSignatureName')->andReturn('test-name');
        $signature->shouldReceive('getDateOfBirth')->andReturn($date);
        $signature->shouldReceive('getCreatedOn')->andReturn($date);

        $surrender = $this->mockSurrender(RefData::SIG_DIGITAL_SIGNATURE);
        $surrender->shouldReceive('getDigitalSignature')->andReturn($signature);

        $this->translator->shouldReceive('translate')
            ->with('markup-signature-digital')
            ->andReturn('%s__%s__%s__translated');

        $markup = $this->sut->getConfigFromData($surrender);
        $expected = ['markup' => "test-name__29 Jan 2019__29 Jan 2019__translated"];

        $this->assertEquals($expected, $markup);
    }

    protected function mockSurrender(mixed $signatureType): mixed
    {
        $refData = m::mock(RefData::class);
        $refData->shouldReceive('getId')->andReturn($signatureType);
        $surrender = m::mock(Surrender::class);
        $surrender->shouldReceive('getSignatureType')->andReturn($refData);

        return $surrender;
    }
}
