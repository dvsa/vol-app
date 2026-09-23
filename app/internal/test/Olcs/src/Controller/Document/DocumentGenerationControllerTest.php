<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Document;

use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Document\DocumentGenerationController;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;

#[\PHPUnit\Framework\Attributes\CoversClass(DocumentGenerationController::class)]
final class DocumentGenerationControllerTest extends MockeryTestCase
{
    private m\MockInterface $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = m::mock(DocumentGenerationController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testNewLetterEntryRedirectsToTheLettersFlow(): void
    {
        $this->sut->shouldReceive('params')->with('id')->andReturn('new-7');
        $this->sut->shouldReceive('isLettersDatabaseDrivenEnabled')->andReturn(true);

        $result = $this->sut->listTemplateBookmarksAction();

        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertSame(
            ['redirectToNewLetterFlow' => true, 'templateId' => 7],
            $result->getVariables()
        );
    }

    /**
     * A template linked to a letter type still offers its old RTF letter under its plain id.
     */
    public function testPlainTemplateIdLoadsBookmarksEvenWhenLinkedToALetterType(): void
    {
        $this->sut->shouldReceive('params')->with('id')->andReturn('7');
        $this->sut->shouldReceive('isLettersDatabaseDrivenEnabled')->andReturn(true);

        $paragraphs = m::mock(CqrsResponse::class);
        $paragraphs->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($paragraphs);

        $result = $this->sut->listTemplateBookmarksAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertNotInstanceOf(JsonModel::class, $result);
    }

    public function testOnlyTheNewLetterEntryGoesToTheLettersFlowOnSubmit(): void
    {
        $this->sut->shouldReceive('isLettersDatabaseDrivenEnabled')->andReturn(true);

        $this->assertTrue($this->sut->shouldRedirectToLetterChoices(['details' => ['documentTemplate' => 'new-7']]));
        $this->assertFalse($this->sut->shouldRedirectToLetterChoices(['details' => ['documentTemplate' => '7']]));
        $this->assertFalse($this->sut->shouldRedirectToLetterChoices(['details' => []]));
    }

    public function testNothingGoesToTheLettersFlowWhenTheToggleIsOff(): void
    {
        $this->sut->shouldReceive('isLettersDatabaseDrivenEnabled')->andReturn(false);

        $this->assertFalse($this->sut->shouldRedirectToLetterChoices(['details' => ['documentTemplate' => 'new-7']]));
    }

    public function testCaseLetterRedirectCarriesTheCase(): void
    {
        $sut = m::mock(DocumentGenerationController::class, [
            m::mock(ScriptFactory::class),
            m::mock(FormHelperService::class),
            m::mock(TableFactory::class),
            m::mock(HelperPluginManager::class),
            [],
            m::mock(FlashMessengerHelperService::class),
            m::mock(DocumentSubCategoryWithDocs::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $request = new Request();
        $request->setRequestUri('/case/12/documents/generate');
        $sut->shouldReceive('getRequest')->andReturn($request);

        $response = new Response();
        $redirect = m::mock(Redirect::class);
        $redirect->shouldReceive('toRoute')
            ->with('letter/create', [], ['query' => [
                'template' => 5,
                'case' => 12,
                'returnUrl' => '/case/12/documents/generate',
            ]])
            ->once()
            ->andReturn($response);
        $sut->shouldReceive('redirect')->andReturn($redirect);

        $method = new \ReflectionMethod(DocumentGenerationController::class, 'redirectToLetterChoices');

        $this->assertSame(
            $response,
            $method->invoke($sut, ['details' => ['documentTemplate' => 'new-5']], ['type' => 'case', 'case' => 12])
        );
    }
}
