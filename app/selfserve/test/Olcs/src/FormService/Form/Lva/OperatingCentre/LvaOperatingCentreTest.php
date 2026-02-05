<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;

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
        'adPlacedLater' => 'lva-oc-adplaced-l-selfserve',
    ];

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);

        $mockTranslator = m::mock(TranslationHelperService::class);
        $mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => 'translated-' . $string
            )
            ->shouldReceive('translateReplace')
            ->andReturnUsing(
                fn($string, $vars) => 'translated-' . $string . '-' . implode('-', $vars)
            );

        $mockUrl = m::mock(UrlHelperService::class);
        $mockUrl->shouldReceive('fromRoute')
            ->andReturnUsing(
                fn($route, $params) => $route . '-' . implode('-', $params)
            );

        $this->sut = new LvaOperatingCentre($this->formHelper, $mockTranslator, $mockUrl);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('paramsProvider')]
    public function testAlterFormNi(array $params): void
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

        // Mock the collection of inputs for searchPostcode
        $inputsCollection = m::mock(CollectionInputFilter::class);
        $inputsCollection->shouldReceive('getInputs')->andReturn([m::mock(InputInterface::class)]);

        // Mock the InputFilter for address
        $addressInputFilter = m::mock(InputFilterInterface::class);
        $addressInputFilter->shouldReceive('get')->with('postcode')->andReturn($postcodeInput);
        $addressInputFilter->shouldReceive('get')->with('searchPostcode')->andReturn($inputsCollection);

        // Mock the main InputFilter
        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('get')->with('address')->andReturn($addressInputFilter);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('gbParamsProvider')]
    public function testAlterFormGb(array $params): void
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

        // Mock the collection of inputs for searchPostcode
        $inputsCollection = m::mock(CollectionInputFilter::class);
        $inputsCollection->shouldReceive('getInputs')->andReturn([m::mock(InputInterface::class)]);

        // Mock the InputFilter for address
        $addressInputFilter = m::mock(InputFilterInterface::class);
        $addressInputFilter->shouldReceive('get')->with('postcode')->andReturn($postcodeInput);
        $addressInputFilter->shouldReceive('get')->with('searchPostcode')->andReturn($inputsCollection);

        // Mock the main InputFilter
        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('get')->with('address')->andReturn($addressInputFilter);

        $form->shouldReceive('getInputFilter')->andReturn($inputFilter);

        // ... existing mocks ...

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
     * @return ((int|int[])[]|bool|int|mixed|string)[][][]
     *
     * @psalm-return list{list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, niFlag: 'Y', licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, trafficArea: array{isNi: 1}, licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, licence: array{trafficArea: array{isNi: 1}}, licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, licence: array{trafficArea: array{isNi: 1}}, licNo: 'AB12345', applicationId: 111, isVariation: true, templateFile: string, guide: mixed}}}
     */
    public static function paramsProvider(): array
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_NI_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_NI_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_NI_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_NI_VAR),
                    'guide' => static::GUIDE_NI_VAR
                ]
            ]
        ];
    }

    /**
     * @return ((int|int[])[]|bool|int|mixed|string)[][][]
     *
     * @psalm-return list{list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, niFlag: 'N', licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, trafficArea: array{isNi: 0}, licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, licence: array{trafficArea: array{isNi: 0}}, licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, licNo: 'AB12345', applicationId: 111, isVariation: false, templateFile: string, guide: mixed}}, list{array{id: 124, isPsv: false, canAddAnother: true, canUpdateAddress: true, wouldIncreaseRequireAdditionalAdvertisement: false, licNo: 'AB12345', applicationId: 111, isVariation: true, templateFile: string, guide: mixed}}}
     */
    public static function gbParamsProvider(): array
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_GB_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_GB_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_GB_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_GB_NEW),
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
                    'templateFile' => base64_encode((string) static::TEMPLATE_FILE_GB_VAR),
                    'guide' => static::GUIDE_GB_VAR
                ]
            ]
        ];
    }
}
