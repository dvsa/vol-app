<?php

namespace Common\Data\Mapper\Permits;

use Common\Form\Elements\Custom\NoOfPermits as NoOfPermitsElement;
use Common\Form\Elements\Types\Html;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use RuntimeException;
use Laminas\Form\Fieldset;

/**
 * No of permits mapper
 */
class NoOfPermits
{
    /** @var TranslationHelperService */
    protected $translator;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermits
     */
    public function __construct(TranslationHelperService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param $form
     * @param string $irhpApplicationDataKey
     * @param string $maxPermitsByStockDataKey
     * @param string $feePerPermitDataKey
     *
     * @throws RuntimeException
     * @return array
     */
    public function mapForFormOptions(
        array $data,
        $form,
        $irhpApplicationDataKey,
        $maxPermitsByStockDataKey,
        $feePerPermitDataKey
    ) {
        $irhpApplication = $data[$irhpApplicationDataKey];

        $irhpPermitTypeId = $irhpApplication['irhpPermitType']['id'];
        if ($irhpPermitTypeId != RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID) {
            throw new RuntimeException('Permit type ' . $irhpPermitTypeId . ' is not supported by this mapper');
        }

        $irhpPermitApplications = $irhpApplication['irhpPermitApplications'];
        $maxPermitsByStock = $data[$maxPermitsByStockDataKey]['result'];

        $permitsRequiredFieldset = new Fieldset('permitsRequired');
        $this->populatePermitsRequiredFieldset(
            $permitsRequiredFieldset,
            $irhpPermitApplications,
            $maxPermitsByStock,
            $irhpApplication['licence']['totAuthVehicles']
        );

        $fieldset = new Fieldset('fields');
        $fieldset->add($permitsRequiredFieldset);

        $form->add($fieldset);

        $data = $this->postProcessData(
            $data,
            $irhpApplicationDataKey,
            $feePerPermitDataKey,
            $maxPermitsByStockDataKey
        );

        $availableStockCount = $this->getAvailableStockCount($irhpPermitApplications, $maxPermitsByStock);

        if ($availableStockCount > 1) {
            $data['banner'] = 'permits.page.no-of-permits.banner';
        }

        if ($availableStockCount == 0) {
            return $this->applyMaxAllowableChanges($data, $form);
        }

        return $data;
    }

    /**
     * Populates a fieldset object with form elements in accordance with permit availabilty
     */
    protected function populateYearFieldset(Fieldset $fieldset, array $years): void
    {
        foreach ($years as $yearAttributes) {
            if ($yearAttributes['maxPermits'] > 0) {
                $element = $this->createNoOfPermitsElement($yearAttributes);
            } else {
                $element = $this->createHtmlElement($yearAttributes);
            }

            $fieldset->add($element);
        }
    }

    protected function populatePermitsRequiredFieldset(
        Fieldset $permitsRequiredFieldset,
        array $irhpPermitApplications,
        array $maxPermitsByStock,
        $totAuthVehicles
    ): void {
        $formElements = [];

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $irhpPermitStock = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock'];
            $validFromTimestamp = strtotime($irhpPermitStock['validFrom']);
            $stockId = $irhpPermitStock['id'];

            $maxPermits = $maxPermitsByStock[$stockId];
            $validFromYear = date('Y', $validFromTimestamp);

            $formElements[$validFromYear] = [
                'validFromYear' => $validFromYear,
                'permitsRequired' => $irhpPermitApplication['permitsRequired'],
                'maxPermits' => $maxPermits,
                'issuedPermits' => $totAuthVehicles - $maxPermits,
            ];
        }

        ksort($formElements);

        $this->populateYearFieldset($permitsRequiredFieldset, $formElements);
    }

    /**
     * Creates and returns a NoOfPermitsElement object corresponding to the provided year attributes
     *
     *
     */
    private function createNoOfPermitsElement(array $yearAttributes): NoOfPermitsElement
    {
        $validFromYear = $yearAttributes['validFromYear'];
        $maxPermits = $yearAttributes['maxPermits'];
        $issuedPermits = $yearAttributes['issuedPermits'];

        $label = $this->translator->translateReplace(
            'permits.page.multilateral.no-of-permits.for-year',
            [$validFromYear]
        );

        $element = new NoOfPermitsElement(
            $validFromYear,
            ['label' => $label]
        );

        $hint = match ($issuedPermits) {
            0 => $this->translator->translateReplace(
                'permits.page.no-of-permits.none-issued',
                [$maxPermits]
            ),
            1 => $this->translator->translateReplace(
                'permits.page.no-of-permits.one-issued',
                [$maxPermits]
            ),
            default => $this->translator->translateReplace(
                'permits.page.no-of-permits.multiple-issued',
                [$maxPermits, $issuedPermits]
            ),
        };

        $element->setOptions(
            [
                'hint' => $hint,
                'hint-class' => 'govuk-hint'
            ]
        );
        $element->setValue($yearAttributes['permitsRequired']);
        $element->setAttributes(['id' => $validFromYear, 'max' => $maxPermits]);

        return $element;
    }

    /**
     * Creates and returns a Html object corresponding to the provided year attributes
     *
     *
     */
    private function createHtmlElement(array $yearAttributes): Html
    {
        $element = new Html($yearAttributes['validFromYear']);

        $translated = $this->translator->translateReplace(
            'permits.page.multilateral.no-of-permits.all-issued',
            [
                $yearAttributes['validFromYear'],
                $yearAttributes['issuedPermits']
            ]
        );

        $element->setValue('<p class="no-more-available">' . $translated . '</p>');
        return $element;
    }

    /**
     * Apply changes to the data and form to reflect the fact that no more permits can be applied for
     *
     * @return array
     */
    protected function applyMaxAllowableChanges(array $data, mixed $form)
    {
        $data['browserTitle'] = 'permits.page.multilateral.no-of-permits.maximum-authorised.browser.title';
        $data['question'] = 'permits.page.multilateral.no-of-permits.maximum-authorised.question';

        $data['guidance'] = [
            'value' => 'permits.page.multilateral.no-of-permits.maximum-authorised.guidance',
            'disableHtmlEscape' => true
        ];

        $formFieldsets = $form->getFieldsets();

        // 'Submit' fieldset isn't present when called from internal
        if (isset($formFieldsets['Submit'])) {
            $submitFieldset = $formFieldsets['Submit'];
            $this->alterSubmitFieldsetOnMaxAllowable($submitFieldset);

            $submitFieldsetElements = $submitFieldset->getElements();
            $saveAndReturnButtonElement = $submitFieldsetElements['SaveAndReturnButton'];
            $saveAndReturnButtonElement->setName('CancelButton');
            $saveAndReturnButtonElement->setValue('permits.page.no-of-permits.button.cancel');
        }

        return $data;
    }


    /**
     * @SuppressWarnings (PHPMD.UnusedFormalParameter)
     *
     * @return ((string|true)[]|mixed|string)[]
     *
     * @psalm-return array{browserTitle: 'permits.page.multilateral.no-of-permits.browser.title', question: 'permits.page.multilateral.no-of-permits.question', guidance?: array{value: string, disableHtmlEscape: true}|mixed,...}
     */
    protected function postProcessData(
        array $data,
        $irhpApplicationDataKey,
        $feePerPermitDataKey,
        $maxPermitsByStockDataKey
    ): array {
        $data['browserTitle'] = 'permits.page.multilateral.no-of-permits.browser.title';
        $data['question'] = 'permits.page.multilateral.no-of-permits.question';

        if (isset($data[$feePerPermitDataKey])) {
            $guidanceLines = $this->generateGuidanceLines(
                $data[$feePerPermitDataKey],
                $data[$irhpApplicationDataKey]['irhpPermitApplications'],
                $data[$maxPermitsByStockDataKey]['result']
            );

            $data['guidance'] = [
                'value' => implode('<br>', $guidanceLines),
                'disableHtmlEscape' => true
            ];
        }

        return $data;
    }

    /**
     * Returns an array, each element of which represents a single line within the guidance message. Will return an
     * empty array if the guidance message is not applicable
     *
     *
     * @return array
     */
    protected function generateGuidanceLines(
        array $feesPerPermit,
        array $irhpPermitApplications,
        array $maxPermitsByStock
    ) {
        $guidanceItems = [];
        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $irhpPermitStock = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock'];
            $irhpPermitStockId = $irhpPermitStock['id'];
            $validFromTimestamp = strtotime($irhpPermitStock['validFrom']);
            $validFromYear = date('Y', $validFromTimestamp);

            if ($maxPermitsByStock[$irhpPermitStockId] > 0) {
                $guidanceItems[$validFromYear] = $feesPerPermit[$irhpPermitApplication['id']];
            }
        }

        ksort($guidanceItems);

        $guidanceLines = [];
        if ($guidanceItems !== []) {
            $guidanceLines [] = $this->translator->translate('permits.page.multilateral.no-of-permits.permit-fees');

            foreach ($guidanceItems as $validFromYear => $feePerPermit) {
                $guidanceLines[] = $this->translator->translateReplace(
                    'permits.page.multilateral.no-of-permits.fee-per-year',
                    [$feePerPermit, $validFromYear]
                );
            }
        }

        return $guidanceLines;
    }

    protected function alterSubmitFieldsetOnMaxAllowable(Fieldset $submitFieldset): void
    {
        $submitFieldset->remove('SubmitButton');
    }

    /**
     * Gets the total number of stocks available for entry (i.e. the number of visible input boxes)
     *
     *
     * @return int
     */
    protected function getAvailableStockCount(array $irhpPermitApplications, array $maxPermitsByStock)
    {
        $availableStockCount = 0;

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $stockId = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock']['id'];
            $maxPermits = $maxPermitsByStock[$stockId];

            if ($maxPermits > 0) {
                ++$availableStockCount;
            }
        }

        return $availableStockCount;
    }
}
