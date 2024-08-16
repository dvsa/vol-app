<?php

/**
 * MoroccoFieldsetPopulator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Service\Permits\Bilateral;

use Common\Service\Qa\Custom\Bilateral\NoOfPermitsElement;
use Olcs\Service\Permits\Bilateral\MoroccoFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Fieldset;

/**
 * MoroccoFieldsetPopulator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class MoroccoFieldsetPopulatorTest extends TestCase
{
    public function testPopulate()
    {
        $caption = 'fields.caption';
        $value = 'fields.value';

        $fields = [
            'caption' => $caption,
            'value' => $value
        ];

        $fieldset = m::mock(Fieldset::class);

        $noOfPermitsElementParams = [
            'type' => NoOfPermitsElement::class,
            'name' => 'permitsRequired',
            'options' => [
                'label' => $fields['caption'],
            ],
            'attributes' => [
                'value' => $fields['value']
            ]
        ];

        $fieldset->shouldReceive('add')
            ->with($noOfPermitsElementParams)
            ->once();

        $moroccoFieldsetPopulator = new MoroccoFieldsetPopulator();

        $moroccoFieldsetPopulator->populate($fieldset, $fields);
    }
}
