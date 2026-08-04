<?php

/**
 * Class SeriousInfringementLink
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * Class SeriousInfringementLink
 *
 * @package Common\Service\Table\Formatter
 * @author  Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SeriousInfringementLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Return a the serious infringement URL for a table.
     *
     * @param  array $data   The row data.
     * @param  array $column The column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        unset($column);
        $url = $this->urlHelper->fromRoute('case_penalty_applied', ['si' => $data['id'], 'action' => 'index'], [], true);

        return '<a class="govuk-link" href="' . $url . '">' . $data['id'] . '</a>';
    }
}
