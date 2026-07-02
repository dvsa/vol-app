<?php

/**
 * PI Report Name formatter
 */

namespace Common\Service\Table\Formatter;

/**
 * PI Report Name formatter
 */
class PiReportName implements FormatterPluginManagerInterface
{
    public function __construct(private OrganisationLink $organisationLinkformatter, private Name $nameFormatter)
    {
    }

    /**
     * Format a PI Name Record
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!empty($data['pi']['case']['licence']['organisation'])) {
            // display org linked to the licence
            return $this->organisationLinkformatter->format($data['pi']['case']['licence'], $column);
        }
        if (!empty($data['pi']['case']['transportManager']['homeCd']['person'])) {
            // display TM details
            return $this->nameFormatter->format($data['pi']['case']['transportManager']['homeCd']['person']);
        }

        return '';
    }
}
