<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\FlashMessenger;
use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;

/**
 * Flash Messenger View Helper Test
 *
 * @covers \Common\View\Helper\FlashMessenger
 */
class FlashMessengerTest extends MockeryTestCase
{
    /**
     * Subject under test
     *
     * @var \Common\View\Helper\FlashMessenger
     */
    private $sut;

    private $flashMessengerHelperService;

    private $mockPluginManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockPluginManager = m::mock(\Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger::class);
        $this->flashMessengerHelperService = m::mock(FlashMessengerHelperService::class);

        $mockTranslator = m::mock(\Laminas\I18n\Translator\Translator::class);
        $mockTranslator->shouldReceive('translate')
            ->andReturnUsing(fn(string $message): string => $this->translate($message));

        $this->sut = new FlashMessenger($this->flashMessengerHelperService);
        $this->sut->setPluginFlashMessenger($this->mockPluginManager);
        $this->sut->setTranslator($mockTranslator);
    }

    /**
     * Mock translation
     *
     * @param string $message
     * @return string
     */
    public function translate($message)
    {
        return '*' . $message . '*';
    }

    public function testGetMessagesFromNamespace(): void
    {
        $namespace = 'foo';

        $this->mockPluginManager->shouldReceive('getMessagesFromNamespace')
            ->with('foo')
            ->andReturn(['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $this->sut->getMessagesFromNamespace($namespace));
    }

    public function testInvokeNoRender(): void
    {
        $sut = $this->sut;

        $this->assertSame($sut, $sut('norender'));
    }

    /**
     * @group view_helper
     * @group flash_messenger_view_helper
     */
    public function testRenderWithoutMessages(): void
    {
        $this->flashMessengerHelperService->shouldReceive('getCurrentMessages')
            ->andReturn([]);

        $this->mockPluginManager->shouldReceive('getMessagesFromNamespace')
            ->andReturn([])
            ->shouldReceive('getCurrentMessagesFromNamespace')
            ->andReturn([]);

        $markup = $this->sut->render();

        $this->assertEquals('', $markup);
    }

    /**
     * @group view_helper
     * @group flash_messenger_view_helper
     */
    public function testInvokeWithoutMessages(): void
    {
        $this->flashMessengerHelperService->shouldReceive('getCurrentMessages')
            ->andReturn([]);

        $this->mockPluginManager->shouldReceive('getMessagesFromNamespace')
            ->andReturn([])
            ->shouldReceive('getCurrentMessagesFromNamespace')
            ->andReturn([]);

        $obj = $this->sut;

        $markup = $obj();

        $this->assertEquals('', $markup);
    }

    /**
     * @group view_helper
     * @group flash_messenger_view_helper
     */
    public function testRenderWithMessages(): void
    {
        $this->flashMessengerHelperService->shouldReceive('getCurrentMessages')
            ->andReturn(['foo']);

        $this->mockPluginManager->shouldReceive('getMessagesFromNamespace')
            ->andReturn(['bar'])
            ->shouldReceive('getCurrentMessagesFromNamespace')
            ->andReturn(['baz']);

        $expected = '<div class="notice-container"><div class="notice--danger"><p role="alert">*bar*</p></div>'
            . '<div class="notice--danger"><p role="alert">*baz*</p></div>'
            . '<div class="notice--danger"><p role="alert">*foo*</p></div>'
            . '<div class="notice--success"><p role="alert">*bar*</p></div>'
            . '<div class="notice--success"><p role="alert">*baz*</p></div>'
            . '<div class="notice--success"><p role="alert">*foo*</p></div>'
            . '<div class="notice--warning"><p role="alert">*bar*</p></div>'
            . '<div class="notice--warning"><p role="alert">*baz*</p></div>'
            . '<div class="notice--warning"><p role="alert">*foo*</p></div>'
            . '<div class="notice--info"><p role="alert">*bar*</p></div>'
            . '<div class="notice--info"><p role="alert">*baz*</p></div>'
            . '<div class="notice--info"><p role="alert">*foo*</p></div>'
            . '<div class="notice--info"><p role="alert">*bar*</p></div>'
            . '<div class="notice--info"><p role="alert">*baz*</p></div>'
            . '<div class="notice--info"><p role="alert">*foo*</p></div>'
            . '</div>';

        $markup = $this->sut->render();

        //check initial markup
        $this->assertEquals($expected, $markup);

        //make sure get is rendered has been set
        $this->assertEquals(true, $this->sut->getIsRendered());

        //check messages don't render twice
        $this->assertEquals('', $this->sut->render());
    }
}
