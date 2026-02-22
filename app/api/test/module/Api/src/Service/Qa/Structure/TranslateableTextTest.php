<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * TranslateableTextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TranslateableTextTest extends MockeryTestCase
{
    private $key;

    private $parameter1Representation;

    private $parameter1;

    private $parameter2Representation;

    private $parameter2;

    private $translateableText;

    public function setUp(): void
    {
        $this->key = 'translateableTextKey';

        $this->parameter1Representation = [
            'value' => 'parameter1Value',
            'formatter' => 'parameter1Formatter'
        ];

        $this->parameter1 = m::mock(TranslateableTextParameter::class);
        $this->parameter1->shouldReceive('getRepresentation')
            ->andReturn($this->parameter1Representation);

        $this->parameter2Representation = [
            'value' => 'parameter2Value',
            'formatter' => 'parameter2Formatter'
        ];

        $this->parameter2 = m::mock(TranslateableTextParameter::class);
        $this->parameter2->shouldReceive('getRepresentation')
            ->andReturn($this->parameter2Representation);

        $this->translateableText = new TranslateableText($this->key);
        $this->translateableText->addParameter($this->parameter1);
        $this->translateableText->addParameter($this->parameter2);
    }

    public function testGetRepresentation(): void
    {
        $expectedRepresentation = [
            'key' => $this->key,
            'parameters' => [
                $this->parameter1Representation,
                $this->parameter2Representation
            ]
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $this->translateableText->getRepresentation()
        );
    }

    public function testSetKey(): void
    {
        $newKey = 'new-key';

        $this->translateableText->setKey($newKey);

        $expectedRepresentation = [
            'key' => $newKey,
            'parameters' => [
                $this->parameter1Representation,
                $this->parameter2Representation
            ]
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $this->translateableText->getRepresentation()
        );
    }

    public function testGetKey(): void
    {
        $this->assertEquals(
            $this->key,
            $this->translateableText->getKey()
        );
    }

    public function testGetParameter(): void
    {
        $this->assertSame(
            $this->parameter1,
            $this->translateableText->getParameter(0)
        );

        $this->assertSame(
            $this->parameter2,
            $this->translateableText->getParameter(1)
        );
    }

    public function testGetParameterBadIndex(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No parameter exists at index 4');

        $this->translateableText->getParameter(4, 'foo');
    }
}
