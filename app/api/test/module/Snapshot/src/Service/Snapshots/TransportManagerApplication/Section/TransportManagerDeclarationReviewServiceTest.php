<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerDeclarationReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * TransportManagerDeclarationReviewServiceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerDeclarationReviewServiceTest extends MockeryTestCase
{
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

        $this->sut = new TransportManagerDeclarationReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testGetConfig(
        mixed $isTypeInternal,
        mixed $niFlag,
        mixed $goodsOrPsvId,
        mixed $expectedMarkupTranslationKey,
        mixed $expectedResidencyClauseKey,
        mixed $expectedRoleClauseKey
    ): void {
        $translatedResidencyClauseKey = 'translated residency clause key';
        $translatedRoleClauseKey = 'translated role clause key';

        $this->mockTranslator->shouldReceive('translate')
            ->with($expectedResidencyClauseKey)
            ->andReturn($translatedResidencyClauseKey);
        $this->mockTranslator->shouldReceive('translate')
            ->with($expectedRoleClauseKey)
            ->andReturn($translatedRoleClauseKey);
        $this->mockTranslator->shouldReceive('translate')
            ->with($expectedMarkupTranslationKey)
            ->andReturn('translated and replaced [%s] [%s]');

        $application = m::mock(Application::class);
        $application->shouldReceive('getNiFlag')
            ->withNoArgs()
            ->andReturn($niFlag);
        $application->shouldReceive('getGoodsOrPsv->getId')
            ->withNoArgs()
            ->andReturn($goodsOrPsvId);

        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getApplication')
            ->withNoArgs()
            ->andReturn($application);
        $tma->shouldReceive('isTypeInternal')
            ->withNoArgs()
            ->andReturn($isTypeInternal);

        $expectedMarkup = 'translated and replaced [translated residency clause key] [translated role clause key]';

        $this->assertEquals(['markup' => $expectedMarkup], $this->sut->getConfig($tma));
    }

    public static function provider(): array
    {
        return [
            [
                true,
                'Y',
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'markup-tma-declaration-internal-ni',
                'tma-declaration.residency-clause.lcat_gv',
                'tma-declaration.role-clause.lcat_gv'
            ],
            [
                true,
                'N',
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'markup-tma-declaration-internal-gb',
                'tma-declaration.residency-clause.lcat_gv',
                'tma-declaration.role-clause.lcat_gv'
            ],
            [
                false,
                'Y',
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'markup-tma-declaration-external-ni',
                'tma-declaration.residency-clause.lcat_gv',
                'tma-declaration.role-clause.lcat_gv'
            ],
            [
                false,
                'N',
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'markup-tma-declaration-external-gb',
                'tma-declaration.residency-clause.lcat_gv',
                'tma-declaration.role-clause.lcat_gv'
            ],
            [
                true,
                'Y',
                Licence::LICENCE_CATEGORY_PSV,
                'markup-tma-declaration-internal-ni',
                'tma-declaration.residency-clause.lcat_psv',
                'tma-declaration.role-clause.lcat_psv'
            ],
            [
                true,
                'N',
                Licence::LICENCE_CATEGORY_PSV,
                'markup-tma-declaration-internal-gb',
                'tma-declaration.residency-clause.lcat_psv',
                'tma-declaration.role-clause.lcat_psv'
            ],
            [
                false,
                'Y',
                Licence::LICENCE_CATEGORY_PSV,
                'markup-tma-declaration-external-ni',
                'tma-declaration.residency-clause.lcat_psv',
                'tma-declaration.role-clause.lcat_psv'
            ],
            [
                false,
                'N',
                Licence::LICENCE_CATEGORY_PSV,
                'markup-tma-declaration-external-gb',
                'tma-declaration.residency-clause.lcat_psv',
                'tma-declaration.role-clause.lcat_psv'
            ],
        ];
    }
}
