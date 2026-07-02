<?php

/**
 * System parameter link formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * System parameter link formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SystemParameterLink implements FormatterPluginManagerInterface
{
    public function __construct(protected UrlHelperService $urlHelper)
    {
    }

    /**
     * Format
     *
     * @param      array $data
     * @param      array $column
     * @inheritdoc
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $url = $this->urlHelper->fromRoute(
            'admin-dashboard/admin-system-parameters',
            ['action' => 'edit', 'sp' => $data['id']]
        );

        return '<a href="' . $url . '" class="govuk-link js-modal-ajax">' . $data['id'] . '</a>';
    }
}
