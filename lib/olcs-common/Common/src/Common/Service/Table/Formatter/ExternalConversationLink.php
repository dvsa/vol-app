<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use DateTimeImmutable;
use DateTimeInterface;

class ExternalConversationLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * status
     */
    #[\Override]
    public function format(array $data, array $column = []): string
    {
        $route = 'conversations/view';
        $params = [
            'conversationId' => $data['id'],
        ];

        $statusCSS = '';
        if ($data['userContextStatus'] === "NEW_MESSAGE") {
            $statusCSS = 'govuk-!-font-weight-bold';
        }

        $rows = '
            <a class="govuk-body govuk-link govuk-!-padding-right-1 %s" href="%s">%s: %s</a>
            <br>
            <p class="govuk-body govuk-!-margin-1">%s</p>
        ';

        // $data["createdOn"] already contains a timezone so createFromFormat will ignore any timezone passed as the
        // third parameter. to override it we need to force set the timezone to the default one
        $latestMessageCreatedOn = DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            $data["createdOn"]
        )->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        $dtOutput = $latestMessageCreatedOn->format('l j F Y \a\t H:ia');

        if (isset($data['task']['application']['id'])) {
            $idMatrix = Escape::html($data['task']['licence']['licNo'] . " / " . $data['task']['application']['id']);
        } else {
            $idMatrix = Escape::html($data['task']['licence']['licNo']);
        }

        return vsprintf(
            $rows,
            [
                $statusCSS,
                $this->urlHelper->fromRoute($route, $params),
                $idMatrix,
                $data["subject"],
                $dtOutput,
            ],
        );
    }
}
