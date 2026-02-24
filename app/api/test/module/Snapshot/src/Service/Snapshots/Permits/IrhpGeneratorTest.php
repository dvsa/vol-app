<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Permits\IrhpGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

/**
 * Class IrhpGeneratorTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpGeneratorTest extends MockeryTestCase
{
    /**
     * @var IrhpGenerator
     */
    protected $sut;

    /**
     * @var PhpRenderer
     */
    protected $viewRenderer;

    /**
     * @var AnswersSummaryGenerator
     */
    protected $answersSummaryGenerator;

    protected function setUp(): void
    {
        $this->answersSummaryGenerator = m::mock(AnswersSummaryGenerator::class);

        $this->viewRenderer = m::mock(PhpRenderer::class);

        $abstractGeneratorServices = m::mock(AbstractGeneratorServices::class);
        $abstractGeneratorServices->shouldReceive('getRenderer')
            ->withNoArgs()
            ->andReturn($this->viewRenderer);

        $this->sut = new IrhpGenerator(
            $abstractGeneratorServices,
            $this->answersSummaryGenerator
        );
    }

    public function testGenerateWithNoPermitApplication(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Snapshot generator expects IRHP application record');

        $this->sut->setData(['entity' => null]);
        $this->sut->generate();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $isCertificateOfRoadworthiness, mixed $expectedGuidanceDeclarationTitle): void
    {
        $operatorName = 'operator name';
        $permitTypeDescription = 'permit type';
        $permitTypeId = 111;
        $applicationRef = 'ref';

        $questionAnswerData = [
            'slug' => 'data1',
            'slug2' => 'data2',
        ];

        $answersSummaryRepresentation = [
            'rows' => $questionAnswerData
        ];

        $html = '<html>';

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getName->getDescription')->once()->andReturn($permitTypeDescription);
        $irhpPermitType->shouldReceive('getId')->once()->withNoArgs()->andReturn($permitTypeId);
        $irhpPermitType->shouldReceive('isCertificateOfRoadworthiness')
            ->once()
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthiness);

        $irhpApplication = m::mock(IrhpApplication::class);

        $irhpApplication->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($irhpPermitType);

        $irhpApplication
            ->shouldReceive('getLicence->getOrganisation->getName')
            ->once()
            ->withNoArgs()
            ->andReturn($operatorName);

        $irhpApplication
            ->shouldReceive('getApplicationRef')
            ->once()
            ->withNoArgs()
            ->andReturn($applicationRef);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('getRepresentation')
            ->andReturn($answersSummaryRepresentation);

        $this->answersSummaryGenerator->shouldReceive('generate')
            ->with($irhpApplication, true)
            ->once()
            ->andReturn($answersSummary);

        $config = [
            'permitType' => $permitTypeDescription,
            'operator' => $operatorName,
            'ref' => $applicationRef,
            'questionAnswerPartialName' => 'question-answer-section-qa',
            'questionAnswerData' => $questionAnswerData,
            'guidanceDeclaration' => [
                'title' => $expectedGuidanceDeclarationTitle,
                'bullets' => 'markup-irhp-declaration-' . $permitTypeId,
                'declaration' => 'permits.snapshot.declaration',
            ],
        ];

        $template = 'layout/permit-application';

        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                function (ViewModel $viewModel) use ($config, $template, $html) {
                    $this->assertEquals($config, $viewModel->getVariables());
                    $this->assertEquals($template, $viewModel->getTemplate());
                    $this->assertTrue($viewModel->terminate());

                    return $html;
                }
            );

        $this->sut->setData(['entity' => $irhpApplication]);
        $this->sut->generate();
    }

    public static function dpGenerate(): array
    {
        return [
            [false, 'permits.snapshot.declaration.title'],
            [true, 'permits.snapshot.declaration.title.certificate'],
        ];
    }
}
