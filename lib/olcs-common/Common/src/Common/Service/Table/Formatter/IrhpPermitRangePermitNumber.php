<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;

/**
 * IRHP Permit Range table - Permit Numbers column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitRangePermitNumber implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format
     *
     * Returns a formatted column for the Permit Numbers
     *
     * @param array          $data
     * @param array          $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        unset($column);

        $url = $this->urlHelper->fromRoute(
            'admin-dashboard/admin-permits/ranges',
            [
                'stockId' => $data['irhpPermitStock']['id'],
                'action' => 'edit',
                'id' => $data['id']
            ]
        );

        $permitNumber = sprintf(
            "%s%s to %s%s",
            Escape::html($data['prefix']),
            Escape::html($data['fromNo']),
            Escape::html($data['prefix']),
            Escape::html($data['toNo'])
        );

        return sprintf(
            "<a class='govuk-link js-modal-ajax' href='%s'>%s</a>",
            $url,
            $permitNumber
        );
    }
}
