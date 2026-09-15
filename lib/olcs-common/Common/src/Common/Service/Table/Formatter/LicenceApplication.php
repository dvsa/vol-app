<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Util\Escape;
use Common\View\Helper\Status as StatusHelper;
use Laminas\View\HelperPluginManager;

/**
 * LicenceApplication formatter
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceApplication implements FormatterPluginManagerInterface
{
    private const LINK_WITH_STATUS = '<a class="govuk-link" href="%s">%s</a>%s';

    public function __construct(private UrlHelper $urlHelper, private HelperPluginManager $viewHelperManager)
    {
    }

    /**
     * Format a cell with links to licence and application
     *
     * @param array $row    row of data
     * @param array $column column
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = null)
    {
        /**
         * @var StatusHelper $statusHelper
         */
        $statusHelper = $this->viewHelperManager->get('status');

        $licenceStatus = [
            'id' => $row['licStatus'],
            'description' => $row['licStatusDesc']
        ];

        $licenceLink = sprintf(
            self::LINK_WITH_STATUS,
            $this->urlHelper->fromRoute('licence', ['licence' => $row['licId']]),
            Escape::html($row['licNo']),
            $statusHelper->__invoke($licenceStatus)
        );

        if (isset($row['appId'])) {
            $appStatus = [
                'id' => $row['appStatus'],
                'description' => $row['appStatusDesc']
            ];

            $appLink = sprintf(
                self::LINK_WITH_STATUS,
                $this->urlHelper->fromRoute('lva-application', ['application' => $row['appId']]),
                Escape::html($row['appId']),
                $statusHelper->__invoke($appStatus)
            );

            return $licenceLink . '<br />' . $appLink;
        }

        return $licenceLink;
    }
}
