<?php

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\OperatingCentre;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Hamcrest\Core\AnyOf;
use Laminas\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\RefData;

/**
 * Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentreTest extends MockeryTestCase
{
    /**
     * @dataProvider adProvider
     */
    public function testMapFromResult($adPlaced, $radio): void
    {
        $result = [
            'version' => 1,
            'noOfVehiclesRequired' => 10,
            'noOfTrailersRequired' => 11,
            'permission' => 13,
            'operatingCentre' => [
                'foo' => 'bar',
                'address' => [
                    'abc',
                    'countryCode' => ['id' => 'GB']
                ]
            ],
            'adPlaced' => $adPlaced,
            'adPlacedIn' => 'Donny Star',
            'adPlacedDate' => '2015-01-01'
        ];

        $expected = [
            'version' => 1,
            'data' => [
                'noOfVehiclesRequired' => 10,
                'noOfTrailersRequired' => 11,
                'permission' => [
                    'permission' => 13,
                ]
            ],
            'operatingCentre' => [
                'foo' => 'bar',
                'address' => [
                    'abc',
                    'countryCode' => ['id' => 'GB']
                ]
            ],
            'address' => [
                'abc',
                'countryCode' => 'GB'
            ],
            'advertisements' => [
                'radio' => $radio,
                'adPlacedContent' => [
                    'adPlacedIn' => 'Donny Star',
                    'adPlacedDate' => '2015-01-01'

                ]
            ]
        ];

        $this->assertEquals($expected, OperatingCentre::mapFromResult($result));
    }

    /**
     * @return (int|string)[][]
     *
     * @psalm-return list{list{1, 'adPlaced'}, list{2, 'adPlacedLater'}}
     */
    public function adProvider(): array
    {
        return [
            [RefData::AD_UPLOAD_NOW, OperatingCentre::VALUE_OPTION_AD_PLACED_NOW],
            [RefData::AD_UPLOAD_LATER, OperatingCentre::VALUE_OPTION_AD_UPLOAD_LATER]
        ];
    }

    /**
     * @dataProvider mapFromFormProvider
     */
    public function testMapFromForm($data, $expected): void
    {
        $this->assertEquals($expected, OperatingCentre::mapFromForm($data));
    }

    /**
     * @return ((int|string|string[])[]|int|string)[][][]
     *
     * @psalm-return list{list{array{version: 1, address: array{foo: 'bar'}, data: array{noOfVehiclesRequired: 10, noOfTrailersRequired: 11, permission: array{permission: 'Y'}}, advertisements: array{radio: 'adPlaced', adPlacedContent: array{adPlacedIn: 'Donny Star', adPlacedDate: '2015-01-01'}}}, array{version: 1, address: array{foo: 'bar'}, noOfVehiclesRequired: 10, noOfTrailersRequired: 11, permission: 'Y', adPlaced: 1, adPlacedIn: 'Donny Star', adPlacedDate: '2015-01-01', taIsOverridden: 'N'}}, list{array{version: 1, address: array{foo: 'bar'}, data: array{noOfVehiclesRequired: 10, noOfTrailersRequired: 11, permission: array{permission: 'Y'}}, advertisements: array{radio: 'adSendByPost', adPlacedContent: array{adPlacedIn: 'Donny Star', adPlacedDate: '2015-01-01'}}}, array{version: 1, address: array{foo: 'bar'}, noOfVehiclesRequired: 10, noOfTrailersRequired: 11, permission: 'Y', adPlaced: 0, adPlacedIn: 'Donny Star', adPlacedDate: '2015-01-01', taIsOverridden: 'N'}}, list{array{version: 1, address: array{foo: 'bar'}, data: array{noOfVehiclesRequired: 10, noOfTrailersRequired: 11, permission: array{permission: 'Y'}}, advertisements: array{radio: 'adPlacedLater', adPlacedContent: array{adPlacedIn: 'Donny Star', adPlacedDate: '2015-01-01'}}}, array{version: 1, address: array{foo: 'bar'}, noOfVehiclesRequired: 10, noOfTrailersRequired: 11, permission: 'Y', adPlaced: 2, adPlacedIn: 'Donny Star', adPlacedDate: '2015-01-01', taIsOverridden: 'N'}}}
     */
    public function mapFromFormProvider(): array
    {
        return [
            [
                [
                    'version' => 1,
                    'address' => ['foo' => 'bar'],
                    'data' => [
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 11,
                        'permission' => [
                            'permission' => 'Y'
                        ]
                    ],
                    'advertisements' => [
                        'radio' => OperatingCentre::VALUE_OPTION_AD_PLACED_NOW,
                        'adPlacedContent' => [
                            'adPlacedIn' => 'Donny Star',
                            'adPlacedDate' => '2015-01-01'
                        ]
                    ]
                ],
                [
                    'version' => 1,
                    'address' => ['foo' => 'bar'],
                    'noOfVehiclesRequired' => 10,
                    'noOfTrailersRequired' => 11,
                    'permission' => 'Y',
                    'adPlaced' => RefData::AD_UPLOAD_NOW,
                    'adPlacedIn' => 'Donny Star',
                    'adPlacedDate' => '2015-01-01',
                    'taIsOverridden' => 'N'

                ]
            ],
            [
                [
                    'version' => 1,
                    'address' => ['foo' => 'bar'],
                    'data' => [
                        'noOfVehiclesRequired' => 10,
                        'noOfTrailersRequired' => 11,
                        'permission' => [
                            'permission' => 'Y'
                        ]
                    ],
                    'advertisements' => [
                        'radio' => OperatingCentre::VALUE_OPTION_AD_UPLOAD_LATER,
                        'adPlacedContent' => [
                            'adPlacedIn' => 'Donny Star',
                            'adPlacedDate' => '2015-01-01'
                        ]
                    ]
                ],
                [
                    'version' => 1,
                    'address' => ['foo' => 'bar'],
                    'noOfVehiclesRequired' => 10,
                    'noOfTrailersRequired' => 11,
                    'permission' => 'Y',
                    'adPlaced' => RefData::AD_UPLOAD_LATER,
                    'adPlacedIn' => 'Donny Star',
                    'adPlacedDate' => '2015-01-01',
                    'taIsOverridden' => 'N'

                ]
            ]
        ];
    }

    public function testMapFormErrors(): void
    {
        $location = OperatingCentre::LOC_EXTERNAL;
        $form = m::mock(\Laminas\Form\Form::class);
        $fm = m::mock(FlashMessengerHelperService::class);
        $th = m::mock(TranslationHelperService::class);
        $th->shouldReceive('translateReplace')
            ->with('ERR_OC_PC_TA_GB', ['url'])
            ->andReturn('translated');
        $th->shouldReceive('translateReplace')
            ->with('ERR_TA_PSV_SR_EXTERNAL', ['Foo'])
            ->andReturn('translated 2');

        $expectedMessages = [
            'data' => [
                'noOfVehiclesRequired' => [
                    'bar1'
                ],
                'noOfTrailersRequired' => [
                    'bar2'
                ],
                'permission' => [
                    'permission' => [
                        'bar6'
                    ]
                ]
            ],
            'advertisements' => [
                'adPlacedIn' => [
                    'bar3'
                ],
                'adPlacedDate' => [
                    'bar4'
                ]
            ],
            'address' => [
                'postcode' => [
                    [
                        'ERR_OC_PC_TA_GB' => 'translated',
                        'ERR_TA_PSV_SR' => 'translated 2',
                    ]
                ]
            ]
        ];

        $errors = [
            'postcode' => [
                [
                    'ERR_OC_PC_TA_GB' => '{"current":"Foo","oc":"Bar"}',
                    'ERR_TA_PSV_SR' => 'Foo',
                ]
            ],
            'noOfVehiclesRequired' => [
                'foo' => 'bar1'
            ],
            'noOfTrailersRequired' => [
                'foo' => 'bar2'
            ],
            'adPlacedIn' => [
                'foo' => 'bar3'
            ],
            'adPlacedDate' => [
                'foo' => 'bar4'
            ],
            'permission' => [
                'foo' => 'bar6'
            ],
            'cake' => 'bar'
        ];

        $formActions = m::mock(\Laminas\Form\ElementInterface::class);
        $formActions->shouldReceive('setOption')->once()->with('shouldEscapeMessages', false);
        $form->shouldReceive('get')->once()->with('form-actions')->andReturn($formActions);

        $form->shouldReceive('setMessages')
            ->once()
            ->with($expectedMessages);

        $fm->shouldReceive('addCurrentErrorMessage')
            ->once()
            ->with('bar');

        OperatingCentre::mapFormErrors($form, $errors, $fm, $th, $location, 'url');
    }

    /**
     * @dataProvider mapFromPostProvider
     */
    public function testMapFromPost($data, $expected): void
    {
        $this->assertEquals($expected, OperatingCentre::mapFromPost($data));
    }

    /**
     * @return ((int|string|string[][][])[]|string)[][][]
     *
     * @psalm-return list{list{array{advertisements: array{radio: 'adSendByPost', adPlacedContent: array{file: array{list: list{'foo'}}}}, bar: 'cake'}, array{advertisements: array{radio: 'adSendByPost', uploadedFileCount: 1, adPlacedContent: array{file: array{list: list{'foo'}}}}, bar: 'cake'}}, list{array{advertisements: array{radio: 'adPlacedLater'}, bar: 'cake'}, array{advertisements: array{radio: 'adPlacedLater', uploadedFileCount: 0}, bar: 'cake'}}, list{array{advertisements: array{radio: 'adPlaced'}, bar: 'cake'}, array{advertisements: array{radio: 'adPlaced', uploadedFileCount: 0}, bar: 'cake'}}}
     */
    public function mapFromPostProvider(): array
    {
        return [
            [
                [
                    'advertisements' => [
                        'radio' => OperatingCentre::VALUE_OPTION_AD_UPLOAD_LATER,
                    ],
                    'bar' => 'cake'
                ],
                [
                    'advertisements' => [
                        'radio' => OperatingCentre::VALUE_OPTION_AD_UPLOAD_LATER,
                        'uploadedFileCount' => 0,
                    ],
                    'bar' => 'cake'
                ]
            ],
            [
                [
                    'advertisements' => [
                        'radio' => OperatingCentre::VALUE_OPTION_AD_PLACED_NOW
                    ],
                    'bar' => 'cake'
                ],
                [
                    'advertisements' => [
                        'radio' => OperatingCentre::VALUE_OPTION_AD_PLACED_NOW,
                        'uploadedFileCount' => 0,
                    ],
                    'bar' => 'cake'
                ]
            ],
        ];
    }

    /**
     * @dataProvider dpConfirmation
     */
    public function testMapFormErrorsConfirmation($location, $expected): void
    {
        $form = m::mock(Form::class);
        $errors = [
            'postcode' => [
                [
                    'ERR_OC_PC_TA_GB' => [
                        'current' => "current",
                        'oc' => "oc"
                    ]
                ]
            ]
        ];
        $mockFm = m::mock(FlashMessengerHelperService::class);
        $mockTranslatorService = m::mock(TranslationHelperService::class);
        $taGuidesUrl = "__TEST__";

        $mockTranslatorService->shouldReceive('translateReplace')
            ->with('ERR_OC_PC_TA_GB', ['__TEST__'])
            ->andReturn('translated');

        $mockTranslatorService->shouldReceive('translateReplace')
            ->with('ERR_TA_PSV_SR_EXTERNAL', ['Foo'])
            ->andReturn('translated 2');

        if ($location === OperatingCentre::LOC_INTERNAL) {
            $mockTranslatorService->shouldReceive('translate')->with('ERR_OC_PC_TA_GB-confirm')->once();
            $mockTranslatorService->shouldReceive('translate')->with('ERR_OC_PC_TA_GB-internalwarning')->once();
            $mockFormActions = m::mock(\Laminas\Form\ElementInterface::class);
            $mockFormActions->shouldReceive('setOption')->once()->with('shouldEscapeMessages', false);
            $mockFormActions->shouldReceive('add')->once();
            $form->shouldReceive('get')->with('form-actions')->twice()->andReturn($mockFormActions);
        } else {
            $mockFormActions = m::mock(\Laminas\Form\ElementInterface::class);
            $mockFormActions->shouldReceive('setOption')->once()->with('shouldEscapeMessages', false);
            $form->shouldReceive('get')->once()->with('form-actions')->andReturn($mockFormActions);
        }

        $form->shouldReceive('setMessages')->once()->with($expected);

        OperatingCentre::mapFormErrors($form, $errors, $mockFm, $mockTranslatorService, $location, $taGuidesUrl);
    }

    /**
     * @return ((string|string[])[][][]|string)[][]
     *
     * @psalm-return array{externalUser: list{'external', array{address: array{postcode: list{array{ERR_OC_PC_TA_GB: 'translated'}}}}}, internalUserConfirmed: list{'internal', array{'form-actions': list{array{ERR_OC_PC_TA_GB: 'translated'}}}}, internalUserNotConfirmed: list{'internal', array{'form-actions': list{array{ERR_OC_PC_TA_GB: 'translated'}}}}}
     */
    public function dpConfirmation(): array
    {

        return [

            'externalUser' => [

                OperatingCentre::LOC_EXTERNAL,
                [
                    'address' =>
                        [
                            'postcode' =>
                                [
                                    0 =>
                                        [
                                            'ERR_OC_PC_TA_GB' => 'translated',
                                        ],
                                ],
                        ],
                ]
            ],
            'internalUserConfirmed' => [
                OperatingCentre::LOC_INTERNAL,
                [
                    'form-actions' =>
                        [
                            0 =>
                                [
                                    'ERR_OC_PC_TA_GB' => 'translated'
                                ],
                        ],
                ]
            ],
            'internalUserNotConfirmed' => [
                OperatingCentre::LOC_INTERNAL,
                [
                    'form-actions' =>
                        [
                            0 =>
                                [
                                    'ERR_OC_PC_TA_GB' => 'translated'
                                ],
                        ],
                ]
            ]
        ];
    }
}
