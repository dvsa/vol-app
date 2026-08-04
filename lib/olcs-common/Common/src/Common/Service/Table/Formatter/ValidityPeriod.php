<?php

/**
 * Validity period formatter
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use IntlDateFormatter;
use Laminas\I18n\View\Helper\DateFormat;
use Laminas\View\HelperPluginManager;

/**
 * Validity period formatter
 */
class ValidityPeriod implements FormatterPluginManagerInterface
{
    public function __construct(private HelperPluginManager $viewHelperManager, private TranslatorDelegator $translator)
    {
    }

    /**
     * @param array $row    Row data
     * @param array $column Column data
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = null)
    {
        $dateFormatter = $this->viewHelperManager->get('DateFormat');
        $locale = $this->translator->getLocale();
        $year = $row['year'];

        return sprintf(
            $this->translator->translate('permits.irhp.fee-breakdown.validity-period.cell'),
            $this->generateDateString($dateFormatter, $row['validFromTimestamp'], $locale, $year),
            $this->generateDateString($dateFormatter, $row['validToTimestamp'], $locale, $year)
        );
    }

    /**
     * @param DateFormat $dateFormatter
     * @param int        $timestamp
     * @param string     $locale
     * @param string     $year
     *
     * @return string
     */
    private function generateDateString($dateFormatter, $timestamp, $locale, $year)
    {
        $dateString = $dateFormatter(
            date($timestamp),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            $locale
        );

        return trim(str_replace($year, '', $dateString));
    }
}
