<?php

declare(strict_types=1);

namespace AdminTest\Controller\Letter;

use Admin\Controller\Letter\LetterAppendixController;
use Admin\Controller\Letter\LetterDeleteErrorTrait;
use Admin\Controller\Letter\LetterIssueController;
use Admin\Controller\Letter\LetterSectionController;
use Admin\Controller\Letter\LetterTodoController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LetterDeleteErrorTraitTest extends MockeryTestCase
{
    private m\MockInterface $translator;
    private m\MockInterface $flashMessenger;
    private LetterTodoController $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslationHelperService::class);
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);

        $this->sut = new LetterTodoController(
            $this->translator,
            m::mock(FormHelperService::class),
            $this->flashMessenger,
            m::mock(Navigation::class)
        );
    }

    public function testShowsWhyTheDeleteWasRefusedEscaped(): void
    {
        $this->flashMessenger->expects('addErrorMessage')->with(
            'Cannot delete this to-do because it is used by issue: &lt;b&gt;Tom &amp; Jerry&#039;s&lt;/b&gt;.'
        );

        $this->sut->handleErrors([
            'messages' => ['letterDelete' => 'Cannot delete this to-do because it is used by issue: <b>Tom & Jerry\'s</b>.'],
        ]);
    }

    public function testAnythingElseKeepsTheGenericHandling(): void
    {
        $sqlError = 'SQLSTATE[23000]: Integrity constraint violation';
        $this->translator->expects('translate')->with($sqlError)->andReturn($sqlError);
        $this->flashMessenger->expects('addErrorMessage')->with('unknown-error');

        $this->sut->handleErrors(['messages' => [$sqlError]]);
    }

    public static function controllerProvider(): \Iterator
    {
        yield 'appendix' => [LetterAppendixController::class];
        yield 'issue' => [LetterIssueController::class];
        yield 'section' => [LetterSectionController::class];
        yield 'to-do' => [LetterTodoController::class];
    }

    #[DataProvider('controllerProvider')]
    public function testUsedByEveryLetterContentController(string $controller): void
    {
        $this->assertContains(LetterDeleteErrorTrait::class, class_uses($controller));
    }
}
