<?php

/**
 * ConstrainedCountriesList.php
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * Class ConstrainedCountriesList
 *
 * Takes a countries array and returns a comma separated list of country names.
 *
 * @package Common\Service\Table\Formatter
 */
class ConstrainedCountriesList implements FormatterPluginManagerInterface
{
    public function __construct(private TranslatorDelegator $translator)
    {
    }

    /**
     *
     * @param array $data   The row data.
     * @param array $column The column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $columnName = $column['name'] ?? 'constrainedCountries';

        if (empty($data[$columnName])) {
            return $this->translator->translate('no.constrained.countries');
        }

        $c = [];
        foreach ($data[$columnName] as $country) {
            $c[] = $this->translator->translate($country['countryDesc']);
        }

        return Escape::html(implode(', ', $c));
    }
}
