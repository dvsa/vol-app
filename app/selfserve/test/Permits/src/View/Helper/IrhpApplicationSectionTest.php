<?php

declare(strict_types=1);

namespace PermitsTest\View\Helper;

use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\View\Helper\IrhpApplicationSection;
use Laminas\View\Model\ViewModel;

/**
 * Irhp Application Section Test
 */
class IrhpApplicationSectionTest extends MockeryTestCase
{
    public function testInvokeWithoutData(): void
    {
        $application = $questionAnswer = $expected = [];

        $sut = new IrhpApplicationSection();
        $response = $sut($application, $questionAnswer);

        $this->assertEquals($expected, $response);
    }

    public function testInvokeWithoutQaData(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestInvokeWithData')]
    public function testInvokeWithData(array $application, array $questionAnswer, array $expected): void
    {
        $sut = new IrhpApplicationSection();

        $response = $sut($application, $questionAnswer);

        foreach ($response as $i => $value) {
            $this->assertInstanceOf(ViewModel::class, $value);
            $this->assertEquals($expected[$i]['template'], $value->getTemplate());
            $this->assertEquals($expected[$i]['variables'], (array)$value->getVariables());
        }
    }

    /**
     * @return ((((int|string)[]|bool|string)[]|string|true)[]|int)[][][]
     *
     * @psalm-return array{'application path enabled - with q&a data': array{application: array{id: 100, irhpPermitType: array{isApplicationPathEnabled: true}}, questionAnswer: list{array{slug: 'qa', questionShort: 'qa-question-short', status: 'section_sts_com'}, array{slug: 'custom-check-answers', questionShort: 'check-answers-question-short', status: 'section_sts_nys'}, array{slug: 'custom-declaration', questionShort: 'declaration-question-short', status: 'section_sts_csy'}}, expected: list{array{template: 'partials/overview_section', variables: array{enabled: true, status: 'Completed', statusColour: 'green', name: 'qa-question-short', route: 'permits/application/question', routeParams: array{id: 100, slug: 'qa'}}}, array{template: 'partials/overview_section', variables: array{enabled: true, status: 'Not started yet', statusColour: 'grey', name: 'check-answers-question-short', route: 'permits/application/check-answers', routeParams: array{id: 100}}}, array{template: 'partials/overview_section', variables: array{enabled: false, status: 'Can't start yet', statusColour: 'grey', name: 'declaration-question-short', route: 'permits/application/declaration', routeParams: array{id: 100}}}}}}
     */
    public static function dpTestInvokeWithData(): array
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
        ];
    }
}
