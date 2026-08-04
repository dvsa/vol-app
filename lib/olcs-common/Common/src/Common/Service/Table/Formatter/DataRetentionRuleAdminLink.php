<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * Data Retention Rule admin link formatter
 */
class DataRetentionRuleAdminLink implements FormatterPluginManagerInterface
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
            'admin-dashboard/admin-data-retention/rule-admin',
            ['action' => 'edit', 'id' => $data['id']]
        );

        return '<a href="' . htmlspecialchars($url) . '" class="govuk-link js-modal-ajax">' . htmlspecialchars(ucwords($data['description'])) . '</a>';
    }
}
