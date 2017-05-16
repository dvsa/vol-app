<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Common\FormService\FormServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;
use Zend\ServiceManager\ServiceManager;
use Common\Service\Helper\FormHelperService;

/**
 * Lva Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LvaOperatingCentreTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock(FormHelperService::class);

        $mockTranslator = m::mock();
        $mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                function ($string) {
                    return 'translated-' . $string;
                }
            )
            ->shouldReceive('translateReplace')
            ->andReturnUsing(
                function ($string, $vars) {
                    return 'translated-' . $string . '-' . implode('-', $vars);
                }
            );

        $mockUrl = m::mock();
        $mockUrl->shouldReceive('fromRoute')
            ->andReturnUsing(
                function ($route, $params) {
                    return $route . '-' . implode('-', $params);
                }
            );

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Helper\Translation', $mockTranslator);
        $sm->setService('Helper\Url', $mockUrl);

        $fsm = m::mock(FormServiceManager::class)->makePartial();
        $fsm->setServiceLocator($sm);

        $this->sut = new LvaOperatingCentre();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($fsm);
    }

    /**
     * @dataProvider paramsProvider
     */
    public function testAlterFormNi($params)
    {
        $form = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('address')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('postcode')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setOption')
                            ->with('shouldEscapeMessages', false)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $form->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('address')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('postcode')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setRequired')
                                    ->with(false)
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->once();

        $adSendByPost = m::mock();
        $adSendByPost->shouldReceive('setValue')
            ->once()
            ->with(
                'translated-markup-lva-oc-ad-send-by-post-text'
                . '-Department for Infrastructure<br />The Central Licensing Office<br />PO Box 180'
                . '<br />Leeds<br />LS9 1BU'
                . '-: <b>AB12345/111</b>'
            )
            ->getMock();

        $adPlaced = m::mock()
            ->shouldReceive('setLabel')
            ->with(
                'translated-markup-lva-oc-ad-placed-label-selfserve'
                . '-guides/guide'
                . '-advertising-your-operating-centre-ni-new'
            )
            ->once()
            ->shouldReceive('getValueOptions')
            ->andReturn(['1' => 'Yes'])
            ->once()
            ->shouldReceive('setValueOptions')
            ->with(['1' => 'lva-oc-adplaced-y-selfserve'])
            ->once()
            ->getMock();

        $advertisements = m::mock()
            ->shouldReceive('get')
            ->with('adSendByPost')
            ->andReturn($adSendByPost)
            ->once()
            ->shouldReceive('get')
            ->with('adPlaced')
            ->andReturn($adPlaced)
            ->once()
            ->shouldReceive('get')
            ->with('adUploadLater')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('markup-lva-oc-ad-upload-later-text')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('adPlacedPost')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setName')
                    ->with('adPlaced')
                    ->once()
                    ->shouldReceive('setValueOptions')
                    ->with(['lva-oc-adplaced-n-selfserve'])
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->shouldReceive('get')
            ->with('adPlacedLater')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setName')
                    ->with('adPlaced')
                    ->once()
                    ->shouldReceive('setValueOptions')
                    ->with(['2' => 'lva-oc-adplaced-l-selfserve'])
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock();

        $form->shouldReceive('get')
            ->with('advertisements')
            ->andReturn($advertisements);

        $this->sut->alterForm($form, $params);
    }

    /**
     * @dataProvider gbParamsProvider
     */
    public function testAlterFormGb($params)
    {
        $form = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('address')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('postcode')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setOption')
                            ->with('shouldEscapeMessages', false)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $form->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('address')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('postcode')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setRequired')
                                    ->with(false)
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->once();

        $adSendByPost = m::mock();
        $adSendByPost->shouldReceive('setValue')
            ->once()
            ->with(
                'translated-markup-lva-oc-ad-send-by-post-text'
                . '-Office of the Traffic Commissioner<br />The Central Licensing Office<br />Hillcrest House'
                . '<br />386 Harehills Lane<br />Leeds<br />LS9 6NF'
                . '-: <b>AB12345/111</b>'
            );

        $adPlaced = m::mock()
            ->shouldReceive('setLabel')
            ->with(
                'translated-markup-lva-oc-ad-placed-label-selfserve'
                . '-guides/guide'
                . '-advertising-your-operating-centre-gb-new'
            )
            ->once()
            ->shouldReceive('getValueOptions')
            ->andReturn(['1' => 'Yes'])
            ->once()
            ->shouldReceive('setValueOptions')
            ->with(['1' => 'lva-oc-adplaced-y-selfserve'])
            ->once()
            ->getMock();

        $advertisements = m::mock()
            ->shouldReceive('get')
            ->with('adSendByPost')
            ->andReturn($adSendByPost)
            ->once()
            ->shouldReceive('get')
            ->with('adPlaced')
            ->andReturn($adPlaced)
            ->once()
            ->shouldReceive('get')
            ->with('adUploadLater')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('markup-lva-oc-ad-upload-later-text')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('adPlacedPost')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setName')
                    ->with('adPlaced')
                    ->once()
                    ->shouldReceive('setValueOptions')
                    ->with(['lva-oc-adplaced-n-selfserve'])
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->shouldReceive('get')
            ->with('adPlacedLater')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setName')
                    ->with('adPlaced')
                    ->once()
                    ->shouldReceive('setValueOptions')
                    ->with(['2' => 'lva-oc-adplaced-l-selfserve'])
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock();

        $form->shouldReceive('get')
            ->with('advertisements')
            ->andReturn($advertisements);

        $this->sut->alterForm($form, $params);
    }

    public function paramsProvider()
    {
        return [
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'niFlag' => 'Y',
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ],
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'trafficArea' => [
                        'isNi' => 1
                    ],
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ],
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'licence' => [
                        'trafficArea' => [
                            'isNi' => 1
                        ],
                    ],
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ]
        ];
    }

    public function gbParamsProvider()
    {
        return [
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'niFlag' => 'N',
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ],
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'trafficArea' => [
                        'isNi' => 0
                    ],
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ],
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'licence' => [
                        'trafficArea' => [
                            'isNi' => 0
                        ]
                    ],
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ],
            [
                [
                    'id' => 124,
                    'isPsv' => false,
                    'canAddAnother' => true,
                    'canUpdateAddress' => true,
                    'wouldIncreaseRequireAdditionalAdvertisement' => false,
                    'licNo' => 'AB12345',
                    'applicationId' => 111,
                    'isVariation' => false
                ]
            ]
        ];
    }
}
