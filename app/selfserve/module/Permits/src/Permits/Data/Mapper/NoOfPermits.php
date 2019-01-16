<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\RefData;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Zend\Form\Fieldset;
use Zend\Form\Element\Number;
use RuntimeException;

/**
 * No of permits mapper
 */
class NoOfPermits
{
    // TODO: this shouldn't be here
    const PERMIT_FEE_IN_POUNDS = 8;

    /**
     * @param array $data
     * @param       $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form, TranslationHelperService $translator)
    {
        $irhpApplication = $data[IrhpApplicationDataSource::DATA_KEY];

        $irhpPermitTypeId = $irhpApplication['irhpPermitType']['id'];
        if ($irhpPermitTypeId != RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            throw new RuntimeException('Permit type ' . $irhpPermitTypeId . ' is not supported by this mapper');
        }

        $formElements = [];

        foreach ($irhpApplication['irhpPermitApplications'] as $irhpPermitApplication) {
            $irhpPermitStock = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock'];
            $validFromTimestamp = strtotime($irhpPermitStock['validFrom']);
            $country = $irhpPermitStock['country'];

            $permitsRequired = $irhpPermitApplication['permitsRequired'];
            $validFromYear = date('Y', $validFromTimestamp);
            $countryId = $country['id'];
            $countryName = $country['countryDesc'];

            if (!isset($formElements[$countryId])) {
                $formElements[$countryId] = [
                    'name' => $countryName,
                    'id' => $countryId,
                    'years' => []
                ];
            }

            $formElements[$countryId]['years'][$validFromYear] = $permitsRequired;
        }

        usort(
            $formElements,
            function($elementA, $elementB) {
                $countryNameA = $elementA['name'];
                $countryNameB = $elementB['name'];

                if ($countryNameA == $countryNameB) {
                    return 0;
                }

                return ($countryNameA < $countryNameB) ? -1 : 1;
            }
        );

        $permitsRequiredFieldset = new Fieldset('permitsRequired');
        foreach ($formElements as $formElement) {
            ksort($formElement['years']);

            $permitsRequiredFieldset->add(
                self::createFieldset($formElement)
            );
        }

        $fieldset = new Fieldset('fields');
        $fieldset->add($permitsRequiredFieldset);
        $form->add($fieldset);

        $data['guidance'] = $translator->translateReplace(
            'permits.page.bilateral.no-of-permits.guidance',
            [
                $irhpApplication['licence']['totAuthVehicles'],
                self::PERMIT_FEE_IN_POUNDS
            ]
        );

        return $data;
    }

    /**
     * Creates and returns a Fieldset object corresponding to the provided country data
     *
     * @param array $country
     *
     * @return Fieldset
     */
    private static function createFieldset(array $country)
    {
        $countryId = $country['id'];
        $countryName = $country['name'];
        $elementName = $countryId;

        $fieldset = new Fieldset($elementName, ['label' => $countryName]);
        foreach ($country['years'] as $year => $permitsRequired) {
            $fieldset->add(
                self::createNumberElement($year, $permitsRequired)
            );
        }

        return $fieldset;
    }

    /**
     * Creates and returns a Number object corresponding to the provided year and permits required
     *
     * @param string $year
     * @param string $permitsRequired
     *
     * @return Number
     */
    private static function createNumberElement($year, $permitsRequired)
    {
        $number = new Number(
            $year,
            ['label' => 'for ' . $year]
        );

        $number->setValue($permitsRequired);
        $number->setAttributes(['min' => 0]);

        return $number;
    }
}
