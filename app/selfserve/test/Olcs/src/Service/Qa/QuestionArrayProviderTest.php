<?php

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\QuestionArrayProvider;
use RuntimeException;

class QuestionArrayProviderTest extends MockeryTestCase
{
    public function testGetWithHtmlEscapeFilter()
    {
        $questionKey = 'questionKey';

        $questionParameters = [
            'questionParameter1',
            'questionParameter2'
        ];

        $question = [
            'filter' => 'htmlEscape',
            'translateableText' => [
                'key' => $questionKey,
                'parameters' => $questionParameters
            ]
        ];

        $sut = new QuestionArrayProvider();

        $expectedArray = [
            'question' => $questionKey,
            'questionArgs' => $questionParameters
        ];

        $this->assertEquals(
            $expectedArray,
            $sut->get($question)
        );
    }

    public function testExceptionOnRawFilter()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(QuestionArrayProvider::ONLY_HTML_ESCAPE_SUPPORTED);

        $question = [
            'filter' => 'raw',
            'translateableText' => [
                'key' => 'questionKey',
                'parameters' => [
                    'questionParameter1',
                    'questionParameter2'
                ]
            ]
        ];

        $sut = new QuestionArrayProvider();
        $sut->get($question);
    }
}
