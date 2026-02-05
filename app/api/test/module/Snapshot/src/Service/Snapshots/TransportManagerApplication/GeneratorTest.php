<?php

declare(strict_types=1);

/**
 * Generator Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Generator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerMainReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerResponsibilityReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerOtherEmploymentReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerPreviousConvictionReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerPreviousLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerDeclarationReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerSignatureReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

/**
 * Generator Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GeneratorTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|PhpRenderer
     */
    protected $viewRenderer;

    /**
     * @var \Mockery\MockInterface|NiTextTranslation
     */
    protected $niTextTranslation;

    /**
     * @var \Mockery\MockInterface|TransportManagerMainReviewService
     */
    protected $transportManagerMainReviewService;

    /**
     * @var \Mockery\MockInterface|TransportManagerResponsibilityReviewService
     */
    protected $transportManagerResponsibilityReviewService;

    /**
     * @var \Mockery\MockInterface|TransportManagerOtherEmploymentReviewService
     */
    protected $transportManagerOtherEmploymentReviewService;

    /**
     * @var \Mockery\MockInterface|TransportManagerPreviousConvictionReviewService
     */
    protected $transportManagerPreviousConvictionReviewService;

    /**
     * @var \Mockery\MockInterface|TransportManagerPreviousLicenceReviewService
     */
    protected $transportManagerPreviousLicenceReviewService;

    /**
     * @var \Mockery\MockInterface|TransportManagerDeclarationReviewService
     */
    protected $transportManagerDeclarationReviewService;

    /**
     * @var \Mockery\MockInterface|TransportManagerSignatureReviewService
     */
    protected $transportManagerSignatureReviewService;

    /**
     * @var Generator
     */
    protected $sut;

    public function setUp(): void
    {
        $this->viewRenderer = m::mock(PhpRenderer::class);

        $abstractGeneratorServices = m::mock(AbstractGeneratorServices::class);
        $abstractGeneratorServices->shouldReceive('getRenderer')
            ->withNoArgs()
            ->andReturn($this->viewRenderer);

        $this->niTextTranslation = m::mock(NiTextTranslation::class);

        $this->transportManagerMainReviewService = m::mock(TransportManagerMainReviewService::class);
        $this->transportManagerResponsibilityReviewService = m::mock(TransportManagerResponsibilityReviewService::class);
        $this->transportManagerOtherEmploymentReviewService = m::mock(TransportManagerOtherEmploymentReviewService::class);
        $this->transportManagerPreviousConvictionReviewService = m::mock(TransportManagerPreviousConvictionReviewService::class);
        $this->transportManagerPreviousLicenceReviewService = m::mock(TransportManagerPreviousLicenceReviewService::class);
        $this->transportManagerDeclarationReviewService = m::mock(TransportManagerDeclarationReviewService::class);
        $this->transportManagerSignatureReviewService = m::mock(TransportManagerSignatureReviewService::class);

        $this->sut = new Generator(
            $abstractGeneratorServices,
            $this->niTextTranslation,
            $this->transportManagerMainReviewService,
            $this->transportManagerResponsibilityReviewService,
            $this->transportManagerOtherEmploymentReviewService,
            $this->transportManagerPreviousConvictionReviewService,
            $this->transportManagerPreviousLicenceReviewService,
            $this->transportManagerDeclarationReviewService,
            $this->transportManagerSignatureReviewService
        );
    }

    public function testGenerate(): void
    {
        $organisation = $this->mockOrganisation();

        $licence = $this->mockLicence($organisation);

        $application = $this->mockApplication($licence);

        /** @var TransportManagerApplication $tma */
        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getApplication')->andReturn($application);
        $tma->shouldReceive('getTmApplicationStatus')
            ->andReturn(new \Dvsa\Olcs\Api\Entity\System\RefData('foobar'));

        $this->setMainServicesExpectations($tma);

        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                fn($view) => $view
            );

        /** @var ViewModel $result */
        $result = $this->sut->generate($tma);

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('layout/review', $result->getTemplate());

        $params = $result->getVariables();

        $expected = [
            'reviewTitle' => 'tm-review-title',
            'subTitle' => 'Foo ltd AB1234567/111',
            'settings' => [
                'hide-count' => true
            ],
            'sections' => [
                [
                    'header' => 'tm-review-main',
                    'config' => 'tmMain'
                ],
                [
                    'header' => 'tm-review-responsibility',
                    'config' => 'tmResponsibility'
                ],
                [
                    'header' => 'tm-review-other-employment',
                    'config' => 'tmEmployment'
                ],
                [
                    'header' => 'tm-review-previous-conviction',
                    'config' => 'tmConviction'
                ],
                [
                    'header' => 'tm-review-previous-licence',
                    'config' => 'tmPreviousLicence'
                ],
            ]
        ];

        $this->assertEquals($expected, $params);
    }

    public function testGenerateWithSignatureOpSigned(): void
    {
        $organisation = $this->mockOrganisation();

        $licence = $this->mockLicence($organisation);

        $application = $this->mockApplication($licence);
        $application->shouldReceive('isVariation')->andReturn(false);
        /** @var TransportManagerApplication $tma */
        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getApplication')->andReturn($application);
        $tma->shouldReceive('getTmApplicationStatus')
            ->andReturn(new \Dvsa\Olcs\Api\Entity\System\RefData(TransportManagerApplication::STATUS_OPERATOR_SIGNED));

        $this->setMainServicesExpectations($tma);

        $this->transportManagerDeclarationReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmDeclaration');

        $this->transportManagerSignatureReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn(['tmSignature']);

        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                fn($view) => $view
            );

        /** @var ViewModel $result */
        $result = $this->sut->generate($tma);

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('layout/review', $result->getTemplate());

        $params = $result->getVariables();

        $expected = [
            'reviewTitle' => 'tm-review-title',
            'subTitle' => 'Foo ltd AB1234567/111',
            'settings' => [
                'hide-count' => true
            ],
            'sections' => $this->getWithSignatureSections()
        ];

        $this->assertEquals($expected, $params);
    }

    public function testGenerateWithSignatureOpSignedDigitally(): void
    {
        $organisation = $this->mockOrganisation();

        $licence = $this->mockLicence($organisation);

        $application = $this->mockApplication($licence);
        $application->shouldReceive('isVariation')->andReturn(false);
        /** @var TransportManagerApplication $tma */
        $tma = m::mock(TransportManagerApplication::class);
        $tma->shouldReceive('getApplication')->andReturn($application);
        $tma->shouldReceive('getTmApplicationStatus')
            ->andReturn(new \Dvsa\Olcs\Api\Entity\System\RefData(TransportManagerApplication::STATUS_RECEIVED));

        $this->setMainServicesExpectations($tma);

        $this->transportManagerDeclarationReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmDeclaration');

        $this->transportManagerSignatureReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn(['tmSignature']);

        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                fn($view) => $view
            );

        /** @var ViewModel $result */
        $result = $this->sut->generate($tma);

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('layout/review', $result->getTemplate());

        $params = $result->getVariables();

        $expected = [
            'reviewTitle' => 'tm-review-title',
            'subTitle' => 'Foo ltd AB1234567/111',
            'settings' => [
                'hide-count' => true
            ],
            'sections' => $this->getWithSignatureSections()
        ];

        $this->assertEquals($expected, $params);
    }

    /**
     * @param $licence
     * @return m\MockInterface
     */
    private function mockApplication(mixed $licence): m\MockInterface
    {
        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence')->andReturn($licence);
        $application->shouldReceive('getId')->andReturn(111);
        $application->shouldReceive('getNiFlag')->andReturn('N');
        return $application;
    }

    /**
     * @param $organisation
     * @return m\MockInterface
     */
    private function mockLicence(mixed $organisation): m\MockInterface
    {
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getLicNo')->andReturn('AB1234567');
        return $licence;
    }

    /**
     * @return m\MockInterface
     */
    private function mockOrganisation(): m\MockInterface
    {
        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getName')->andReturn('Foo ltd');
        return $organisation;
    }

    /**
     * @param $tma
     */
    private function setMainServicesExpectations(mixed $tma): void
    {
        $this->niTextTranslation
            ->shouldReceive('setLocaleForNiFlag')->once()->with('N');

        $this->transportManagerMainReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmMain');

        $this->transportManagerResponsibilityReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmResponsibility');

        $this->transportManagerOtherEmploymentReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmEmployment');

        $this->transportManagerPreviousConvictionReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmConviction');

        $this->transportManagerPreviousLicenceReviewService
            ->shouldReceive('getConfig')->once()->with($tma)->andReturn('tmPreviousLicence');
    }

    /**
     * @return array
     */
    private function getWithSignatureSections(): array
    {
        return [
            [
                'header' => 'tm-review-main',
                'config' => 'tmMain'
            ],
            [
                'header' => 'tm-review-responsibility',
                'config' => 'tmResponsibility'
            ],
            [
                'header' => 'tm-review-other-employment',
                'config' => 'tmEmployment'
            ],
            [
                'header' => 'tm-review-previous-conviction',
                'config' => 'tmConviction'
            ],
            [
                'header' => 'tm-review-previous-licence',
                'config' => 'tmPreviousLicence'
            ],
            [
                'header' => 'tm-review-declaration',
                'config' => 'tmDeclaration'
            ],
            [
                'config' => ['tmSignature']
            ],
        ];
    }
}
