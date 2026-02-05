<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Generator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CommunityLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CurrentDiscsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\DeclarationReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\OperatorLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\LicenceDetailsService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\SignatureReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class GeneratorTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|PhpRenderer
     */
    private $viewRenderer;

    /**
     * @var \Mockery\MockInterface|LicenceDetailsService
     */
    private $licenceDetailsService;

    /**
     * @var \Mockery\MockInterface|CurrentDiscsReviewService
     */
    protected $currentDiscsReviewService;

    /**
     * @var \Mockery\MockInterface|OperatorLicenceReviewService
     */
    protected $operatorLicenceReviewService;

    /**
     * @var \Mockery\MockInterface|CommunityLicenceReviewService
     */
    protected $communityLicenceReviewService;

    /**
     * @var \Mockery\MockInterface|DeclarationReviewService
     */
    protected $declarationReviewService;

    /**
     * @var \Mockery\MockInterface|SignatureReviewService
     */
    protected $signatureReviewService;

    /**
     * @var Generator
     */
    protected $sut;

    /**
     * @var array
     */
    protected $services;

    protected function setUp(): void
    {
        $this->viewRenderer = m::mock(PhpRenderer::class);

        $abstractGeneratorServices = m::mock(AbstractGeneratorServices::class);
        $abstractGeneratorServices->shouldReceive('getRenderer')
            ->withNoArgs()
            ->andReturn($this->viewRenderer);

        $this->licenceDetailsService = m::mock(LicenceDetailsService::class);
        $this->currentDiscsReviewService = m::mock(CurrentDiscsReviewService::class);
        $this->operatorLicenceReviewService = m::mock(OperatorLicenceReviewService::class);
        $this->communityLicenceReviewService = m::mock(CommunityLicenceReviewService::class);
        $this->declarationReviewService = m::mock(DeclarationReviewService::class);
        $this->signatureReviewService = m::mock(SignatureReviewService::class);

        $this->sut = new Generator(
            $abstractGeneratorServices,
            $this->licenceDetailsService,
            $this->currentDiscsReviewService,
            $this->operatorLicenceReviewService,
            $this->communityLicenceReviewService,
            $this->declarationReviewService,
            $this->signatureReviewService
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dbLicenceType')]
    public function testGenerate(mixed $licenceType, mixed $expected): void
    {
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicNo')->andReturn('AB1234567');
        $licence->shouldReceive('getLicenceType->getId')->andReturn($licenceType);

        $surrender = m::mock(Surrender::class);
        $surrender->shouldReceive('getLicence')->andReturn($licence);

        $this->setServices($surrender);

        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                fn($view) => $view
            );

        /** @var ViewModel $result */
        $result = $this->sut->generate($surrender);

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('layout/review', $result->getTemplate());

        $variables = $result->getVariables();

        $this->assertSame($expected, $variables);
    }

    protected function setServices(mixed $surrender): void
    {
        $this->licenceDetailsService->shouldReceive('getConfigFromData')
            ->once()->with($surrender)->andReturn('licenceDetails');

        $this->currentDiscsReviewService->shouldReceive('getConfigFromData')
            ->once()->with($surrender)->andReturn('currentDiscs');

        $this->operatorLicenceReviewService->shouldReceive('getConfigFromData')
            ->once()->with($surrender)->andReturn('Operator licence');

        $this->communityLicenceReviewService->shouldReceive('getConfigFromData')
            ->atMost()->once()->with($surrender)->andReturn('Community licence');

        $this->declarationReviewService->shouldReceive('getConfigFromData')
            ->once()->with($surrender)->andReturn('declaration');

        $this->signatureReviewService->shouldReceive('getConfigFromData')
            ->once()->with($surrender)->andReturn('signature');
    }

    public static function dbLicenceType(): array
    {
        return [
            [
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                [
                    'reviewTitle' => 'surrender-review-title',
                    'subTitle' => 'AB1234567',
                    'settings' => [
                        'hide-count' => true
                    ],
                    'sections' => [
                        [
                            'header' => 'surrender-review-licence',
                            'config' => 'licenceDetails'
                        ],
                        [
                            'header' => 'surrender-review-current-discs',
                            'config' => 'currentDiscs'
                        ],
                        [
                            'header' => 'surrender-review-operator-licence',
                            'config' => 'Operator licence'
                        ],
                        [
                            'header' => 'surrender-review-community-licence',
                            'config' => 'Community licence'
                        ],
                        [
                            'header' => 'surrender-review-declaration',
                            'config' => 'declaration'
                        ],
                        [
                            'config' => 'signature'
                        ]
                    ]
                ],
            ],
            [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    'reviewTitle' => 'surrender-review-title',
                    'subTitle' => 'AB1234567',
                    'settings' => [
                        'hide-count' => true
                    ],
                    'sections' => [
                        [
                            'header' => 'surrender-review-licence',
                            'config' => 'licenceDetails'
                        ],
                        [
                            'header' => 'surrender-review-current-discs',
                            'config' => 'currentDiscs'
                        ],
                        [
                            'header' => 'surrender-review-operator-licence',
                            'config' => 'Operator licence'
                        ],
                        [
                            'header' => 'surrender-review-declaration',
                            'config' => 'declaration'
                        ],
                        [
                            'config' => 'signature'
                        ]
                    ]
                ],
            ],
        ];
    }
}
