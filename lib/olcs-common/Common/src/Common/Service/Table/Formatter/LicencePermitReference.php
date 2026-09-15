<?php

/**
 * Status formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * Status formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class LicencePermitReference implements FormatterPluginManagerInterface
{
    private static array $routes = [
        RefData::ECMT_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION => 'application/under-consideration',
            RefData::PERMIT_APP_STATUS_AWAITING_FEE => 'application/awaiting-fee',
            RefData::PERMIT_APP_STATUS_FEE_PAID => null,
            RefData::PERMIT_APP_STATUS_ISSUING => null,
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION => 'application/under-consideration',
            RefData::PERMIT_APP_STATUS_AWAITING_FEE => 'application/awaiting-fee',
            RefData::PERMIT_APP_STATUS_FEE_PAID => null,
            RefData::PERMIT_APP_STATUS_ISSUING => null,
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::ECMT_REMOVAL_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::IRHP_BILATERAL_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
        ],
        RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
        ],
    ];

    public function __construct(private TranslatorDelegator $translator, private UrlHelperService $urlHelper)
    {
    }

    /**
     * status
     *
     * @param array $row    Row data
     * @param array $column Column data
     *
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = null)
    {
        $referenceNumberMarkup = sprintf(
            '<span class="govuk-visually-hidden">%s</span>',
            Escape::html(
                $this->translator->translate('dashboard-table-permit-application-ref')
            )
        );

        // find a route for the type and status
        $route = static::$routes[$row['typeId']][$row['statusId']] ?? null;

        $text = $row['applicationRef'];

        if (!isset($route)) {
            if (
                $row['statusId'] == RefData::PERMIT_APP_STATUS_VALID
                && in_array(
                    $row['typeId'],
                    [
                        RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                        RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    ]
                )
            ) {
                // Certificate of Roadworthiness doesn't have valid page itself
                // but it is still grouped by licence number
                $text = $row['licNo'];
            }

            return $referenceNumberMarkup . ' ' . Escape::html($text);
        }

        // default to application
        $params = [
            'id' => $row['id'],
        ];

        if ($route === 'valid') {
            // specific for valid IRHP application
            $params = [
            'licence' => $row['licenceId'],
            'type' => $row['typeId'],
            ];
            $text = $row['licNo'];
        }

        return sprintf(
            '%s <a class="overview__link" href="%s"><span class="overview__link--underline">%s</span></a>',
            $referenceNumberMarkup,
            $this->urlHelper->fromRoute('permits/' . $route, $params),
            Escape::html($text)
        );
    }
}
