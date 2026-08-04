<?php

/**
 * Community Licence status formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * Community Licence status formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicenceStatus implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper, private TreeRouteStack $router, private Request $request)
    {
    }

    /**
     * Format
     *
     * @param array $data   data
     * @param array $column column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {

        $routeMatch = $this->router->match($this->request);
        $matchedRouteName = $routeMatch->getMatchedRouteName();
        $url = $this->urlHelper->fromRoute(
            $matchedRouteName,
            ['child_id' => $data['id'], 'action' => 'edit'],
            ['query' => $this->request->getQuery()->toArray()],
            true
        );

        $futureSuspension = $data['futureSuspension'];

        if ($futureSuspension && isset($futureSuspension['startDate']) && isset($futureSuspension['endDate'])) {
            return '<a class="govuk-link" href="' . $url . '">' .
                'Suspension due: ' .
                $this->formatDate($futureSuspension['startDate']) .
                ' to ' .
                $this->formatDate($futureSuspension['endDate']) .
                '</a>';
        }

        if ($futureSuspension && isset($futureSuspension['startDate']) && !isset($futureSuspension['endDate'])) {
            return '<a class="govuk-link" href="' . $url . '">' .
                'Suspension due: ' .
                $this->formatDate($futureSuspension['startDate']) .
                '</a>';
        }

        $currentSuspension = $data['currentSuspension'];

        if ($currentSuspension && isset($currentSuspension['startDate']) && isset($currentSuspension['endDate'])) {
            return '<a class="govuk-link" href="' . $url . '">' .
                'Suspended: ' .
                $this->formatDate($currentSuspension['startDate']) .
                ' to ' .
                $this->formatDate($currentSuspension['endDate']) .
                '</a>';
        }

        if ($currentSuspension && isset($currentSuspension['startDate']) && !isset($currentSuspension['endDate'])) {
            return '<a class="govuk-link" href="' . $url . '">' .
                'Suspended: ' .
                $this->formatDate($currentSuspension['startDate']) .
                '</a>';
        }

        $currentWithdrawal = $data['currentWithdrawal'];

        if ($currentWithdrawal && isset($currentWithdrawal['startDate'])) {
            return 'Withdrawn: ' . $this->formatDate($currentWithdrawal['startDate']);
        }

        if (isset($data['expiredDate'])) {
            return $data['status']['description'] . ': ' . $this->formatDate($data['expiredDate']);
        }

        return $data['status']['description'];
    }

    /**
     * Format date
     *
     * @param DateTime $date date
     *
     * @return bool|string
     */
    private function formatDate($date)
    {
        return date('d/m/Y', strtotime($date));
    }
}
