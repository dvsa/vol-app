<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\View\Helper\TranslateReplace;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\View\HelperPluginManager;

/**
 * Dashboard Transport Manager Action Link
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardTmActionLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper, private HelperPluginManager $viewHelperManager, private TranslatorDelegator $translator)
    {
    }

    /**
     * Generate the HTML to display the Action link
     *
     * @param array $data   Row data
     * @param array $column Column parameters
     *
     * @return string HTML
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $provideStatuses = [
            RefData::TMA_STATUS_INCOMPLETE,
            RefData::TMA_STATUS_AWAITING_SIGNATURE,
        ];

        if (in_array($data['transportManagerApplicationStatus']['id'], $provideStatuses, true)) {
            $linkText = 'dashboard.tm-applications.table.action.provide-details';
            $ariaLabel = 'dashboard.tm-applications.table.aria.provide-details';
        } else {
            $linkText = 'dashboard.tm-applications.table.action.view-details';
            $ariaLabel = 'dashboard.tm-applications.table.aria.view-details';
        }

        /** @var TranslateReplace $translateReplace */
        $translateReplace = $this->viewHelperManager->get('translateReplace');

        return sprintf(
            '<a class="govuk-link" href="%s" aria-label="%s">%s</a>',
            static::getApplicationUrl(
                $data['applicationId'],
                $data['transportManagerApplicationId'],
                $data['isVariation']
            ),
            $translateReplace($ariaLabel, [$data['applicationId']]),
            $this->translator->translate($linkText)
        );
    }

    /**
     * Get the hyperlink for the application number
     *
     * @param int  $applicationId                 Application id
     * @param int  $transportManagerApplicationId TM Application Id
     * @param bool $isVariation                   Is this application a variation
     *
     * @return string URL
     */
    protected function getApplicationUrl($applicationId, $transportManagerApplicationId, $isVariation)
    {
        $lva = ($isVariation) ? 'variation' : 'application';
        $route = 'lva-' . $lva . '/transport_manager_details';

        return $this->urlHelper->fromRoute(
            $route,
            ['action' => null, 'application' => $applicationId, 'child_id' => $transportManagerApplicationId],
            [],
            true
        );
    }
}
