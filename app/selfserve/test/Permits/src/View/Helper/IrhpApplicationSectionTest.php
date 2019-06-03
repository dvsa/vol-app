<?php

namespace PermitsTest\View\Helper;

use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\View\Helper\IrhpApplicationSection;
use Zend\View\Model\ViewModel;

/**
 * Irhp Application Section Test
 */
class IrhpApplicationSectionTest extends MockeryTestCase
{
    public function testInvokeWithoutData()
    {
        $application = $questionAnswer = $expected = [];

        $sut = new IrhpApplicationSection();
        $response = $sut($application, $questionAnswer);

        $this->assertEquals($expected, $response);
    }

    public function testInvokeWithoutQaData()
    {
        $application = [
            'id' => 100,
            'irhpPermitType' => [
                'isApplicationPathEnabled' => true,
            ],
        ];
        $questionAnswer = $expected = [];

        $sut = new IrhpApplicationSection();
        $response = $sut($application, $questionAnswer);

        $this->assertEquals($expected, $response);
    }

    /**
     * @dataProvider dpTestInvokeWithData
     */
    public function testInvokeWithData($application, $questionAnswer, $expected)
    {
        $sut = new IrhpApplicationSection();

        $response = $sut($application, $questionAnswer);

        foreach ($response as $i => $value) {
            $this->assertInstanceOf(ViewModel::class, $value);
            $this->assertEquals($expected[$i]['template'], $value->getTemplate());
            $this->assertEquals($expected[$i]['variables'], (array)$value->getVariables());
        }
    }

    public function dpTestInvokeWithData()
    {
        return [
            'application path enabled - with q&a data' => [
                'application' => [
                    'id' => 100,
                    'irhpPermitType' => [
                        'isApplicationPathEnabled' => true,
                    ],
                ],
                'questionAnswer' => [
                    // licence
                    [
                        'slug' => 'custom-licence',
                        'questionShort' => 'licence-question-short',
                        'status' => IrhpApplicationSection::SECTION_COMPLETION_COMPLETED,
                    ],
                    // question / answer
                    [
                        'slug' => 'qa',
                        'questionShort' => 'qa-question-short',
                        'status' => IrhpApplicationSection::SECTION_COMPLETION_COMPLETED,
                    ],
                    // check answers
                    [
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'check-answers-question-short',
                        'status' => IrhpApplicationSection::SECTION_COMPLETION_NOT_STARTED,
                    ],
                    // declaration
                    [
                        'slug' => 'custom-declaration',
                        'questionShort' => 'declaration-question-short',
                        'status' => IrhpApplicationSection::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                'expected' => [
                    // licence
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => true,
                            'status' => 'Completed',
                            'statusColour' => 'green',
                            'name' => 'licence-question-short',
                            'route' => IrhpApplicationSection::ROUTE_LICENCE,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                    // question / answer
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => true,
                            'status' => 'Completed',
                            'statusColour' => 'green',
                            'name' => 'qa-question-short',
                            'route' => IrhpApplicationSection::ROUTE_QUESTION,
                            'routeParams' => [
                                'id' => 100,
                                'slug' => 'qa',
                            ],
                        ]
                    ],
                    // check answers
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => true,
                            'status' => 'Not started yet',
                            'statusColour' => 'grey',
                            'name' => 'check-answers-question-short',
                            'route' => IrhpApplicationSection::ROUTE_CHECK_ANSWERS,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                    // declaration
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => false,
                            'status' => 'Can\'t start yet',
                            'statusColour' => 'grey',
                            'name' => 'declaration-question-short',
                            'route' => IrhpApplicationSection::ROUTE_DECLARATION,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                ],
            ],
            'application path disabled - bilateral' => [
                'application' => [
                    'id' => 100,
                    'irhpPermitType' => [
                        'isApplicationPathEnabled' => false,
                        'id' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    ],
                    'sectionCompletion' => [
                        'licence' => IrhpApplicationSection::SECTION_COMPLETION_COMPLETED,
                        'countries' => IrhpApplicationSection::SECTION_COMPLETION_COMPLETED,
                        'permitsRequired' => IrhpApplicationSection::SECTION_COMPLETION_NOT_STARTED,
                        'checkedAnswers' => IrhpApplicationSection::SECTION_COMPLETION_CANNOT_START,
                        'declaration' => IrhpApplicationSection::SECTION_COMPLETION_CANNOT_START,
                    ]
                ],
                'questionAnswer' => [],
                'expected' => [
                    // licence
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => true,
                            'status' => 'Completed',
                            'statusColour' => 'green',
                            'name' => 'section.name.application/licence',
                            'route' => IrhpApplicationSection::ROUTE_LICENCE,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                    // countries
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => true,
                            'status' => 'Completed',
                            'statusColour' => 'green',
                            'name' => 'section.name.application/countries',
                            'route' => IrhpApplicationSection::ROUTE_COUNTRIES,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                    // no of permits
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => true,
                            'status' => 'Not started yet',
                            'statusColour' => 'grey',
                            'name' => 'section.name.application/no-of-permits',
                            'route' => IrhpApplicationSection::ROUTE_NO_OF_PERMITS,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                    // check answers
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => false,
                            'status' => 'Can\'t start yet',
                            'statusColour' => 'grey',
                            'name' => 'section.name.application/check-answers',
                            'route' => IrhpApplicationSection::ROUTE_CHECK_ANSWERS,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                    // declaration
                    [
                        'template' => 'partials/overview_section',
                        'variables' => [
                            'enabled' => false,
                            'status' => 'Can\'t start yet',
                            'statusColour' => 'grey',
                            'name' => 'section.name.application/declaration',
                            'route' => IrhpApplicationSection::ROUTE_DECLARATION,
                            'routeParams' => [
                                'id' => 100,
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
