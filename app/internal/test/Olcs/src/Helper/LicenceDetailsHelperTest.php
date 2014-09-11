<?php

/**
 * Licence Details Helper Tests
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Helper;

use PHPUnit_Framework_TestCase;
use Olcs\Helper\LicenceDetailsHelper;

/**
 * Licence Details Helper Tests
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceDetailsHelperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->helper = new LicenceDetailsHelper();
    }

    /**
     * Test the getAccessibleSections returns the correct sections for the licence
     *
     * @dataProvider getAccessibleSectionsProvider
     */
    public function testGetAccessibleSections($goodsOrPsv, $licenceType, $expected)
    {
        $actual = $this->helper->getAccessibleSections($goodsOrPsv, $licenceType);

        $this->assertEquals($expected, array_keys($actual));
    }

    public function getAccessibleSectionsProvider()
    {
        return array(
            array(
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'operating_centre',
                    'vehicle',
                    'safety',
                    'condition_undertaking'
                )
            ),
            array(
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'operating_centre',
                    'transport_manager',
                    'vehicle',
                    'safety',
                    'condition_undertaking'
                )
            ),
            array(
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'operating_centre',
                    'transport_manager',
                    'vehicle',
                    'safety',
                    'condition_undertaking'
                )
            ),
            array(
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'operating_centre',
                    'vehicle_psv',
                    'safety',
                    'condition_undertaking'
                )
            ),
            array(
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'operating_centre',
                    'transport_manager',
                    'vehicle_psv',
                    'safety',
                    'condition_undertaking'
                )
            ),
            array(
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'operating_centre',
                    'transport_manager',
                    'vehicle_psv',
                    'safety',
                    'condition_undertaking'
                )
            ),
            array(
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_SPECIAL_RESTRICTED,
                array(
                    'overview',
                    'type_of_licence',
                    'business_details',
                    'address',
                    'people',
                    'taxi_phv'
                )
            )
        );
    }

    /**
     * Test the doesLicenceHaveAccess returns the correct access rights
     *
     * @dataProvider doesLicenceHaveAccessProvider
     */
    public function testDoesLicenceHaveAccess($section, $goodsOrPsv, $licenceType, $expected)
    {
        $actual = $this->helper->doesLicenceHaveAccess($section, $goodsOrPsv, $licenceType);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @NOTE Here I just test a selected group of scenarios, the above test will cover all scenarios anyway
     *
     * @return array
     */
    public function doesLicenceHaveAccessProvider()
    {
        return array(
            // Test that every licence can see overview
            array(
                'overview',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                true
            ),
            array(
                'overview',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                true
            ),
            array(
                'overview',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                true
            ),
            array(
                'overview',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                true
            ),
            array(
                'overview',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                true
            ),
            array(
                'overview',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                true
            ),
            array(
                'overview',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_SPECIAL_RESTRICTED,
                true
            ),
            // Test which licence has taxi phv
            array(
                'taxi_phv',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                false
            ),
            array(
                'taxi_phv',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                false
            ),
            array(
                'taxi_phv',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                false
            ),
            array(
                'taxi_phv',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                false
            ),
            array(
                'taxi_phv',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                false
            ),
            array(
                'taxi_phv',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                false
            ),
            array(
                'taxi_phv',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_SPECIAL_RESTRICTED,
                true
            ),
            // Test who can transport manager
            array(
                'transport_manager',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                false
            ),
            array(
                'transport_manager',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                true
            ),
            array(
                'transport_manager',
                LicenceDetailsHelper::GOODS_OR_PSV_GOODS,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                true
            ),
            array(
                'transport_manager',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_RESTRICTED,
                false
            ),
            array(
                'transport_manager',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                true
            ),
            array(
                'transport_manager',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL,
                true
            ),
            array(
                'transport_manager',
                LicenceDetailsHelper::LICENCE_CATEGORY_PSV,
                LicenceDetailsHelper::LICENCE_TYPE_SPECIAL_RESTRICTED,
                false
            )
        );
    }

    /**
     * Test get navigation
     */
    public function testGetNavigation()
    {
        $licenceId = 1;
        $goodsOrPsv = LicenceDetailsHelper::GOODS_OR_PSV_GOODS;
        $licenceType = LicenceDetailsHelper::LICENCE_TYPE_STANDARD_NATIONAL;
        $activeSection = 'overview';

        $nav = $this->helper->getNavigation($licenceId, $goodsOrPsv, $licenceType, $activeSection);

        // We should have 10 sections
        $this->assertEquals(10, count($nav));

        // First sectiion (overview) should be active
        $this->assertTrue($nav[0]['active']);
        $this->assertFalse($nav[1]['active']);

        // Assert that the licence route param is set
        $this->assertEquals($licenceId, $nav[0]['params']['licence']);
    }
}
