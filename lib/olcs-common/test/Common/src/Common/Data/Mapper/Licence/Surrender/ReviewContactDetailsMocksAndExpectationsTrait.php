<?php

namespace CommonTest\Common\Data\Mapper\Licence\Surrender;

trait ReviewContactDetailsMocksAndExpectationsTrait
{
    protected function mockLicence(): array
    {
        return [
            'licNo' => 'AB12345',
            'organisation' => [
                'name' => 'Organisation name',
                'tradingNames' => [
                    [
                        'name' => 'test trading name'
                    ]
                ]
            ],
            'correspondenceCd' => [
                'address' => [
                    'addressLine1' => 'Address line 1',
                    'addressLine2' => 'Address line 2',
                    'addressLine3' => 'Address line 3',
                    'addressLine4' => 'Address line 4',
                    'town' => 'Test Town',
                    'countryCode' => [
                        'countryDesc' => 'Test Country'
                    ],
                ],
                'emailAddress' => 'test@email.com',
                'phoneContacts' => [
                    [
                        'phoneContactType' => ['id' => 'phone_t_primary'],
                        'phoneNumber' => '12345678'
                    ],
                    [
                        'phoneContactType' => ['id' => 'phone_t_secondary'],
                        'phoneNumber' => '87654321'
                    ]
                ]
            ]
        ];
    }

    protected function expectedForContactDetails(): array
    {
        $mockLicence = $this->mockLicence();
        $changeLinkInHeading = true;
        return [
            'sectionHeading' => 'contact details',
            'changeLinkInHeading' => $changeLinkInHeading,
            'change' => [
                'sectionLink' => 'licence/surrender/address-details#contactDetails'
            ],
            'questions' => [
                [
                    'label' => 'contact number',
                    'answer' => $mockLicence['correspondenceCd']['phoneContacts'][0]['phoneNumber'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ],
                [
                    'label' => 'secondary contact number',
                    'answer' => $mockLicence['correspondenceCd']['phoneContacts'][1]['phoneNumber'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ],
                [
                    'label' => 'Email',
                    'answer' => $mockLicence['correspondenceCd']['emailAddress'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ]
            ],
        ];
    }

    protected function expectedForLicenceDetails(): array
    {
        $mockLicence = $this->mockLicence();
        $changeLinkInHeading = true;
        return [
            'sectionHeading' => 'licence details',
            'changeLinkInHeading' => $changeLinkInHeading,
            'change' => false,
            'questions' => [
                [
                    'label' => 'licence number',
                    'answer' => $mockLicence['licNo'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ],
                [
                    'label' => 'name of licence holder',
                    'answer' => $mockLicence['organisation']['name'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ],
                [
                    'label' => 'trading name',
                    'answer' => $mockLicence['organisation']['tradingNames'][0]['name'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ]
            ],
        ];
    }

    protected function expectedForCorrespondenceAddress(): array
    {
        $mockLicence = $this->mockLicence();
        $changeLinkInHeading = true;

        $licenceAddress = $mockLicence['correspondenceCd']['address']['addressLine1']
            . '<br>' . $mockLicence['correspondenceCd']['address']['addressLine2']
            . '<br>' . $mockLicence['correspondenceCd']['address']['addressLine3']
            . '<br>' . $mockLicence['correspondenceCd']['address']['addressLine4'];
        return [
            'sectionHeading' => 'correspondence address',
            'changeLinkInHeading' => $changeLinkInHeading,
            'change' => [
                'sectionLink' => 'licence/surrender/address-details#correspondenceAddress'
            ],
            'questions' => [
                [
                    'label' => 'address',
                    'answer' => $licenceAddress,
                    'changeLinkInHeading' => $changeLinkInHeading
                ],
                [
                    'label' => 'town/city',
                    'answer' => $mockLicence['correspondenceCd']['address']['town'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ],
                [
                    'label' => 'country',
                    'answer' => $mockLicence['correspondenceCd']['address']['countryCode']['countryDesc'],
                    'changeLinkInHeading' => $changeLinkInHeading
                ]
            ],
        ];
    }

    protected function mockTranslatorForContactDetails($mockTranslator): void
    {
        $mockTranslator
            ->shouldReceive('translate')
            ->with('contact-details')
            ->andReturn('contact details');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('contact-number')
            ->andReturn('contact number');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('secondary-contact-number')
            ->andReturn('secondary contact number');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('Email')
            ->andReturn('Email');
    }

    protected function mockUrlHelperFromRoute($mockUrlHelper, $route, $times): void
    {
        $mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with($route, [], [], true)
            ->times($times)
            ->andReturn($route);
    }

    protected function mockTranslatorForLicenceDetails($mockTranslator): void
    {
        $mockTranslator
            ->shouldReceive('translate')
            ->with('licence-details')
            ->andReturn('licence details');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('licence-number-full')
            ->andReturn('licence number');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('name-of-licence-holder')
            ->andReturn('name of licence holder');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('trading-name')
            ->andReturn('trading name');
    }

    protected function mockTranslatorForCorrespondenceAddress($mockTranslator): void
    {
        $mockTranslator
            ->shouldReceive('translate')
            ->with('correspondence-address')
            ->andReturn('correspondence address');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('address')
            ->andReturn('address');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('address_townCity')
            ->andReturn('town/city');
        $mockTranslator
            ->shouldReceive('translate')
            ->with('address_country')
            ->andReturn('country');
    }
}
