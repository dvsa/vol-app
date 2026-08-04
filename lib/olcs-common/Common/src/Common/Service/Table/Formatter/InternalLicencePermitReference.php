<?php

/**
 * Internal licence permit reference formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Common\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;

/**
 * Internal licence permit reference formatter
 */
class InternalLicencePermitReference implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper, private Permission $permissionService)
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
        $applicationRef = Escape::html($row['applicationRef']);

        if ($this->permissionService->isInternalReadOnly()) {
            return $applicationRef;
        }

        $route = 'licence/irhp-application/application';
        $params = [
            'licence' => $row['licenceId'],
            'action' => 'edit',
            'irhpAppId' => $row['id']
        ];

        return vsprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            [
                $this->urlHelper->fromRoute($route, $params),
                $applicationRef
            ]
        );
    }
}
