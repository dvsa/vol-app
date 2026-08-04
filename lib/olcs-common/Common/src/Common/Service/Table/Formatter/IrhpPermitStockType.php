<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * IRHP Permit Stock table - Permit Type column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitStockType implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format
     *
     * Returns the title of the ECMT Permit
     *
     * @param array $data
     * @param array $column The column data.
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
                'stockId' => $data['id']
            ]
        );

        $canDelete = $data['canDelete'];

        return sprintf(
            sprintf('<a class=\'govuk-link\' data-stock-delete=\'%s\' href=\'%%s\'>%%s</a>', $canDelete),
            $url,
            $data['irhpPermitType']['name']['description']
        );
    }
}
