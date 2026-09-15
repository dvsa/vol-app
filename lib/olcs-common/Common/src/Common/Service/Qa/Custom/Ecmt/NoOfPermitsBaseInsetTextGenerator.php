<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;

class NoOfPermitsBaseInsetTextGenerator
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsBaseInsetTextGenerator
     */
    public function __construct(private TranslationHelperService $translator, private CurrencyFormatter $currencyFormatter)
    {
    }

    /**
     * Generate the base inset text wrapped in the specified format for use on the number of permits page, or return an
     * empty string if the issue fee is 'N/A'
     *
     * @param string $format
     * @return string
     */
    public function generate(array $options, $format)
    {
        if ($options['issueFee'] == 'N/A') {
            return '';
        }

        $insetText = sprintf(
            $this->translator->translate('qanda.ecmt.number-of-permits.inset.base'),
            $this->currencyFormatter->__invoke($options['applicationFee']),
            $this->currencyFormatter->__invoke($options['issueFee'])
        );

        return sprintf($format, $insetText);
    }
}
