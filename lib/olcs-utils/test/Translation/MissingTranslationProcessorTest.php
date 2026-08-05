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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class MissingTranslationProcessorTest extends TestCase
{
    /**
     * @var RendererInterface&Stub
     */
    protected $mockRenderer;

    /**
     * @var ResolverInterface&Stub
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

        $this->mockRenderer = $this->createStub(RendererInterface::class);
        $this->mockResolver = $this->createStub(ResolverInterface::class);
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
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
        $event->expects($this->once())
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_GB',
                'message' => 'markup-some-partial',
            ]);

        $this->mockResolver
            ->method('resolve')
            ->willReturnMap([
                ['en_GB/markup-some-partial', 'path_to_the_partial'],
            ]);

        $this->mockRenderer
            ->method('render')
            ->willReturnMap([
                ['en_GB/markup-some-partial', 'markup'],
            ]);

        $this->assertEquals('markup', $this->getService()->processEvent($event));
    }

    public function testProcessEventForPartialNi(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_NI',
                'message' => 'markup-some-partial',
            ]);

        $this->mockResolver
            ->method('resolve')
            ->willReturnMap([
                ['en_NI/markup-some-partial', 'path_to_the_partial'],
            ]);

        $this->mockRenderer
            ->method('render')
            ->willReturnMap([
                ['en_NI/markup-some-partial', 'markup'],
            ]);

        $this->assertEquals('markup', $this->getService()->processEvent($event));
    }

    public function testProcessEventForPartialNiFallsBackToGb(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
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
            ->willReturnMap([
                ['en_GB/markup-some-partial', 'gb-markup'],
            ]);

        $this->assertEquals('gb-markup', $this->getService()->processEvent($event));
    }

    public function testProcessEventForNestedTranslation(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('translate')
            ->willReturnMap([
                ['nested.translation.key', 'translated substring'],
            ]);

        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($translator);
        $event
            ->method('getParams')
            ->willReturn([
                'message' => 'text with a {nested.translation.key} in it',
            ]);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects($this->never())->method('resolve');
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->expects($this->never())->method('render');

        $this->assertEquals(
            'text with a translated substring in it',
            $this->getService($renderer, $resolver)->processEvent($event),
        );
    }

    public function testOtherMissingKeysDontTriggerTemplateResolver(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn(['message' => 'missing.key']);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects($this->never())->method('resolve');
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->expects($this->never())->method('render');

        $this->assertNull($this->getService($renderer, $resolver)->processEvent($event));
    }

    public function testProcessEventForPartialWithPlaceholder(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
        $event
            ->method('getParams')
            ->willReturn([
                'locale' => 'en_GB',
                'message' => 'markup-some-partial',
            ]);

        $placeholderContainer = $this->createStub(PlaceholderContainer::class);
        $placeholderContainer->method('asString')->willReturn('foo-placeholder');

        $this->getPlaceholder = function (string $name) use ($placeholderContainer): PlaceholderContainer {
            $this->assertSame('FOO', $name);
            return $placeholderContainer;
        };

        $this->mockResolver
            ->method('resolve')
            ->willReturnMap([
                ['en_GB/markup-some-partial', 'path_to_the_partial'],
            ]);

        $this->mockRenderer
            ->method('render')
            ->willReturnMap([
                ['en_GB/markup-some-partial', 'markup {{PLACEHOLDER:FOO}} bar'],
            ]);

        $this->assertEquals('markup foo-placeholder bar', $this->getService()->processEvent($event));
    }

    public function testProcessEventReturnsNullWhenTargetIsNotATranslator(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn(new \stdClass());

        $this->assertNull($this->getService()->processEvent($event));
    }

    public function testProcessEventReturnsNullForEmptyMessage(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
        $event->method('getParams')->willReturn(['message' => '']);

        $this->assertNull($this->getService()->processEvent($event));
    }

    public function testProcessEventReturnsNullForNonStringMessage(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getTarget')->willReturn($this->createStub(TranslatorInterface::class));
        $event->method('getParams')->willReturn(['message' => ['not', 'a', 'string']]);

        $this->assertNull($this->getService()->processEvent($event));
    }

    protected function getService(
        ?RendererInterface $renderer = null,
        ?ResolverInterface $resolver = null,
    ): MissingTranslationProcessor {
        return new MissingTranslationProcessor(
            $renderer ?? $this->mockRenderer,
            $resolver ?? $this->mockResolver,
            $this->getPlaceholder,
        );
    }
}
