<?php

/**
 * Unlicensed Operator Business Details Mapper Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Data\Mapper;

use PHPUnit_Framework_TestCase;
use Olcs\Data\Mapper\UnlicensedOperatorBusinessDetails as Sut;

/**
 * Unlicensed Operator Business Details Mapper Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedOperatorBusinessDetailsTest extends PHPUnit_Framework_TestCase
{
    public function testMapFromResult()
    {
        $input = array (
            'id' => 121,
            'version' => 1,
            'isUnlicensed' => true,
            'name' => 'Dan Ltd v3',
            'licences' => array (
                0 => array (
                    'correspondenceCd' => array (
                        'address' => array (
                            'addressLine1' => '386 Harehills Lane',
                            'addressLine2' => 'Harehills',
                            'addressLine3' => '',
                            'addressLine4' => '',
                            'adminArea' => NULL,
                            'countryCode' => array (
                                'countryDesc' => 'United Kingdom',
                                'id' => 'GB',
                                ),
                            'id' => 137,
                            'postcode' => 'LS9 6NF',
                            'town' => 'Leeds',
                            'version' => 1,
                            ),
                        'emailAddress' => 'foo@bar.com',
                        'id' => 160,
                        'version' => 2,
                        'phoneContacts' => array (
                            0 => array (
                                'id' => 38,
                                'phoneContactType' => array (
                                    'description' => 'Business',
                                    'id' => 'phone_t_tel',
                                ),
                                'phoneNumber' => '012345',
                                'version' => 1,
                            ),
                            1 => array (
                                'id' => 39,
                                'phoneContactType' => array (
                                    'description' => 'Home',
                                    'id' => 'phone_t_home',
                                ),
                                'phoneNumber' => '123456',
                                'version' => 1,
                            ),
                            2 => array (
                                'id' => 40,
                                'phoneContactType' => array (
                                    'description' => 'Mobile',
                                    'id' => 'phone_t_mobile',
                                ),
                                'phoneNumber' => '234567',
                                'version' => 1,
                            ),
                            3 => array (
                                'id' => 41,
                                'phoneContactType' => array (
                                    'description' => 'Fax',
                                    'id' => 'phone_t_fax',
                                ),
                                'phoneNumber' => '345678',
                                'version' => 1,
                            ),
                        ),
                    ),
                    'goodsOrPsv' => array (
                        'description' => 'Public Service Vehicle',
                        'id' => 'lcat_psv',
                    ),
                    'id' => 716,
                    'licNo' => NULL,
                    'licenceType' => NULL,
                    'niFlag' => 'N',
                    'status' => NULL,
                    'trafficArea' => array (
                        'id' => 'G',
                        'name' => 'Wales',
                        'version' => 1,
                    ),
                    'version' => 1,
                ),
            ),
        );

        $output = Sut::mapFromResult($input);

        $expected = array (
            'operator-details' => array (
                'id' => 121,
                'version' => 1,
                'name' => 'Dan Ltd v3',
                'operatorType' => 'lcat_psv',
                'contactDetailsId' => 160,
                'contactDetailsVersion' => 2,
                'trafficArea' => 'G',
            ),
            'correspondenceAddress' => array (
                'addressLine1' => '386 Harehills Lane',
                'addressLine2' => 'Harehills',
                'addressLine3' => '',
                'addressLine4' => '',
                'adminArea' => NULL,
                'countryCode' => array (
                    'countryDesc' => 'United Kingdom',
                    'id' => 'GB',
                ),
                'id' => 137,
                'postcode' => 'LS9 6NF',
                'town' => 'Leeds',
                'version' => 1,
            ),
            'contact' => array (
                'phone_business' => '012345',
                'phone_business_id' => 38,
                'phone_business_version' => 1,
                'phone_home' => '123456',
                'phone_home_id' => 39,
                'phone_home_version' => 1,
                'phone_mobile' => '234567',
                'phone_mobile_id' => 40,
                'phone_mobile_version' => 1,
                'phone_fax' => '345678',
                'phone_fax_id' => 41,
                'phone_fax_version' => 1,
                'email' => 'foo@bar.com',
                ),
            );

        $this->assertEquals($expected, $output);
    }

    public function testMapFromForm()
    {
        $this->markTestIncomplete('@todo');
    }

    public function testMapFromErrors()
    {
        $this->markTestIncomplete('@todo');
    }
}
