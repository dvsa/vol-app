<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SubmissionSectionTable;
use Olcs\View\Helper\SubmissionSectionMultipleTables;
use Olcs\View\Helper\SubmissionSectionTableFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SubmissionSectionMultipleTables
 * @package OlcsTest\View\Helper
 */
final class SubmissionSectionMultipleTablesTest extends TestCase
{
    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvoke')]
    public function testInvoke(mixed $input, mixed $expected): void
    {
        $sut = new SubmissionSectionMultipleTables();

        $translatorMock = m::mock(\Laminas\I18n\Translator\Translator::class);
        $translatorMock->shouldReceive('translate')->with(m::type('string'))->andReturn('foo');

        $sut->setTranslator($translatorMock);

        $mockView = m::mock(\Laminas\View\Renderer\PhpRenderer::class);

        $mockViewHelper = m::mock(\Olcs\View\Helper\SubmissionSectionMultipleTables::class);
        $mockTableHelper = m::mock(\Olcs\View\Helper\SubmissionSectionTable::class);
        $mockTableHelper->shouldReceive('__invoke')->andReturn('<table></table>');

        $mockViewHelper->shouldReceive('__invoke');
        $mockView->shouldReceive('plugin')->andReturn($mockViewHelper);
        $mockView->shouldReceive('SubmissionSectionTable')->andReturn($mockTableHelper);
        $mockView->shouldReceive('render');

        $sut->setView($mockView);

        $result = $sut(
            $input['submissionSection'],
            $input['data']
        );

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public static function provideInvoke(): \Iterator
    {
        yield [
            ['submissionSection' => 'introduction', 'data' => ['data' => []]], null
        ];
        yield [
            ['submissionSection' => '', 'data' => ['data' => []]], ''
        ];
        yield [
            [
                'submissionSection' => 'condition-and-undertakings',
                'data' => [
                    'sectionId' => 'conditions-and-undertakings',
                    'data' => [
                        'tables' => [
                            'conditions' => [
                                0 => ['id' => 1],
                                1 => ['id' => 2]
                            ]
                        ]
                    ]
                ]
            ],
            null
        ];
    }
}
