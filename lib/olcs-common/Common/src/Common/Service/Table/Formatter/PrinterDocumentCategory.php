<?php

/**
 * Printer Document Category formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * Printer Document Category formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterDocumentCategory implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = [])
    {
        $url = $this->urlHelper->fromRoute(
            'admin-dashboard/admin-team-management',
            ['rule' => $row['id'], 'action' => 'editRule', 'team' => $row['team']['id']]
        );

        $categories = isset($row['subCategory']) ?
            $row['subCategory']['category']['description'] . ' / ' . $row['subCategory']['subCategoryName'] :
            'Default setting';

        return '<a href="' . $url . '" class="govuk-link js-modal-ajax">' . $categories . '</a>';
    }
}
