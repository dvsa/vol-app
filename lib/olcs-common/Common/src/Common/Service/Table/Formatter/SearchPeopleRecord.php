<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use LmcRbacMvc\Service\AuthorizationService;

class SearchPeopleRecord implements FormatterPluginManagerInterface
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
        $showAsText = $this->authService->isGranted(RefData::PERMISSION_INTERNAL_IRHP_ADMIN);
        if (!empty($data['applicationId']) && !empty($data['licNo'])) {
            if ($showAsText) {
                return sprintf(
                    '%s / %s',
                    $this->formatCellLicNo($data, $showAsText),
                    Escape::html($data['applicationId'])
                );
            }
            return sprintf(
                '%s / <a class="govuk-link" href="%s">%s</a>',
                $this->formatCellLicNo($data, $showAsText),
                $this->urlHelper->fromRoute('lva-application', ['application' => $data['applicationId']]),
                Escape::html($data['applicationId'])
            );
        }
        if (!empty($data['tmId'])) {
            if ($showAsText) {
                $tmLink = sprintf('TM %s', Escape::html($data['tmId']));
            } else {
                $tmLink = sprintf(
                    '<a class="govuk-link" href="%s">TM %s</a>',
                    $this->urlHelper->fromRoute('transport-manager/details', ['transportManager' => $data['tmId']]),
                    Escape::html($data['tmId'])
                );
            }
            if (!empty($data['licNo'])) {
                $licenceLink = $this->formatCellLicNo($data, $showAsText);
                return $tmLink . ' / ' . $licenceLink;
            }
            return $tmLink;
        }
        if (!empty($data['licTypeDesc']) && !empty($data['licStatusDesc'])) {
            if ($showAsText) {
                return sprintf(
                    '%s, %s<br />%s',
                    Escape::html($data['licNo']),
                    Escape::html($data['licTypeDesc']),
                    Escape::html($data['licStatusDesc'])
                );
            }
            return sprintf(
                '<a class="govuk-link" href="%s">%s</a>, %s<br />%s',
                $this->urlHelper->fromRoute('licence', ['licence' => $data['licId']]),
                Escape::html($data['licNo']),
                Escape::html($data['licTypeDesc']),
                Escape::html($data['licStatusDesc'])
            );
        }
        if (!empty($data['licNo'])) {
            return $this->formatCellLicNo($data, $showAsText);
        }

        if (!empty($data['applicationId'])) {
            if ($showAsText) {
                return sprintf(
                    '%s, %s',
                    Escape::html($data['applicationId']),
                    Escape::html($data['appStatusDesc'])
                );
            }
            return sprintf(
                '<a class="govuk-link" href="%s">%s</a>, %s',
                $this->urlHelper->fromRoute('lva-application', ['application' => $data['applicationId']]),
                Escape::html($data['applicationId']),
                Escape::html($data['appStatusDesc'])
            );
        }

        return '';
    }

    /**
     * Formats a cell with a licence link based on licNo
     *
     * @param array     $row        data row
     * @param bool      $showAsText Whether to return text only
     *
     * @return string
     */
    public function formatCellLicNo($row, $showAsText = false)
    {
        if ($showAsText) {
            return Escape::html($row['licNo']);
        }

        return sprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            $this->urlHelper->fromRoute('licence-no', ['licNo' => trim($row['licNo'])]),
            Escape::html($row['licNo'])
        );
    }
}
