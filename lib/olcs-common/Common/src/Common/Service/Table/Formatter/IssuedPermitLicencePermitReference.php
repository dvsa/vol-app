<?php

/**
 * Issued permit licence permit reference formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;

/**
 * Issued permit licence permit reference formatter
 */
class IssuedPermitLicencePermitReference implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Issued permit licence permit reference
     *
     * @param array $row    Row data
     * @param array $column Column data
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = null)
    {
        $route = 'licence/irhp-application/irhp-permits';
        $params = [
            'licence' => $row['licenceId'],
            'irhpAppId' => $row['id'],
            'permitTypeId' => $row['typeId']
        ];

        $appLinkPermitTypeIds = [
            RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
        ];

        $permitTypeId = $row['typeId'];

        if (in_array($permitTypeId, $appLinkPermitTypeIds)) {
            $route = 'licence/irhp-application/application';
            $params = [
                'licence' => $row['licenceId'],
                'action' => 'edit',
                'irhpAppId' => $row['id']
            ];
        }

        $url = $this->urlHelper->fromRoute($route, $params);

        return '<a class="govuk-link" href="' . $url . '">' . Escape::html($row['applicationRef']) . '</a>';
    }
}
