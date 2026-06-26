<?php

namespace CommonTest\Form\View\Helper\Readonly;

use Common\Form\Elements;
use Common\Form\View\Helper;
use Common\Form\View\Helper\Readonly\FormFieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Form\View\Helper\Readonly\FormFieldset
 */
class FormFieldsetTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestInvoke
     */
    public function testInvoke($element, $expect): void
    {
        $mockFormCollection = m::mock(Helper\FormCollection::class);
        $mockFormCollection->shouldReceive('render')->andReturn('FORM_COLLECTION_RENDER_RESULT');

        $mockFileUploadList = m::mock(Helper\Readonly\FormFileUploadList::class);
        $mockFileUploadList->shouldReceive('render')->andReturn('FORM_FILE_UPLOAD_LIST_RENDER_RESULT');

        $mockView = m::mock(\Laminas\View\Renderer\PhpRenderer::class);
        $mockView
            ->shouldReceive('plugin')->with('FormCollection')->andReturn($mockFormCollection)
            ->shouldReceive('plugin')
            ->with(Helper\Readonly\FormFileUploadList::class)
            ->andReturn($mockFileUploadList);

        $sut = new FormFieldset();
        $sut->setView($mockView);

        static::assertEquals($expect, $sut($element));
    }

    /**
     * @return (m\LegacyMockInterface&m\MockInterface&Elements\Types\FileUploadList|m\LegacyMockInterface&m\MockInterface&\Laminas\Form\FieldsetInterface|string)[][]
     *
     * @psalm-return list{array{element: m\LegacyMockInterface&m\MockInterface&\Laminas\Form\FieldsetInterface, expect: 'FORM_COLLECTION_RENDER_RESULT'}, array{element: m\LegacyMockInterface&m\MockInterface&Elements\Types\FileUploadList, expect: 'FORM_FILE_UPLOAD_LIST_RENDER_RESULT'}}
     */
    public function dpTestInvoke(): array
    {
        return [
            [
                'element' => m::mock(\Laminas\Form\FieldsetInterface::class),
                'expect' => 'FORM_COLLECTION_RENDER_RESULT',
            ],
            [
                'element' => m::mock(Elements\Types\FileUploadList::class),
                'expect' => 'FORM_FILE_UPLOAD_LIST_RENDER_RESULT',
            ],
        ];
    }
}
