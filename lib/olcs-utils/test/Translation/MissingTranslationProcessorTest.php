<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\MissingTranslationProcessor;
use Dvsa\Olcs\Utils\View\Helper\GetPlaceholder as PlaceholderContainer;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MissingTranslationProcessorTest extends TestCase
{
    /**
     * @var RendererInterface|MockObject
     */
    protected $mockRenderer;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $mockResolver;

    /**
     * Stand-in for the registered `getPlaceholder` view helper, which is
     * actually a Closure (see GetPlaceholderFactory). Tests override this in
     * setUp for the placeholder-rendering case.
     *
     * @var \Closure
     */
    protected $getPlaceholder;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockRenderer = $this->createMock(RendererInterface::class);
        $this->mockResolver = $this->createMock(ResolverInterface::class);
        $this->getPlaceholder = fn(string $name): ?PlaceholderContainer => null;
    }

    public function testAttach(): void
    {
        $events = $this->createMock(EventManagerInterface::class);
        $events->expects($this->once())->method('attach');

        $this->getService()->attach($events);
    }

    public function testProcessEventForPartial(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event->expects($this->once())
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_GB',
                'message' => 'markup-some-partial',
            ]);

        $this->mockResolver
            ->method('resolve')
            ->with('en_GB/markup-some-partial')
            ->willReturn('path_to_the_partial');

        $this->mockRenderer
            ->method('render')
            ->with('en_GB/markup-some-partial')
            ->willReturn('markup');

        $this->assertEquals('markup', $this->getService()->processEvent($event));
    }

    public function testProcessEventForPartialNi(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_NI',
                'message' => 'markup-some-partial',
            ]);

        $this->mockResolver
            ->method('resolve')
            ->with('en_NI/markup-some-partial')
            ->willReturn('path_to_the_partial');

        $this->mockRenderer
            ->method('render')
            ->with('en_NI/markup-some-partial')
            ->willReturn('markup');

        $this->assertEquals('markup', $this->getService()->processEvent($event));
    }

    public function testProcessEventForPartialNiFallsBackToGb(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_NI',
                'message' => 'markup-some-partial',
            ]);

        $this->mockResolver
            ->method('resolve')
            ->willReturnCallback(
                fn(string $partial) => $partial === 'en_GB/markup-some-partial' ? 'path_to_the_partial' : false,
            );

        $this->mockRenderer
            ->method('render')
            ->with('en_GB/markup-some-partial')
            ->willReturn('gb-markup');

        $this->assertEquals('gb-markup', $this->getService()->processEvent($event));
    }

    public function testProcessEventForNestedTranslation(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('translate')
            ->with('nested.translation.key')
            ->willReturn('translated substring');

        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($translator);
        $event
            ->method('getParams')
            ->willReturn([
                'message' => 'text with a {nested.translation.key} in it',
            ]);

        $this->mockResolver->expects($this->never())->method('resolve');
        $this->mockRenderer->expects($this->never())->method('render');

        $this->assertEquals(
            'text with a translated substring in it',
            $this->getService()->processEvent($event),
        );
    }

    public function testOtherMissingKeysDontTriggerTemplateResolver(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn(['message' => 'missing.key']);

        $this->mockResolver->expects($this->never())->method('resolve');
        $this->mockRenderer->expects($this->never())->method('render');

        $this->assertNull($this->getService()->processEvent($event));
    }

    public function testProcessEventForPartialWithPlaceholder(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_GB',
                'message' => 'markup-some-partial',
            ]);

        $placeholderContainer = $this->createMock(PlaceholderContainer::class);
        $placeholderContainer->method('asString')->willReturn('foo-placeholder');

        $this->getPlaceholder = function (string $name) use ($placeholderContainer): PlaceholderContainer {
            $this->assertEquals('FOO', $name);
            return $placeholderContainer;
        };

        $this->mockResolver
            ->method('resolve')
            ->with('en_GB/markup-some-partial')
            ->willReturn('path_to_the_partial');

        $this->mockRenderer
            ->method('render')
            ->with('en_GB/markup-some-partial')
            ->willReturn('markup {{PLACEHOLDER:FOO}} bar');

        $this->assertEquals('markup foo-placeholder bar', $this->getService()->processEvent($event));
    }

    public function testProcessEventReturnsNullWhenTargetIsNotATranslator(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn(new \stdClass());

        $this->assertNull($this->getService()->processEvent($event));
    }

    public function testProcessEventReturnsNullForEmptyMessage(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event->method('getParams')->willReturn(['message' => '']);

        $this->assertNull($this->getService()->processEvent($event));
    }

    public function testProcessEventReturnsNullForNonStringMessage(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTarget')->willReturn($this->createMock(TranslatorInterface::class));
        $event->method('getParams')->willReturn(['message' => ['not', 'a', 'string']]);

        $this->assertNull($this->getService()->processEvent($event));
    }

    protected function getService(): MissingTranslationProcessor
    {
        return new MissingTranslationProcessor(
            $this->mockRenderer,
            $this->mockResolver,
            $this->getPlaceholder,
        );
    }
}
