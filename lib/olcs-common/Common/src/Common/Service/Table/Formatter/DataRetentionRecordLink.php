<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use Common\View\Helper\Status as StatusHelper;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\View\HelperPluginManager;

/**
 * Data Retention Record Link
 */
class DataRetentionRecordLink implements FormatterPluginManagerInterface
{
    private const ENTITY_TRANSPORT_MANAGER = 'transport_manager';

    private const ENTITY_IRFO_GV_PERMIT = 'irfo_gv_permit';

    private const ENTITY_IRFO_PSV_AUTH = 'irfo_psv_auth';

    private const ENTITY_ORGANISATION = 'organisation';

    private const ENTITY_APPLICATION = 'application';

    private const ENTITY_BUS_REG = 'bus_reg';

    private const ENTITY_LICENCE = 'licence';

    private const ENTITY_CASES = 'cases';

    public const STATUS_DELETION = [
        'value' => 'Marked for deletion',
        'colour' => 'red'
    ];

    public const STATUS_POSTPONED = [
        'value' => 'Postponed',
        'colour' => 'orange'
    ];

    public const STATUS_REVIEW = [
        'value' => 'To review',
        'colour' => 'green'
    ];

    public function __construct(protected UrlHelperService $urlHelper, protected HelperPluginManager $viewHelperManager)
    {
    }

    /**
     * Format column value
     *
     * @param array $data   Row data
     * @param array $column Column Parameters
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $statusHelper = $this->viewHelperManager->get('status');

        $url = match ($data['entityName']) {
            self::ENTITY_LICENCE => $this->urlHelper->fromRoute('licence', ['licence' => $data['entityPk']], [], true),
            self::ENTITY_APPLICATION => $this->urlHelper->fromRoute('lva-application', ['application' => $data['entityPk']], [], true),
            self::ENTITY_TRANSPORT_MANAGER => $this->urlHelper->fromRoute(
                'transport-manager',
                ['transportManager' => $data['entityPk']],
                [],
                true
            ),
            self::ENTITY_IRFO_GV_PERMIT => $this->urlHelper->fromRoute(
                'operator/irfo/gv-permits',
                [
                'organisation' => $data['organisationId'],
                'action' => 'details',
                'id' => $data['entityPk']
                ],
                [],
                true
            ),
            self::ENTITY_IRFO_PSV_AUTH => $this->urlHelper->fromRoute(
                'operator/irfo/psv-authorisations',
                [
                'organisation' => $data['organisationId'],
                'action' => 'edit',
                'id' => $data['entityPk']
                ],
                [],
                true
            ),
            self::ENTITY_ORGANISATION => $this->urlHelper->fromRoute(
                'operator/business-details',
                ['organisation' => $data['organisationId']],
                [],
                true
            ),
            self::ENTITY_CASES => $this->urlHelper->fromRoute(
                'case',
                ['action' => 'details', 'case' => $data['entityPk']],
                [],
                true
            ),
            self::ENTITY_BUS_REG => $this->urlHelper->fromRoute(
                'licence/bus-details',
                [
                'licence' => $data['licenceId'],
                'busRegId' => $data['entityPk']
                ],
                [],
                true
            ),
            default => null,
        };

        $output = self::getOutput(
            Escape::html($data['organisationId']),
            Escape::html($data['organisationName']),
            Escape::html($data['licNo']),
            Escape::html($data['entityName']),
            Escape::html($data['entityPk']),
            $url
        );

        $statusInfo = $this->getStatus($data['actionConfirmation'], $data['nextReviewDate']);
        $status = $statusHelper->__invoke($statusInfo);

        return $output . $status;
    }

    /**
     * render output for the table
     *
     * @param int         $organisationId   Organisation id
     * @param string      $organisationName Organisation name
     * @param string      $licNo            Licence number
     * @param string      $entityName       Entity name
     * @param string      $entityPk         Entity Primary Key
     * @param string|null $url              URL
     *
     * @return string
     */
    private function getOutput(
        $organisationId,
        $organisationName,
        $licNo,
        $entityName,
        $entityPk,
        $url = null
    ) {
        $licenceNumber = self::getLicenceNumber($licNo, $url);
        $organisationName = self::getOrganisationName($organisationId, $organisationName, $url);

        if ($url === null) {
            return $organisationName .
                $licenceNumber .
                $entityName . ' ' .
                $entityPk;
        }

        return $organisationName .
        $licenceNumber .
        sprintf('<a class="govuk-link" href="%s" target="_self">%s</a>', $url, ucfirst($entityName) . ' ' . $entityPk);
    }

    /**
     * Get licence number value for output, if URL or non URL
     *
     * @param string $licenceNumber Licence number value
     * @param string $url           URL
     *
     * @return string
     */
    private function getLicenceNumber($licenceNumber, $url = null)
    {
        if (empty($licenceNumber)) {
            return '';
        }

        if (!is_null($url)) {
            $url = $this->urlHelper->fromRoute(
                'licence-no',
                ['licNo' => $licenceNumber],
                [],
                true
            );

            return sprintf(
                '<a class="govuk-link" href="%s" target="_self">%s</a>',
                $url,
                $licenceNumber
            ) . ' / ';
        }

        return $licenceNumber . ' / ';
    }

    /**
     * Get organisation name value for output, if URL or non URL
     *
     * @param int    $organisationId   Organisation ID
     * @param string $organisationName Organisation number value
     * @param string $url              URL
     *
     * @return string
     */
    private function getOrganisationName($organisationId, $organisationName, $url = null)
    {
        if (empty($organisationId) || empty($organisationName)) {
            return '';
        }

        if (!is_null($url)) {
            $url = $this->urlHelper->fromRoute(
                'operator/business-details',
                ['organisation' => $organisationId],
                [],
                true
            );

            return sprintf(
                '<a class="govuk-link" href="%s" target="_self">%s</a>',
                $url,
                $organisationName
            ) .
            ' / ';
        }

        return $organisationName . ' / ';
    }

    /**
     * Determine the status
     * This ought to come from backend ref data or be calculated by the entity, but not currently available.
     *
     * @param bool        $actionConfirmation action confirmation
     * @param string|null $nextReviewDate     next review date
     *
     * @return array
     */
    private function getStatus($actionConfirmation, $nextReviewDate)
    {
        $status = self::STATUS_DELETION;

        if ($actionConfirmation === false) {
            $status = self::STATUS_POSTPONED;

            if (is_null($nextReviewDate) || new \DateTime($nextReviewDate) <= new \DateTime()) {
                $status = self::STATUS_REVIEW;
            }
        }

        return $status;
    }
}
