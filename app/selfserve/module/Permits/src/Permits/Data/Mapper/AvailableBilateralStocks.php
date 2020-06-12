<?php

namespace Permits\Data\Mapper;

use Common\Form\Element\DynamicRadio;
use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Zend\Form\Element\Hidden;

/**
 * Available stocks mapper
 */
class AvailableBilateralStocks
{
    /** @var TranslationHelperService */
    private $translator;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     *
     * @return AvailableBilateralStocks
     */
    public function __construct(TranslationHelperService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Map stock options
     *
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        $stocks = $data['stocks'];

        if (count($stocks) == 1) {
            $this->singleStockOption($form, $stocks[0]);
        } else {
            $selectedCountryStock = $this->getStockIdForCurrentCountrySelection(
                $data['application']['irhpPermitApplications'],
                $data['routeParams']['country']
            );

            $data['application']['selectedStockId'] = $selectedCountryStock;

            $this->multipleStockOptions($form, $stocks, $selectedCountryStock);
        }

        foreach ($data['application']['countrys'] as $country) {
            if ($country['id'] == $data['routeParams']['country']) {
                $data['application']['countryName'] = $country['countryDesc'];
            }
        }

        return $data;
    }

    /**
     * @param Form $form
     * @param array $stocks
     * @param string|null $selectedCountryStock
     */
    private function multipleStockOptions(Form $form, array $stocks, ?string $selectedCountryStock)
    {
        $valueOptions = [];

        foreach ($stocks as $stock) {
            $valueOptions[] = [
                'value' => $stock['id'],
                'label' => $stock['periodNameKey'],
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
            ];
        }

        $irhpPermitStock = $form->get('fields')->get('irhpPermitStock');
        $irhpPermitStock->setValueOptions($this->transformValueOptions($valueOptions));
        $irhpPermitStock->setValue($selectedCountryStock);
    }

    /**
     * @param Form $form
     * @param array $stock
     */
    private function singleStockOption(Form $form, array $stock)
    {
        // Add label for single stock translation key
        $form->get('fields')->add(
            [
                'name' => 'irhpPermitStockLabel',
                'type' => Html::class,
                'attributes' => [
                    'value' => $this->translator->translate($stock['periodNameKey']),
                ]
            ]
        );

        // add hidden field with stock ID
        $form->get('fields')->add(
            [
                'name' => 'irhpPermitStock',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => $stock['id'],
                ]
            ]
        );
    }

    /**
     * Set the id of the first radio button in the list for validation accessibility purposes
     *
     * @param array $valueOptions
     *
     * @return array
     */
    private function transformValueOptions(array $valueOptions)
    {
        if (count($valueOptions)) {
            $valueOptions[0]['attributes'] = [
                'id' => 'stock'
            ];
        }

        return $valueOptions;
    }

    /**
     * @param array $response
     * @param array $routeParams
     * @param array $formData
     * @return array
     */
    public function processRedirectParams(array $response, array $routeParams, array $formData)
    {
        return [
            'id' => $routeParams['id'],
            'irhpPermitApplication' => $response['id']['irhpPermitApplication'],
            'slug' => RefData::BILATERAL_PERMIT_USAGE
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public function mapFromForm($data)
    {
        return $data['fields'];
    }

    /**
     * Return the stock ID for the country referenced in the route params if one exists.
     *
     * @param array $irhpPermitApplications
     * @param string $country
     * @return mixed|null
     */
    private function getStockIdForCurrentCountrySelection(array $irhpPermitApplications, string $country)
    {
        foreach ($irhpPermitApplications as $ipa) {
            if ($ipa['irhpPermitWindow']['irhpPermitStock']['country']['id'] == $country) {
                return $ipa['irhpPermitWindow']['irhpPermitStock']['id'];
            }
        }
        return null;
    }
}
