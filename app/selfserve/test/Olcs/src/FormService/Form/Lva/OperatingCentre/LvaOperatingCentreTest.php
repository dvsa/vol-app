<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\ElementInterface;
use Laminas\InputFilter\InputFilterInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;
use Common\Service\Helper\FormHelperService;

class LvaOperatingCentreTest extends MockeryTestCase
{
    protected const TEMPLATE_FILE_NI_VAR = 'advertising-your-operating-centre-ni-var';
    protected const TEMPLATE_FILE_NI_NEW = 'advertising-your-operating-centre-ni-new';
    protected const TEMPLATE_FILE_GB_NEW = 'default-guide-oc-advert-gb-new';
    protected const TEMPLATE_FILE_GB_VAR = 'advertising-your-operating-centre-gb-var';

    protected const GUIDE_NI_VAR = 'advertising-your-operating-centre-ni-var';
    protected const GUIDE_NI_NEW = 'advertising-your-operating-centre-ni-new';
    protected const GUIDE_GB_NEW = 'advertising-your-operating-centre-gb-new';
    protected const GUIDE_GB_VAR = 'advertising-your-operating-centre-gb-var';
    protected $sut;

    protected $formHelper;

    protected $valueOptions = [
        'adPlaced' => 'lva-oc-adplaced-y-selfserve',
        'adSendByPost' => 'lva-oc-adplaced-n-selfserve',
        'adPlacedLater' => 'lva-oc-adplaced-l-selfserve',
    ];

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);

        $mockTranslator = m::mock(TranslationHelperService::class);
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

        $mockUrl = m::mock(UrlHelperService::class);
        $mockUrl->shouldReceive('fromRoute')
            ->andReturnUsing(
                function ($route, $params) {
                    return $route . '-' . implode('-', $params);
                }
            );

        $this->sut = new LvaOperatingCentre($this->formHelper, $mockTranslator, $mockUrl);
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
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('postcode')
                    ->andReturn(
                        m::mock(ElementInterface::class)
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

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('guidance')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setValue')
                            ->with(
                                'translated-markup-lva-oc-ad-placed-label-selfserve'
                                . '-getfile-'
                                . $params['templateFile']
                                . '-guides/guide-'
                                . $params['guide']
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $postcodeInput = m::mock(InputInterface::class);
        $postcodeInput->shouldReceive('setRequired')->with(false)->once();

        // Mock for searchPostcode input and its setRequired and setAllowEmpty methods
        $searchPostcodeInput = m::mock(InputInterface::class);
        $searchPostcodeInput->shouldReceive('setRequired')->with(false)->once();
        $searchPostcodeInput->shouldReceive('setAllowEmpty')->with(true)->once();

        // Mock the collection of inputs for searchPostcode
        $inputsCollection = m::mock(CollectionInputFilter::class);
        $inputsCollection->shouldReceive('getInputs')->andReturn([$searchPostcodeInput]);

        // Mock the InputFilter for address
        $addressInputFilter = m::mock(InputFilterInterface::class);
        $addressInputFilter->shouldReceive('get')->with('postcode')->andReturn($postcodeInput);
        $addressInputFilter->shouldReceive('get')->with('searchPostcode')->andReturn($inputsCollection);

        // Mock the main InputFilter
        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('get')->with('address')->andReturn($addressInputFilter);

        $adSendByPost = m::mock(ElementInterface::class);
        $adSendByPost->shouldReceive('setValue')
            ->once()
            ->with(
                'translated-markup-lva-oc-ad-send-by-post-text'
                . '-Department for Infrastructure<br />The Central Licensing Office<br />PO Box 180'
                . '<br />Leeds<br />LS9 1BU'
                . '-: <b>AB12345/111</b>'
            )
            ->getMock();

        $form->shouldReceive('getInputFilter')->andReturn($inputFilter);

        $radio = m::mock(ElementInterface::class)
            ->shouldReceive('getValueOptions')
            ->andReturn([])
            ->once()
            ->shouldReceive('setValueOptions')
            ->with($this->valueOptions)
            ->once()
            ->getMock();

        $advertisements = m::mock(ElementInterface::class)
            ->shouldReceive('get')
            ->with('adSendByPostContent')
            ->andReturn($adSendByPost)
            ->once()
            ->shouldReceive('get')
            ->with('radio')
            ->andReturn($radio)
            ->once()
            ->shouldReceive('get')
            ->with('adPlacedLaterContent')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setValue')
                    ->with('markup-lva-oc-ad-upload-later-text')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('setLabel')
            ->with('lva-operating-centre-radio-label')
            ->once()
            ->shouldReceive('setOption')
            ->with('hint', 'lva-operating-centre-radio-hint')
            ->once()
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
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('postcode')
                    ->andReturn(
                        m::mock(ElementInterface::class)
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

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('guidance')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setValue')
                            ->with(
                                'translated-markup-lva-oc-ad-placed-label-selfserve'
                                . '-getfile-'
                                . $params['templateFile']
                                . '-guides/guide-'
                                . $params['guide']
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $postcodeInput = m::mock(InputInterface::class);
        $postcodeInput->shouldReceive('setRequired')->with(false)->once();

        // Mock for searchPostcode input and its setRequired and setAllowEmpty methods
        $searchPostcodeInput = m::mock(InputInterface::class);
        $searchPostcodeInput->shouldReceive('setRequired')->with(false)->once();
        $searchPostcodeInput->shouldReceive('setAllowEmpty')->with(true)->once();

        // Mock the collection of inputs for searchPostcode
        $inputsCollection = m::mock(CollectionInputFilter::class);
        $inputsCollection->shouldReceive('getInputs')->andReturn([$searchPostcodeInput]);

        // Mock the InputFilter for address
        $addressInputFilter = m::mock(InputFilterInterface::class);
        $addressInputFilter->shouldReceive('get')->with('postcode')->andReturn($postcodeInput);
        $addressInputFilter->shouldReceive('get')->with('searchPostcode')->andReturn($inputsCollection);

        // Mock the main InputFilter
        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('get')->with('address')->andReturn($addressInputFilter);

        $form->shouldReceive('getInputFilter')->andReturn($inputFilter);

        // ... existing mocks ...


        $adSendByPost = m::mock(ElementInterface::class);
        $adSendByPost->shouldReceive('setValue')
            ->once()
            ->with(
                'translated-markup-lva-oc-ad-send-by-post-text'
                . '-Office of the Traffic Commissioner<br />The Central Licensing Office<br />Hillcrest House'
                . '<br />386 Harehills Lane<br />Leeds<br />LS9 6NF'
                . '-: <b>AB12345/111</b>'
            );

        $radio = m::mock(ElementInterface::class)
            ->shouldReceive('getValueOptions')
            ->andReturn([])
            ->once()
            ->shouldReceive('setValueOptions')
            ->with($this->valueOptions)
            ->once()
            ->getMock();

        $advertisements = m::mock(ElementInterface::class)
            ->shouldReceive('get')
            ->with('adSendByPostContent')
            ->andReturn($adSendByPost)
            ->once()
            ->shouldReceive('get')
            ->with('radio')
            ->andReturn($radio)
            ->once()
            ->shouldReceive('get')
            ->with('adPlacedLaterContent')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setValue')
                    ->with('markup-lva-oc-ad-upload-later-text')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('setLabel')
            ->with('lva-operating-centre-radio-label')
            ->once()
            ->shouldReceive('setOption')
            ->with('hint', 'lva-operating-centre-radio-hint')
            ->once()
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_NI_NEW),
                    'guide' => static::GUIDE_NI_NEW
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_NI_NEW),
                    'guide' => static::GUIDE_NI_NEW
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_NI_NEW),
                    'guide' => static::GUIDE_NI_NEW
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
                    'isVariation' => true,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_NI_VAR),
                    'guide' => static::GUIDE_NI_VAR
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_GB_NEW),
                    'guide' => static::GUIDE_GB_NEW
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_GB_NEW),
                    'guide' => static::GUIDE_GB_NEW
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_GB_NEW),
                    'guide' => static::GUIDE_GB_NEW
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
                    'isVariation' => false,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_GB_NEW),
                    'guide' => static::GUIDE_GB_NEW
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
                    'isVariation' => true,
                    'templateFile' => base64_encode(static::TEMPLATE_FILE_GB_VAR),
                    'guide' => static::GUIDE_GB_VAR
                ]
            ]
        ];
    }
}
