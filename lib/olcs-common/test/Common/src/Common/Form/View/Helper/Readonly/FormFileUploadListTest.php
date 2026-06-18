<?php

namespace CommonTest\Form\View\Helper\Readonly;

use Common\Form\Elements\Types\Address;
use Common\Form\Elements\Types\FileUploadList;
use Common\Form\Elements\Types\FileUploadListItem;
use Common\Form\View\Helper;
use Common\Form\View\Helper\Readonly\FormFileUploadList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element;
use Laminas\Form\FieldsetInterface;

/**
 * @covers \Common\Form\View\Helper\Readonly\FormFileUploadList
 */
class FormFileUploadListTest extends MockeryTestCase
{
    public function testRenderInvalidElement(): void
    {
        $this->expectException(\Exception::class);

        $sut = new FormFileUploadList();
        $sut->render(m::mock(FieldsetInterface::class));
    }

    public function testRenderNotItems(): void
    {
        $sut = new FormFileUploadList();

        static::assertEquals('', $sut(new FileUploadList()));
    }

    public function testRender(): void
    {
        /** @var Element | m\MockInterface $mockUplElmChildItem */
        $mockUplElmChildItem = m::mock(Element::class)->makePartial();
        $mockUplElmChildItem->setName("unit_elm1");
        $mockUplElmChildItem->shouldReceive('setOption')->with('disable_html_escape', true)->times(4);

        $mockUplElmChildItem2 = (clone $mockUplElmChildItem);
        $mockUplElmChildItem2->setName("unit_elm2");

        $mockFileItem = (new FileUploadListItem('unit_UplItem'))
            ->add($mockUplElmChildItem)
            ->add($mockUplElmChildItem2);

        $mockFileItem2 = clone $mockFileItem;
        $mockFileItem2->setName('unit_UplItem2');

        /** @var Address | m\MockInterface $mockOtherElm */
        $mockOtherElm = m::mock(Address::class);
        $mockOtherElm->shouldReceive('getName')->withAnyArgs()->andReturn('unit_Address');

        $list = (new FileUploadList())
            ->add($mockFileItem)
            ->add($mockOtherElm)
            ->add($mockFileItem2);

        $mockFormItem = m::mock(Helper\Readonly\FormItem::class);
        $mockFormItem->shouldReceive('render')->andReturn('_FORM_ITEM_RENDER_RESULT_');

        /** @var \Laminas\View\Renderer\PhpRenderer | m\MockInterface $mockView */
        $mockView = m::mock(\Laminas\View\Renderer\PhpRenderer::class);
        $mockView
            ->shouldReceive('plugin')->with('readonlyformitem')->andReturn($mockFormItem)
            ->shouldReceive('translate')->andReturnUsing(
                static fn($arg) => '_TRANSL_' . $arg
            );

        $sut = new FormFileUploadList();
        $sut->setView($mockView);

        static::assertEquals(
            '<div class="help__text">' .
            '<h3 class="file__heading">_TRANSL_common.file-upload.table.col.FileName</h3>' .
            '<ul class="js-upload-list">' .
            '<li class="file">_FORM_ITEM_RENDER_RESULT__FORM_ITEM_RENDER_RESULT_</li>' .
            '<li class="file">_FORM_ITEM_RENDER_RESULT__FORM_ITEM_RENDER_RESULT_</li>' .
            '</ul>' .
            '</div>',
            $sut($list)
        );
    }
}
