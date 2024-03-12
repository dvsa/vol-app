<?php

/**
 * Unlicensed Operator Business Details Mapper Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Data\Mapper\UnlicensedOperatorBusinessDetails as Sut;

/**
 * Unlicensed Operator Business Details Mapper Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedOperatorBusinessDetailsTest extends TestCase
{
    public function testMapFromResult()
    {
        $input =  [
            'id' => 121,
            'version' => 1,
            'isUnlicensed' => true,
            'name' => 'Dan Ltd v3',
            'licences' =>  [
                0 =>  [
                    'correspondenceCd' =>  [
                        'address' =>  [
                            'addressLine1' => '386 Harehills Lane',
                            'addressLine2' => 'Harehills',
                            'addressLine3' => '',
                            'addressLine4' => '',
                            'adminArea' => null,
                            'countryCode' =>  [
                                'countryDesc' => 'United Kingdom',
                                'id' => 'GB',
                                ],
                            'id' => 137,
                            'postcode' => 'LS9 6NF',
                            'town' => 'Leeds',
                            'version' => 1,
                            ],
                        'emailAddress' => 'foo@bar.com',
                        'id' => 160,
                        'version' => 2,
                        'phoneContacts' =>  [
                            0 =>  [
                                'id' => 38,
                                'phoneContactType' =>  [
                                    'description' => 'Business',
                                    'id' => 'phone_t_primary',
                                ],
                                'phoneNumber' => '11111',
                                'version' => 1,
                            ],
                            1 =>  [
                                'id' => 39,
                                'phoneContactType' =>  [
                                    'description' => 'Home',
                                    'id' => 'phone_t_secondary',
                                ],
                                'phoneNumber' => '22222',
                                'version' => 2,
                            ],
                        ],
                    ],
                    'goodsOrPsv' =>  [
                        'description' => 'Public Service Vehicle',
                        'id' => 'lcat_psv',
                    ],
                    'id' => 716,
                    'licNo' => null,
                    'licenceType' => null,
                    'niFlag' => 'N',
                    'status' => null,
                    'trafficArea' =>  [
                        'id' => 'G',
                        'name' => 'Wales',
                        'version' => 1,
                    ],
                    'version' => 1,
                ],
            ],
        ];

        $output = Sut::mapFromResult($input);

        $expected =  [
            'operator-details' =>  [
                'id' => 121,
                'version' => 1,
                'name' => 'Dan Ltd v3',
                'operatorType' => 'lcat_psv',
                'contactDetailsId' => 160,
                'contactDetailsVersion' => 2,
                'trafficArea' => 'G',
            ],
            'correspondenceAddress' =>  [
                'addressLine1' => '386 Harehills Lane',
                'addressLine2' => 'Harehills',
                'addressLine3' => '',
                'addressLine4' => '',
                'adminArea' => null,
                'countryCode' =>  [
                    'countryDesc' => 'United Kingdom',
                    'id' => 'GB',
                ],
                'id' => 137,
                'postcode' => 'LS9 6NF',
                'town' => 'Leeds',
                'version' => 1,
            ],
            'contact' =>  [
                'phone_primary' => '11111',
                'phone_primary_id' => 38,
                'phone_primary_version' => 1,
                'phone_secondary' => '22222',
                'phone_secondary_id' => 39,
                'phone_secondary_version' => 2,
                'email' => 'foo@bar.com',
                ],
            ];

        $this->assertEquals($expected, $output);
    }

    public function testMapFromForm()
    {
        $input =  [
            'operator-details' =>  [
                'name' => 'Dan Ltd v3',
                'id' => '121',
                'version' => '1',
                'operatorType' => 'lcat_psv',
                'trafficArea' => 'G',
                'contactDetailsId' => '160',
                'contactDetailsVersion' => '2',
            ],
            'correspondenceAddress' =>  [
                'addressLine1' => '15 Allerton Grange Vale',
                'addressLine2' => '',
                'addressLine3' => '',
                'addressLine4' => '',
                'town' => 'Leeds',
                'postcode' => 'LS17 6LS',
                'id' => '137',
                'version' => '1',
            ],
            'contact' =>  [
                'phone_primary' => '012345',
                'phone_primary_id' => '38',
                'phone_primary_version' => '1',
                'phone_secondary' => '123456',
                'phone_secondary_id' => '39',
                'phone_secondary_version' => '2',
                'email' => 'foo@bar.com',
            ],
            'isExempt' => false
        ];

        $output = Sut::mapFromForm($input);

        $expected =  [
            'name' => 'Dan Ltd v3',
            'operatorType' => 'lcat_psv',
            'id' => '121',
            'version' => '1',
            'trafficArea' => 'G',
            'contactDetails' =>  [
                'id' => '160',
                'version' => '2',
                'address' =>  [
                    'addressLine1' => '15 Allerton Grange Vale',
                    'addressLine2' => '',
                    'addressLine3' => '',
                    'addressLine4' => '',
                    'town' => 'Leeds',
                    'postcode' => 'LS17 6LS',
                    'id' => '137',
                    'version' => '1',
                ],
                'phoneContacts' => [
                    [
                        'id' => '38',
                        'version' => '1',
                        'phoneNumber' => '012345',
                        'phoneContactType' => 'phone_t_primary',
                    ],
                    [
                        'id' => '39',
                        'version' => '2',
                        'phoneNumber' => '123456',
                        'phoneContactType' => 'phone_t_secondary',
                    ],
                ],
                'emailAddress' => 'foo@bar.com',
            ],
            'isExempt' => false
        ];

        $this->assertEquals($expected, $output);
    }

    public function testMapFromErrors()
    {
        $errors = [
            'name' => ['name error'],
            'operatorType' => ['operatorType error'],
            'trafficArea' => ['trafficArea error'],
            'contactDetails' => [
                'address' => [
                    'addressLine1' => ['addressLine1 error'],
                    'addressLine2' => ['addressLine2 error'],
                    'addressLine3' => ['addressLine3 error'],
                    'addressLine4' => ['addressLine4 error'],
                    'town' => ['town error'],
                    'postcode' => ['postcode error'],
                ],
                'emailAddress' => ['email error'],
                'businessPhoneContact' => ['business error'],
                'faxPhoneContact' => ['fax error'],
                'homePhoneContact' => ['ET error'],
                'mobilePhoneContact' => ['mobile error'],
            ],
        ];

        // @note the method returns the input, not the formErrors, so we
        // get a reference via andReturnUsing
        $formErrors = null;
        $form = m::mock(\Laminas\Form\FormInterface::class);
        $form
            ->shouldReceive('setMessages')
            ->andReturnUsing(
                function ($messages) use (&$formErrors) {
                    $formErrors = $messages;
                }
            );

        Sut::mapFromErrors($form, $errors);

        $expected = [
            'operator-details' => [
                'name' => ['name error'],
                'operatorType' => ['operatorType error'],
                'trafficArea' => ['trafficArea error'],
            ],
            'correspondenceAddress' => [
                'addressLine1' => ['addressLine1 error'],
                'addressLine2' => ['addressLine2 error'],
                'addressLine3' => ['addressLine3 error'],
                'addressLine4' => ['addressLine4 error'],
                'town' => ['town error'],
                'postcode' => ['postcode error'],
            ],
            'contact' => [
                'email' => ['email error'],
                'phone_home' => ['ET error'],
                'phone_fax' => ['fax error'],
                'phone_mobile' => ['mobile error'],
                'phone_business' => ['business error'],
            ],
        ];

        $this->assertEquals($expected, $formErrors);
    }
}
