<?php

declare(strict_types=1);

namespace PermitsTest\View\Helper;

use Common\RefData;
use Permits\View\Helper\AnswerFormatter;
use Mockery as m;
use Laminas\View\Renderer\RendererInterface;

/**
 * Class AnswerFormatterTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class AnswerFormatterTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /** @var AnswerFormatter */
    private $sut;

    /** @var RendererInterface|m\MockInterface */
    private $view;

    #[\Override]
    public function setUp(): void
    {
        $this->view = m::mock(RendererInterface::class);
        $this->sut = new AnswerFormatter();
        $this->sut->setView($this->view);
    }

    public function testInvokeBoolean(): void
    {
        $input = [
            'question' => 'qanda.question',
            'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
            'answer' => [
                true, false, 1, 0,
            ],
            'escape' => true,
        ];

        $this->view->shouldReceive('translate')
            ->twice()
            ->with('Yes')
            ->andReturn('translated yes');

        $this->view->shouldReceive('escapeHtml')
            ->twice()
            ->with('translated yes')
            ->andReturn('escaped and translated yes');

        $this->view->shouldReceive('translate')
            ->twice()
            ->with('No')
            ->andReturn('translated no');

        $this->view->shouldReceive('escapeHtml')
            ->twice()
            ->with('translated no')
            ->andReturn('escaped and translated no');

        $expected =
            'escaped and translated yes' . AnswerFormatter::SEPARATOR .
            'escaped and translated no' . AnswerFormatter::SEPARATOR .
            'escaped and translated yes' . AnswerFormatter::SEPARATOR .
            'escaped and translated no';

        $this->assertEquals($expected, $this->sut->__invoke($input));
    }

    public function testInvokeBooleanNoEscape(): void
    {
        $input = [
            'question' => 'qanda.question',
            'questionType' => RefData::QUESTION_TYPE_BOOLEAN,
            'answer' => [
                true, false, 1, 0,
            ],
            'escape' => false,
        ];

        $this->view->shouldReceive('translate')
            ->twice()
            ->with('Yes')
            ->andReturn('translated yes');

        $this->view->shouldReceive('translate')
            ->twice()
            ->with('No')
            ->andReturn('translated no');

        $this->view->shouldReceive('escapeHtml')->never();

        $expected =
            'translated yes' . AnswerFormatter::SEPARATOR .
            'translated no' . AnswerFormatter::SEPARATOR .
            'translated yes' . AnswerFormatter::SEPARATOR .
            'translated no';

        $this->assertEquals($expected, $this->sut->__invoke($input));
    }

    public function testInvokeInteger(): void
    {
        $input = [
            'question' => 'qanda.question',
            'questionType' => RefData::QUESTION_TYPE_INTEGER,
            'answer' => [
                1, 0, 999,
            ]
        ];

        $output = '1' . AnswerFormatter::SEPARATOR . '0' . AnswerFormatter::SEPARATOR . '999';

        $this->assertEquals($output, $this->sut->__invoke($input));
    }

    /**
     * Tests a single answer is converted to an array containing one answer and is still processed correctly
     */
    public function testInvokeWithAnswerNotArray(): void
    {
        $input = [
            'question' => 'qanda.question',
            'questionType' => RefData::QUESTION_TYPE_INTEGER,
            'answer' => 1,
        ];

        $this->assertEquals(1, $this->sut->__invoke($input));
    }

    /**
     * Tests answer rendered as expected for string/custom
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpInvokeOther')]
    public function testInvokeOther(string $questionType): void
    {
        $input = [
            'question' => 'qanda.question',
            'questionType' => $questionType,
            'answer' => [
                1, 0, 'text',
            ],
            'escape' => true,
        ];

        $this->view->shouldReceive('translate')
            ->times(3)
            ->andReturnUsing(
                fn($arg) => 'translated ' . $arg
            );

        $this->view->shouldReceive('escapeHtml')
            ->times(3)
            ->andReturnUsing(
                fn($arg) => 'escaped and ' . $arg
            );

        $expected =
            'escaped and translated 1' . AnswerFormatter::SEPARATOR .
            'escaped and translated 0' . AnswerFormatter::SEPARATOR .
            'escaped and translated text';

        $this->assertEquals($expected, $this->sut->__invoke($input));
    }

    /**
     * Tests answer rendered as expected for string/custom
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpInvokeOther')]
    public function testInvokeOtherNoEscape(string $questionType): void
    {
        $input = [
            'question' => 'qanda.question',
            'questionType' => $questionType,
            'answer' => [
                1, 0, 'text',
            ],
            'escape' => false,
        ];

        $this->view->shouldReceive('translate')
            ->times(3)
            ->andReturnUsing(
                fn($arg) => 'translated ' . $arg
            );

        $this->view->shouldReceive('escapeHtml')->never();

        $expected =
            'translated 1' . AnswerFormatter::SEPARATOR .
            'translated 0' . AnswerFormatter::SEPARATOR .
            'translated text';

        $this->assertEquals($expected, $this->sut->__invoke($input));
    }

    /**
     * @return \Iterator<(int | string), array<string>>
     *
     * @psalm-return list{list{'question_type_string'}, list{'question_type_custom'}}
     */
    public static function dpInvokeOther(): \Iterator
    {
        yield [RefData::QUESTION_TYPE_STRING];
        yield [RefData::QUESTION_TYPE_CUSTOM];
    }
}
