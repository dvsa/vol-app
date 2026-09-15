<?php

declare(strict_types=1);

namespace CommonTest\Service\Table;

use Common\Service\Table\ContentHelper;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Http\Response;

final class ContentHelperTest extends TestCase
{
    /**
     * Setup the content helper
     *
     * @param \PHPUnit\Framework\MockObject\MockObject&ContentHelper|\PHPUnit\Framework\MockObject\MockObject&Response|null $mock
     */
    public function getContentHelper(ContentHelper|Response|null $mock): ContentHelper
    {
        return new ContentHelper(__DIR__ . '/TestResources', $mock);
    }

    /**
     * Test translator set correctly
     */
    public function testTranslatorSet(): void
    {
        $translatorMock = $this->createStub(\Dvsa\Olcs\Utils\Translation\TranslatorDelegator::class);

        $mock = $this->createPartialMock(ContentHelper::class, ['getTranslator']);

        $mock->expects($this->once())
            ->method('getTranslator')
            ->willReturn($translatorMock);

        $this->assertSame($translatorMock, $this->getContentHelper($mock)->getTranslator());
    }

    /**
     * Test renderLayout with missing partial
     *
     */
    public function testRenderLayoutWithMissingPartial(): void
    {
        $this->expectException(\Exception::class);

        $contentHelper = $this->getContentHelper(null);

        $contentHelper->renderLayout('MissinPartial');
    }

    /**
     * Test renderLayout with partial, with object call
     */
    public function testRenderLayoutWithPartial(): void
    {
        $mock = $this->createMock(Response::class);

        $mock->expects($this->once())
            ->method('getContent')
            ->willReturn('SomeContent');

        $contentHelper = $this->getContentHelper($mock);

        $this->assertEquals('<p>SomeContent</p>', $contentHelper->renderLayout('OutputContent'));
    }

    /**
     * Test renderAttributes
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('attributesProvider')]
    public function testRenderAttributes($attrs, $expected): void
    {
        $contentHelper = $this->getContentHelper(null);

        $this->assertEquals($expected, $contentHelper->renderAttributes($attrs));
    }

    /**
     * Provider for renderAttributes
     *
     * @return \Iterator<(int | string), array<(array<(int | string | null)> | string)>>
     *
     * @psalm-return list{list{array{name: 'bob', id: 123, type: 'test'}, 'name="bob" id="123" type="test"'}, list{array{name: null, id: 123, type: 'test'}, 'name="" id="123" type="test"'}, list{array<never, never>, ''}}
     */
    public static function attributesProvider(): \Iterator
    {
        yield [['name' => 'bob', 'id' => 123, 'type' => 'test'], 'name="bob" id="123" type="test"'];
        yield [['name' => null, 'id' => 123, 'type' => 'test'], 'name="" id="123" type="test"'];
        yield [[], ''];

        // A value carrying a quote must not be able to close the attribute and start a new one.
        yield 'attribute breakout' => [
            ['class' => 'x" onmouseover="alert(1)'],
            'class="x&quot; onmouseover=&quot;alert(1)"',
        ];

        // Spaces and slashes are left alone — these are quoted attributes, so only the quote
        // character is load-bearing, and class lists / URL paths stay readable.
        yield 'class list is not mangled' => [
            ['class' => 'govuk-table__cell govuk-table__cell--numeric'],
            'class="govuk-table__cell govuk-table__cell--numeric"',
        ];
        yield 'url path is not mangled' => [
            ['href' => '/file/123'],
            'href="/file/123"',
        ];
    }

    /**
     * Test replaceContent
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('replaceContentProvider')]
    public function testReplaceContent($content, $vars, $expected): void
    {
        $contentHelper = $this->getContentHelper(null);

        $this->assertEquals($expected, $contentHelper->replaceContent($content, $vars));
    }

    /**
     * Data provider for replaceContent
     *
     * @return \Iterator<(int | string), array<(array<string> | string)>>
     *
     * @psalm-return list{list{'<p>No Variables</p>', array<never, never>, '<p>No Variables</p>'}, list{'<p>Foo {{bar}}</p>', array{bar: 'BOB'}, '<p>Foo BOB</p>'}, list{'<p>Foo {{bar}} {{cake}}</p>', array{bar: 'BOB'}, '<p>Foo BOB </p>'}, list{'{{[paragraph]}}', array{content: 'FOO'}, '<p>FOO</p>'}, list{'{{[paragraph]}}{{[paragraph]}}', array{content: 'FOO'}, '<p>FOO</p><p>FOO</p>'}}
     */
    public static function replaceContentProvider(): \Iterator
    {
        yield ['<p>No Variables</p>', [], '<p>No Variables</p>'];
        yield ['<p>Foo {{bar}}</p>', ['bar' => 'BOB'], '<p>Foo BOB</p>'];
        yield ['<p>Foo {{bar}} {{cake}}</p>', ['bar' => 'BOB'], '<p>Foo BOB </p>'];
        yield ['{{[paragraph]}}', ['content' => 'FOO'], '<p>FOO</p>'];
        yield ['{{[paragraph]}}{{[paragraph]}}', ['content' => 'FOO'], '<p>FOO</p><p>FOO</p>'];
    }
}
