<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use LmcRbacMvc\Service\AuthorizationService;

/**
 *
 * @package Common\Service\Table\Formatter
 *
*
 */
class SearchCasesName implements FormatterPluginManagerInterface
{
    public function __construct(private AuthorizationService $authService, private UrlHelperService $urlHelper)
    {
    }

    /**
     *
     * @param array $data The row data.
     * @param array $column The column data.
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!empty($data['tmId'])) {
            $url = $this->urlHelper->fromRoute(
                'transport-manager/details',
                ['transportManager' => $data['tmId']]
            );
            $link = $data['tmForename'] . ' ' . $data['tmFamilyName'];
        } else {
            $url = $this->urlHelper->fromRoute(
                'operator/business-details',
                ['organisation' => $data['orgId']]
            );
            $link = $data['orgName'];
        }

        if ($this->authService->isGranted(RefData::PERMISSION_INTERNAL_IRHP_ADMIN)) {
            return Escape::html($link);
        }

        return '<a class="govuk-link" href="' . $url . '">' . Escape::html($link) . '</a>';
    }
}
