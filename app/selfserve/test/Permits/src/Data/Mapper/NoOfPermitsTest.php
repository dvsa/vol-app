<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Permits\Data\Mapper\NoOfPermits;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Zend\Form\Element\Number;
use RuntimeException;

/**
 * NoOfPermitsTest
 */
class NoOfPermitsTest extends TestCase
{
    public function testMapForFormOptions()
    {
        $form = new Form();

        $translatedGuidanceText = 'translatedGuidanceText';

        $translationHelperService = m::mock(TranslationHelperService::class);
        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.bilateral.no-of-permits.guidance',
                [7, 8]
            )
            ->andReturn($translatedGuidanceText);

        $data = [
            'irhpApplication' => [
                'irhpPermitType' => [
                    'id' => 4
                ],
                'licence' => [
                    'totAuthVehicles' => 7
                ],
                'irhpPermitApplications' => [
                    [
                        'permitsRequired' => 3,
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validFrom' => '2020-04-01',
                                'country' => [
                                    'id' => 'IT',
                                    'countryDesc' => 'Italy'
                                ]
                            ]
                        ]
                    ],
                    [
                        'permitsRequired' => 12,
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validFrom' => '2019-12-31',
                                'country' => [
                                    'id' => 'IT',
                                    'countryDesc' => 'Italy'
                                ]
                            ]
                        ]
                    ],
                    [
                        'permitsRequired' => 4,
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validFrom' => '2018-08-31',
                                'country' => [
                                    'id' => 'FR',
                                    'countryDesc' => 'France'
                                ]
                            ]
                        ]
                    ],
                    [
                        'permitsRequired' => null,
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validFrom' => '2019-12-01',
                                'country' => [
                                    'id' => 'FR',
                                    'countryDesc' => 'France'
                                ]
                            ]
                        ]
                    ],
                    [
                        'permitsRequired' => 8,
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'validFrom' => '2019-12-01',
                                'country' => [
                                    'id' => 'DE',
                                    'countryDesc' => 'Germany'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data = NoOfPermits::mapForFormOptions($data, $form, $translationHelperService);

        $this->assertCount(0, $form->getElements());
        $formFieldsets = $form->getFieldsets();
        $this->assertCount(1, $formFieldsets);
        $this->assertArrayHasKey('fields', $formFieldsets);

        $fields = $formFieldsets['fields'];
        $this->assertCount(0, $fields->getElements());
        $fieldsFieldsets = $fields->getFieldsets();
        $this->assertCount(1, $fieldsFieldsets);
        $this->assertArrayHasKey('permitsRequired', $fieldsFieldsets);

        $permitsRequired = $fieldsFieldsets['permitsRequired'];
        $permitsRequiredFieldsets = $permitsRequired->getFieldsets();
        $this->assertEquals(['FR', 'DE', 'IT'], array_keys($permitsRequiredFieldsets));

        $franceFieldset = $permitsRequiredFieldsets['FR'];
        $this->assertEquals('France', $franceFieldset->getLabel());
        $this->assertCount(0, $franceFieldset->getFieldsets());

        $franceElements = $franceFieldset->getElements();
        $this->assertEquals(['2018', '2019'], array_keys($franceElements));

        $franceElement2018 = $franceElements['2018'];
        $this->assertInstanceOf(Number::class, $franceElement2018);
        $this->assertEquals('for 2018', $franceElement2018->getLabel());
        $this->assertEquals(4, $franceElement2018->getValue());

        $franceElement2018Attributes = $franceElement2018->getAttributes();
        $this->assertArrayHasKey('min', $franceElement2018Attributes);
        $this->assertEquals(0, $franceElement2018Attributes['min']);

        $franceElement2019 = $franceElements['2019'];
        $this->assertEquals('for 2019', $franceElement2019->getLabel());
        $this->assertNull($franceElement2019->getValue());

        $franceElement2019Attributes = $franceElement2019->getAttributes();
        $this->assertInstanceOf(Number::class, $franceElement2019);
        $this->assertArrayHasKey('min', $franceElement2019Attributes);
        $this->assertEquals(0, $franceElement2019Attributes['min']);

        $germanyFieldset = $permitsRequiredFieldsets['DE'];
        $this->assertEquals('Germany', $germanyFieldset->getLabel());
        $this->assertCount(0, $germanyFieldset->getFieldsets());

        $germanyElements = $germanyFieldset->getElements();
        $this->assertEquals(['2019'], array_keys($germanyElements));

        $germanyElement2019 = $germanyElements['2019'];
        $this->assertInstanceOf(Number::class, $germanyElement2019);
        $this->assertEquals('for 2019', $germanyElement2019->getLabel());
        $this->assertEquals(8, $germanyElement2019->getValue());

        $germanyElement2019Attributes = $germanyElement2019->getAttributes();
        $this->assertArrayHasKey('min', $germanyElement2019Attributes);
        $this->assertEquals(0, $germanyElement2019Attributes['min']);
        
        $italyFieldset = $permitsRequiredFieldsets['IT'];
        $this->assertEquals('Italy', $italyFieldset->getLabel());
        $this->assertCount(0, $italyFieldset->getFieldsets());

        $italyElements = $italyFieldset->getElements();
        $this->assertEquals(['2019', '2020'], array_keys($italyElements));

        $italyElement2019 = $italyElements['2019'];
        $this->assertInstanceOf(Number::class, $italyElement2019);
        $this->assertEquals('for 2019', $italyElement2019->getLabel());
        $this->assertEquals(12, $italyElement2019->getValue());

        $italyElement2019Attributes = $italyElement2019->getAttributes();
        $this->assertArrayHasKey('min', $italyElement2019Attributes);
        $this->assertEquals(0, $italyElement2019Attributes['min']);

        $italyElement2020 = $italyElements['2020'];
        $this->assertEquals('for 2020', $italyElement2020->getLabel());
        $this->assertEquals(3, $italyElement2020->getValue());

        $italyElement2020Attributes = $italyElement2020->getAttributes();
        $this->assertInstanceOf(Number::class, $italyElement2020);
        $this->assertArrayHasKey('min', $italyElement2020Attributes);
        $this->assertEquals(0, $italyElement2020Attributes['min']);

        $this->assertArrayHasKey('guidance', $data);
        $this->assertEquals($data['guidance'], $translatedGuidanceText);
    }

    public function testExceptionOnIncorrectPermitType()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Permit type 3 is not supported by this mapper');

        $data = [
            'irhpApplication' => [
                'irhpPermitType' => [
                    'id' => 3
                ]
            ]
        ];

        $form = new Form();
        $translationHelperService = m::mock(TranslationHelperService::class);

        $data = NoOfPermits::mapForFormOptions($data, $form, $translationHelperService);
    }
}
