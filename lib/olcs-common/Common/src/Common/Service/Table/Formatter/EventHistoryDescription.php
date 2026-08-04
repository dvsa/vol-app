<?php

/**
 * Event History Description
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

/**
 * Event History Description
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EventHistoryDescription implements FormatterPluginManagerInterface
{
    public function __construct(private TreeRouteStack $router, private Request $request, private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format
     *
     * @param array $data   Event data
     * @param array $column Column data
     *
     * @return                                        string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $routeMatch = $this->router->match($this->request);
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        $entity = $this->getEntityName($data);
        // special case for busReg!
        if ($entity === 'busReg') {
            $entity = 'busRegId';
            $id = $data['busReg'];
        } else {
            $id = $data[$entity]['id'];
        }

        $url = $this->urlHelper->fromRoute(
            $matchedRouteName,
            [
                'action' => 'edit',
                $entity => $id,
                'id' => $data['id'],
            ],
            [],
            true
        );

        $text = $data['eventHistoryType']['description'] ?? '';

        return sprintf(
            '<a class="govuk-link js-modal-ajax" href="%s">%s</a>',
            $url,
            $text
        );
    }

    /**
     * Discover which entity the the event is linked to
     *
     * @param array $data Event data
     *
     * @return string Entity name
     * @throws \Exception
     */
    private function getEntityName($data)
    {
        $possibleEntities = ['application', 'licence', 'busReg', 'transportManager', 'organisation', 'case', 'irhpApplication'];

        foreach ($possibleEntities as $possibleEntity) {
            if (isset($data[$possibleEntity])) {
                return $possibleEntity;
            }
        }

        throw new \Exception('Not implemented');
    }
}
