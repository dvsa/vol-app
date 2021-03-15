<?php

namespace Permits\Data\Mapper;

use Common\Form\Element\DynamicRadio;
use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\Form\Input\StockInputMorocco;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\Element\Hidden;

/**
 * Available stocks mapper
 */
class AvailableBilateralStocks
{
    const MOROCCO_CODE = 'MA';

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
            $countryId = $data['routeParams']['country'];

            $selectedCountryStock = $this->getStockIdForCurrentCountrySelection(
                $data['application']['irhpPermitApplications'],
                $countryId
            );

            $data['application']['selectedStockId'] = $selectedCountryStock;

            $isMorocco = $countryId == self::MOROCCO_CODE;

            if ($isMorocco) {
                $data['question'] = 'permits.page.bilateral.which-period-required.morocco';
                $data['browserTitle'] = 'permits.page.bilateral.which-period-required.morocco';
                $form->get('fields')->get('irhpPermitStock')->setOption('input_class', StockInputMorocco::class);
            }

            $form->get('Submit')->get('SubmitButton')->setValue('Save and continue');

            $this->multipleStockOptions($form, $stocks, $selectedCountryStock, $isMorocco);
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
     * @param bool $isMorocco
     */
    private function multipleStockOptions(Form $form, array $stocks, ?string $selectedCountryStock, $isMorocco)
    {
        $valueOptions = [];

        foreach ($stocks as $stock) {
            $periodNameKey = $stock['periodNameKey'];

            $valueOption = [
                'value' => $stock['id'],
                'label' => $periodNameKey,
            ];

            if ($isMorocco) {
                $valueOption['hint'] = str_replace('label', 'hint', $periodNameKey);
            }

            $valueOptions[] = $valueOption;
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
        $markup = sprintf(
            '<p class="govuk-body-l">%s</p>',
            $this->translator->translate($stock['periodNameKey'])
        );

        $fields = $form->get('fields');

        // Add label for single stock translation key
        $fields->add(
            [
                'name' => 'irhpPermitStockLabel',
                'type' => Html::class,
                'attributes' => [
                    'value' => $markup,
                ]
            ]
        );

        // add hidden field with stock ID
        $fields->add(
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
     * @param array $data
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processRedirectParams(array $response, array $routeParams, array $formData, array $data)
    {
        $stocks = $data['stocks'];
        $stockToSlugLookup = [];

        foreach ($stocks as $stock) {
            $stockToSlugLookup[$stock['id']] = $stock['first_step_slug'];
        }

        $selectedStockId = $formData['fields']['irhpPermitStock'];
        $slug = $stockToSlugLookup[$selectedStockId];

        return [
            'id' => $routeParams['id'],
            'irhpPermitApplication' => $response['id']['irhpPermitApplication'],
            'slug' => $slug
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
