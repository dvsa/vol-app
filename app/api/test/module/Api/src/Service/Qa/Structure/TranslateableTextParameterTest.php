<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameter;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TranslateableTextParameterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TranslateableTextParameterTest extends MockeryTestCase
{
    private $value;

    private $formatter;

    public function setUp(): void
    {
        $this->value = 'value';

        $this->formatter = 'formatter';
    }

    public function testGetRepresentationWithFormatter(): void
    {
        $translateableTextParameter = new TranslateableTextParameter($this->value, $this->formatter);

        $expectedRepresentation = [
            'value' => $this->value,
            'formatter' => $this->formatter
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $translateableTextParameter->getRepresentation()
        );
    }

    public function testGetRepresentationWithoutFormatter(): void
    {
        $translateableTextParameter = new TranslateableTextParameter($this->value, null);

        $expectedRepresentation = [
            'value' => $this->value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $translateableTextParameter->getRepresentation()
        );
    }

    public function testSetValue(): void
    {
        $translateableTextParameter = new TranslateableTextParameter($this->value, $this->formatter);

        $newValue = 'newValue';

        $expectedRepresentation = [
            'value' => $newValue,
            'formatter' => $this->formatter
        ];

        $translateableTextParameter->setValue($newValue);

        $this->assertEquals(
            $expectedRepresentation,
            $translateableTextParameter->getRepresentation()
        );
    }
}
