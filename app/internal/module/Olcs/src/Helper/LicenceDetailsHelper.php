<?php

/**
 * Licence Details Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Helper;

/**
 * Licence Details Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceDetailsHelper
{
    const GOODS_OR_PSV_GOODS = 'lcat_gv';
    const GOODS_OR_PSV_PSV = 'lcat_psv';

    const LICENCE_TYPE_RESTRICTED = 'ltyp_r';
    const LICENCE_TYPE_STANDARD_INTERNATIONAL = 'ltyp_si';
    const LICENCE_TYPE_STANDARD_NATIONAL = 'ltyp_sn';
    const LICENCE_TYPE_SPECIAL_RESTRICTED = 'ltyp_sr';

    /**
     * Holds the section config
     *
     * @var array
     */
    protected $sections = array(
        'overview' => array(

        ),
        'type_of_licence' => array(

        ),
        'business_details' => array(

        ),
        'address' => array(

        ),
        'people' => array(

        ),
        'operating_centre' => array(
            'restricted' => array(
                // @NOTE The licence must follow ANY of these restrictions
                self::LICENCE_TYPE_RESTRICTED,
                self::LICENCE_TYPE_STANDARD_NATIONAL,
                self::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ),
        'transport_manager' => array(
            'restricted' => array(
                self::LICENCE_TYPE_STANDARD_NATIONAL,
                self::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ),
        'vehicle' => array(
            'restricted' => array(
                self::LICENCE_TYPE_RESTRICTED,
                self::LICENCE_TYPE_STANDARD_NATIONAL,
                self::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ),
        'safety' => array(
            'restricted' => array(
                self::LICENCE_TYPE_RESTRICTED,
                self::LICENCE_TYPE_STANDARD_NATIONAL,
                self::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ),
        'condition_undertaking' => array(
            'restricted' => array(
                self::LICENCE_TYPE_RESTRICTED,
                self::LICENCE_TYPE_STANDARD_NATIONAL,
                self::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        ),
        'taxi_phv' => array(
            'restricted' => array(
                // @NOTE The licence must follow ALL of these restrictions
                array(
                    self::GOODS_OR_PSV_PSV,
                    self::LICENCE_TYPE_SPECIAL_RESTRICTED
                )
            )
        )
    );

    /**
     * Get a list of accessible sections
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return array
     */
    public function getAccessibleSections($goodsOrPsv, $licenceType)
    {
        $sections = $this->sections;

        foreach (array_keys($sections) as $section) {
            if (!$this->doesLicenceHaveAccess($section, $goodsOrPsv, $licenceType)) {
                unset($sections[$section]);
            }
        }

        return $sections;
    }

    /**
     * Check if the licence has access to the section
     *
     * @param string $section
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return boolean
     */
    public function doesLicenceHaveAccess($section, $goodsOrPsv, $licenceType)
    {
        $sectionDetails = $this->sections[$section];

        // If the section has no restrictions just return
        if (!isset($sectionDetails['restricted'])) {
            return true;
        }

        $access = array($goodsOrPsv, $licenceType);

        // Iterate through the restrictions
        foreach ($sectionDetails['restricted'] as $restriction) {
            // If we have an sub array of restrictions, then we must match ALL
            if ((is_array($restriction) && count(array_diff($restriction, $access)) === 0)
                // If not we just have to match 1 restriction
                || in_array($restriction, $access)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get navigation config
     *
     * @param int $licenceId
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return array
     */
    public function getNavigation($licenceId, $goodsOrPsv, $licenceType, $activeSection = null)
    {
        $sections = $this->getAccessibleSections($goodsOrPsv, $licenceType);

        $navigation = array();

        foreach (array_keys($sections) as $section) {
            $navigation[] = array(
                'label' => 'internal-licence-details-' . $section . '-label',
                'title' => 'internal-licence-details-' . $section . '-title',
                'route' => 'licence/details/' . $section,
                'use_route_match' => true,
                'params' => array(
                    'licence' => $licenceId
                ),
                'active' => $section == $activeSection
            );
        }

        return $navigation;
    }
}
