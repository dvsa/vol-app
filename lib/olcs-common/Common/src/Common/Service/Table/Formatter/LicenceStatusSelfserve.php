<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * Licence status is shown slightly differently in selfserve, with certain statuses mapped to "expired" status
 */
class LicenceStatusSelfserve implements FormatterPluginManagerInterface
{
    private const MARKUP_FORMAT = '<span class="govuk-tag govuk-tag--%s">%s</span>';

    public function __construct(private TranslatorDelegator $translator)
    {
    }

    /**
     * @param array $row    Row data
     * @param array $column Column data
     *
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = null)
    {
        $statusClass = match ($row['status']['id']) {
            RefData::LICENCE_STATUS_VALID, RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION => 'green',
            RefData::LICENCE_STATUS_SUSPENDED, RefData::LICENCE_STATUS_CURTAILED, RefData::LICENCE_STATUS_UNDER_CONSIDERATION, RefData::LICENCE_STATUS_GRANTED => 'orange',
            RefData::LICENCE_STATUS_SURRENDERED, RefData::LICENCE_STATUS_REVOKED, RefData::LICENCE_STATUS_TERMINATED, RefData::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, RefData::LICENCE_STATUS_WITHDRAWN, RefData::LICENCE_STATUS_REFUSED, RefData::LICENCE_STATUS_NOT_TAKEN_UP => 'red',
            default => 'grey',
        };

        if ($row['status']['id'] !== RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION) {
            [$row, $statusClass] = $this->changeStateIfExpired($row, $statusClass);
        }

        return sprintf(
            self::MARKUP_FORMAT,
            $statusClass,
            Escape::html($this->translator->translate($row['status']['description']))
        );
    }

    protected function changeStateIfExpired(array $row, string $statusClass): array
    {
        if (isset($row['isExpired']) && $row['isExpired'] === true) {
            $row['status']['description'] = 'licence.status.expired';
            $statusClass = 'red';
        }

        if (isset($row['isExpiring']) && $row['isExpiring'] === true) {
            $row['status']['description'] = 'licence.status.expiring';
            $statusClass = 'red';
        }

        return [$row, $statusClass];
    }
}
