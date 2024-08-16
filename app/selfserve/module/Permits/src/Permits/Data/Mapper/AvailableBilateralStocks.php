<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
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
class AvailableBilateralStocks implements MapperInterface
{
    public const MOROCCO_CODE = 'MA';

    /**
     * Create service instance
     *
     *
     * @return AvailableBilateralStocks
     */
    public function __construct(private readonly TranslationHelperService $translator)
    {
    }

    /**
     * Map stock options
     *
     * @param Form  $form
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

            $data['question'] = $data['browserTitle'] = 'permits.page.bilateral.which-period-required.multi-stock';

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
     * @param bool $isMorocco
     */
    private function multipleStockOptions(Form $form, array $stocks, ?string $selectedCountryStock, $isMorocco): void
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

    private function singleStockOption(Form $form, array $stock): void
    {
        $markup = sprintf(
            '<p class="govuk-body-l">%s %s</p>',
            $this->translator->translate('permits.page.bilateral.which-period-required.single-stock.text'),
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
    public static function mapFromResult($data)
    {
        return $data['fields'];
    }

    /**
     * Return the stock ID for the country referenced in the route params if one exists.
     *
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

    /**
     * @param $data
     * @return array
     */
    public function mapFromForm($data)
    {
        return $data['fields'];
    }
}
