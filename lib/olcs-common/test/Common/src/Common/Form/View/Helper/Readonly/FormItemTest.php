<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper\Readonly;

use Common\Form\Elements;
use Common\Form\View\Helper\Readonly\FormItem;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\View\Helper\Readonly\FormItem::class)]
final class FormItemTest extends \PHPUnit\Framework\TestCase
{
    public function testInvokeSelf(): void
    {
        $sut = new FormItem();

        $this->assertSame($sut, $sut(null));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestRender')]
    public function testRender($element, $expect): void
    {
        $sut = new FormItem();
        $this->assertSame($expect, $sut->render($element));
    }

    /**
     * @return \Iterator<(int | string), array<(\Laminas\Form\Element | string)>>
     *
     * @psalm-return array{common: array{element: \Laminas\Form\Element, expect: 'foo&lt;br /&gt;'}, 'common;htmlEscapeOff': array{element: \Laminas\Form\Element, expect: 'foo<br />'}, ActionButton: array{element: Elements\InputFilters\ActionButton, expect: ''}, AttachFilesButton: array{element: Elements\Types\AttachFilesButton, expect: ''}, Button: array{element: \Laminas\Form\Element\Button, expect: ''}, 'input:submit': array{element: \Laminas\Form\Element\Submit, expect: ''}, 'input:hidden': array{element: \Laminas\Form\Element\Hidden, expect: ''}}
     */
    public static function dpTestRender(): \Iterator
    {
        yield 'common' => [
            'element' => new \Laminas\Form\Element()
                ->setValue('foo<br />'),
            'expect' => 'foo&lt;br /&gt;',
        ];
        yield 'common;htmlEscapeOff' => [
            'element' => new \Laminas\Form\Element()
                ->setValue('foo<br />')
                ->setOption('disable_html_escape', true),
            'expect' => 'foo<br />',
        ];
        yield 'ActionButton' => [
            'element' => (new Elements\InputFilters\ActionButton()),
            'expect' => '',
        ];
        yield 'AttachFilesButton' => [
            'element' => (new Elements\Types\AttachFilesButton()),
            'expect' => '',
        ];
        yield 'Button' => [
            'element' => (new \Laminas\Form\Element\Button()),
            'expect' => '',
        ];
        yield 'input:submit' => [
            'element' => (new \Laminas\Form\Element\Submit()),
            'expect' => '',
        ];
        yield 'input:hidden' => [
            'element' => (new \Laminas\Form\Element\Hidden()),
            'expect' => '',
        ];
    }
}
