<?php

/**
 * Internal licence conversation link
 */

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use Laminas\Router\Http\RouteMatch;

/**
 * Internal licence conversation link
 */
class InternalConversationLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper, RefDataStatus $refDataStatus, private RouteMatch $route)
    {
    }

    /**
     * status
     *
     * @param array $row Row data
     * @param array $column Column data
     *
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = null)
    {
        switch ($this->route->getParam('type')) {
            case 'application':
                $route = 'lva-application/conversation/view';
                $params = [
                    'application' => $this->route->getParam('application'),
                ];
                break;
            case 'licence':
                $route = 'licence/conversation/view';
                $params = [
                    'licence' => $row['task']['licence']['id'],
                ];
                break;
            case 'case':
                $route = 'case_conversation/view';
                $params = [
                    'licence' => $row['task']['licence']['id'],
                    'case'    => $this->route->getParam('case'),
                ];
                break;
            case 'busReg':
                $route = 'licence/bus_conversation/view';
                $params = [
                    'licence' => $row['task']['licence']['id'],
                    'busRegId' => $this->route->getParam('busRegId'),
                ];
                break;
            case 'irhp-application':
                $route = 'licence/irhp-application-conversation/view';
                $params = [
                    'licence' => $row['task']['licence']['id'],
                    'irhpAppId' => $this->route->getParam('irhpAppId'),
                ];
                break;
            default:
                throw new DomainException('Invalid route type');
        }

        $params['conversation'] = $row['id'];

        $statusCSS = '';

        switch ($row['userContextStatus']) {
            case "NEW_MESSAGE":
                $statusCSS = 'govuk-!-font-weight-bold';
                $tagColor = 'govuk-tag--red';
                break;
            case "OPEN":
                $tagColor = 'govuk-tag--blue';
                break;
            case "CLOSED":
                $tagColor = 'govuk-tag--grey';
                break;
            default:
                $tagColor = 'govuk-tag--green';
                break;
        }

        // $row["createdOn"] already contains a timezone so createFromFormat will ignore any timezone passed as the
        // third parameter. to override it we need to force set the timezone to the default one
        $latestMessageCreatedOn = DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            $row["createdOn"]
        )->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        $dtOutput = $latestMessageCreatedOn->format('l j F Y \a\t H:ia');

        if (isset($row['task']['application']['id'])) {
            $idMatrix = Escape::html($row['task']['licence']['licNo'] . " / " . $row['task']['application']['id']);
        } else {
            $idMatrix = Escape::html($row['task']['licence']['licNo']);
        }

        $html = '<a class="govuk-body govuk-link govuk-!-padding-right-1 %s" href="%s">%s: %s</a><strong class="govuk-tag %s">%s</strong><br><p class="govuk-body govuk-!-margin-1">%s</p>';

        return sprintf(
            $html,
            $statusCSS,
            $this->urlHelper->fromRoute($route, $params),
            $idMatrix,
            $row["subject"],
            $tagColor,
            ucfirst(strtolower(str_replace('_', ' ', $row['userContextStatus']))),
            $dtOutput,
        );
    }
}
