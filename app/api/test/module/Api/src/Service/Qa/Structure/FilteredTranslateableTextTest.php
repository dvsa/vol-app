<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FilteredTranslateableTextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FilteredTranslateableTextTest extends MockeryTestCase
{
    private $filter;

    private $translateableText;

    private $filteredTranslateableText;

    public function setUp(): void
    {
        $this->filter = 'htmlEscape';

        $this->translateableText = m::mock(TranslateableText::class);

        $this->filteredTranslateableText = new FilteredTranslateableText($this->filter, $this->translateableText);
    }

    public function testGetRepresentation(): void
    {
        $translateableTextRepresentation = [
            'key' => 'translateableTextKey',
            'parameters' => [
                'translateableTextParameter1',
                'translateableTextParameter2'
            ]
        ];

        $filteredTranslateableTextRepresentation = [
            'filter' => $this->filter,
            'translateableText' => $translateableTextRepresentation
        ];

        $this->translateableText->shouldReceive('getRepresentation')
            ->andReturn($translateableTextRepresentation);

        $this->assertEquals(
            $filteredTranslateableTextRepresentation,
            $this->filteredTranslateableText->getRepresentation()
        );
    }

    public function testGetTranslateableText(): void
    {
        $this->assertEquals(
            $this->translateableText,
            $this->filteredTranslateableText->getTranslateableText()
        );
    }
}
