<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Document;

use Common\Service\Cqrs\Response as CqrsResponse;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Document\DocumentGenerationController;

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
}
