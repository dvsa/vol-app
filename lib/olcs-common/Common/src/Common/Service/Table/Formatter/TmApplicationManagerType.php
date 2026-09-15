<?php

/**
 * Tm Application Manager Type formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;
use Common\Service\Helper\UrlHelperService;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\Mvc\Application;

/**
 * Tm Application Manager Type formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmApplicationManagerType implements FormatterPluginManagerInterface
{
    public function __construct(private Application $application, private UrlHelperService $urlHelper, private TranslatorDelegator $translator)
    {
    }

    /**
     * Tm Application Manager Type formatter
     *
     * @param  array $row
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($row, $column = [])
    {
        $routeParams = [
            'id' => $row['id'],
            'action' => 'edit-tm-application',
            'transportManager' => $this->application
                ->getMvcEvent()
                ->getRouteMatch()
                ->getParam('transportManager')
        ];
        $url = $this->urlHelper->fromRoute(null, $routeParams);
        $status = match ($row['action']) {
            'A' => $this->translator->translate('tm_application.table.status.new'),
            'U' => $this->translator->translate('tm_application.table.status.updated'),
            'D' => $this->translator->translate('tm_application.table.status.removed'),
            default => '',
        };

        // $status is a translation of a fixed key, so it stays raw; the description is row data.
        $description = Escape::html($row['tmType']['description']);

        return $row['action'] === 'D' ? trim($description . ' ' . $status) :
            '<a class="govuk-link" href="' . $url . '">' . trim($description . ' ' . $status) . '</a>';
    }
}
