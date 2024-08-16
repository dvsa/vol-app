<?php

/**
 * NoOfPermitsElementGenerator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Service\Permits\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Form\Element\Permits\BilateralNoOfPermitsElement;
use Olcs\Service\Permits\Bilateral\NoOfPermitsElementGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Factory as FormFactory;

/**
 * NoOfPermitsElementGenerator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class NoOfPermitsElementGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $translatedLabel = 'Translated label';
        $permitsRequired = '21';

        $fieldData = [
            'cabotage' => 'cabotageValue',
            'journey' => 'journey_journeyValue',
            'value' => $permitsRequired
        ];

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('permits.irhp.range.type.cabotageValue.journeyValue')
            ->andReturn($translatedLabel);

        $bilateralNoOfPermitsElementParams = [
            'type' => BilateralNoOfPermitsElement::class,
            'name' => 'cabotageValue-journey_journeyValue',
            'options' => [
                'label' => $translatedLabel
            ],
            'attributes' => [
                'value' => $permitsRequired
            ]
        ];

        $noOfPermitsElement = m::mock(BilateralNoOfPermitsElement::class);

        $formFactory = m::mock(FormFactory::class);
        $formFactory->shouldReceive('create')
            ->with($bilateralNoOfPermitsElementParams)
            ->andReturn($noOfPermitsElement);

        $noOfPermitsElementGenerator = new NoOfPermitsElementGenerator($translator, $formFactory);

        $this->assertSame(
            $noOfPermitsElement,
            $noOfPermitsElementGenerator->generate($fieldData)
        );
    }
}
