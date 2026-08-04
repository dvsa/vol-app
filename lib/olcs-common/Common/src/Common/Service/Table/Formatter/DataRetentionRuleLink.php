<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * Data Retention Rule link formatter
 */
class DataRetentionRuleLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format
     *
     * @param array $data   Data of current row
     * @param array $column Column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $url = $this->urlHelper->fromRoute(
            'admin-dashboard/admin-data-retention/review/records',
            ['dataRetentionRuleId' => $data['id']]
        );

        return '<a class="govuk-link" href="' . $url . '" target="_self">' .
            ucwords($data['description']) .
            '</a>';
    }
}
