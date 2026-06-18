<?php

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use PHPUnit\Framework\TestCase;
use Dvsa\Olcs\Transfer\Command\Document\CreateLetter;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;

class CreateLetterTest extends TestCase
{
    use CommandTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof CommandTest;
    }

    /**
     * @inheritDoc
     */
    protected function createBlankDto()
    {
        return new CreateLetter();
    }

    /**
     * @inheritDoc
     */
    protected function getValidFieldValues()
    {
        return [
            'template' => [
                '1',
                '1001',
            ],
            'data' => [
                'some-string',
                '191',
                ['an-array'],
                '10.1'
            ],
            'disableBookmarks' => [
                true,
                false,
            ],
            'meta' => [
                'a-string',
                21098,
                ['some-array'],
                90.1
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getInvalidFieldValues()
    {
        return [
            'template' => [
                ['array' => 'stuff'],
                []
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getFilterTransformations()
    {
        return [
            'template' => [
                [1, '1'],
                ['2 ', '2']
            ],
            'disableBookmarks' => [
                [1, true],
                [0, false]
            ]
        ];
    }
}
