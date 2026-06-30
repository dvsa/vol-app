<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\Text;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TextTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $name = 'qaElement';

        $expectedInputSpecification = [
            'id' => 'qaText',
            'name' => $name,
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => []
        ];

        $sut = new Text($name);

        $this->assertEquals(
            $expectedInputSpecification,
            $sut->getInputSpecification()
        );
    }
}
