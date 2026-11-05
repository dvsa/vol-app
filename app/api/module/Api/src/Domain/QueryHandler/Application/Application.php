<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Service\Sms\SmsNumberCapture;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Application extends AbstractQueryHandler
{
    private const string OPERATING_CENTRES_SECTION = 'operatingCentres';

    protected $repoServiceName = 'Application';
    protected $extraRepos = ['Note', 'SystemParameter'];

    /**
     * @var \Dvsa\Olcs\Api\Service\Lva\SectionAccessService
     */
    private $sectionAccessService;

    /**
     * @var \Dvsa\Olcs\Api\Service\FeesHelperService
     */
    private $feesHelper;

    private SmsNumberCapture $smsNumberCapture;

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($query);

        $this->auditRead($application);

        if ($query->getValidateAppCompletion() && $application->isVariation()) {
            $this->getCommandHandler()->handleCommand(
                UpdateApplicationCompletionCmd::create(
                    ['id' => $application->getId(), 'section' => self::OPERATING_CENTRES_SECTION]
                )
            );
        }

        $latestNote = $this->getRepo('Note')->fetchForOverview($application->getLicence()->getId());
        $result = $this->result(
            $application,
            [
                'licence' => [
                    'organisation' => [
                        'type',
                        'disqualifications',
                        'organisationPersons' => [
                            'person' => ['disqualifications']
                        ],
                    ],
                ],
                'applicationCompletion',
                's4s' => [
                    'outcome'
                ],
                'status',
                'goodsOrPsv'
            ],
            [
                'sections' => $this->sectionAccessService->getAccessibleSections($application),
                'outstandingFeeTotal' => $this->feesHelper->getTotalOutstandingFeeAmountForApplication(
                    $application->getId()
                ),
                'variationCompletion' => $application->getVariationCompletion(),
                'canCreateCase' => $application->canCreateCase(),
                'existingPublication' => !$application->getPublicationLinks()->isEmpty(),
                'isPublishable' => $application->isPublishable(),
                'latestNote' => $latestNote,
                'disableCardPayments' => $this->getRepo('SystemParameter')->getDisableSelfServeCardPayments(),
                'isMlh' => $application->getLicence()->getOrganisation()->isMlh(),
                'allowedOperatorLocation' =>
                    $application->getLicence()->getOrganisation()->getAllowedOperatorLocation(),
                'canHaveInspectionRequest' => !$application->isSpecialRestricted(),
            ]
        );

        if ($this->hasStaleAddressesCompletion($application)) {
            // Shown as not done so it can't be submitted; the stored status is left alone.
            $result->setValue('applicationCompletion', ['addressesStatus' => ApplicationCompletion::STATUS_INCOMPLETE]);
        }

        return $result;
    }

    /**
     * Addresses was completed before sms_number_capture went on, and a selfserve user now has to add one.
     */
    private function hasStaleAddressesCompletion(ApplicationEntity $application): bool
    {
        return $application->getStatus()?->getId() === ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED
            && $application->getApplicationCompletion()?->getAddressesStatus() === ApplicationCompletion::STATUS_COMPLETE
            && $this->smsNumberCapture->blocksApplication($application, $this->isExternalUser(...));
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Application
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->sectionAccessService = $container->get('SectionAccessService');
        $this->feesHelper = $container->get('FeesHelperService');
        $this->smsNumberCapture = $container->get(SmsNumberCapture::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
