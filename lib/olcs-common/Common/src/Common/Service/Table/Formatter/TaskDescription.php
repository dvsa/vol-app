<?php

/**
 * Task description formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * Task description formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TaskDescription implements FormatterPluginManagerInterface
{
    public function __construct(private TreeRouteStack $router, private Request $request, private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format a task description
     *
     * @param      array $row
     * @param      array $column
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = [])
    {
        $routeMatch = $this->router->match($this->request);
        $params     = $routeMatch->getParams();

        // the edit URL should pass the context of the page we're on,
        // rather than the type of each task
        // (see https://jira.i-env.net/browse/OLCS-6041)
        $routeParams = match ($routeMatch->getMatchedRouteName()) {
            'licence/processing/tasks' => ['type' => 'licence', 'typeId' => $params['licence']],
            'lva-application/processing/tasks' => ['type' => 'application', 'typeId' => $params['application']],
            'transport-manager/processing/tasks' => ['type' => 'tm', 'typeId' => $params['transportManager']],
            'licence/bus-processing/tasks' => [
            'type'    => 'busreg',
            'typeId'  => $params['busRegId'],
            'licence' => $params['licence']
            ],
            'licence/irhp-application-processing/tasks' => [
            'type'    => 'irhpapplication',
            'typeId'  => $params['irhpAppId'],
            'licence' => $params['licence']
            ],
            'case_processing_tasks' => ['type' => 'case', 'typeId' => $params['case']],
            'operator/processing/tasks' => [
            'type'    => 'organisation',
            'typeId'  => $params['organisation']
            ],
            default => [],
        };

        $url = $this->urlHelper->fromRoute(
            'task_action',
            array_merge(
                [
                    'task' => $row['id'],
                    'action' => 'edit',
                ],
                $routeParams
            ),
            [
                'query' => $this->request->getQuery()->toArray()
            ]
        );

        return sprintf('<a href="%s" class="govuk-link js-modal-ajax">%s</a>', $url, $row['description']);
    }
}
