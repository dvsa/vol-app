<?php

/**
 * Change Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Domain\Command\Organisation\ChangeBusinessType as Cmd;

/**
 * Change Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ChangeBusinessType extends AbstractCommandHandler implements TransactionedInterface
{
    public const REG_TO_ST = 'REG_TO_ST';
    public const ANY_TO_ST = 'ANY_TO_ST';
    public const BUS_TYP_REQ_CONF = 'BUS_TYP_REQ_CONF';

    protected $repoServiceName = 'Organisation';

    protected $extraRepos = ['CompanySubsidiary', 'OrganisationPerson'];

    protected $transitions = [
        self::REG_TO_ST => [
            'from' => [
                Organisation::ORG_TYPE_REGISTERED_COMPANY,
                Organisation::ORG_TYPE_LLP
            ],
            'to' => [
                Organisation::ORG_TYPE_SOLE_TRADER,
                Organisation::ORG_TYPE_PARTNERSHIP,
                Organisation::ORG_TYPE_OTHER
            ]
        ],
        self::ANY_TO_ST => [
            'from' => [
                Organisation::ORG_TYPE_PARTNERSHIP,
                Organisation::ORG_TYPE_OTHER,
                Organisation::ORG_TYPE_REGISTERED_COMPANY,
                Organisation::ORG_TYPE_LLP,
                Organisation::ORG_TYPE_IRFO
            ],
            'to' => [
                Organisation::ORG_TYPE_SOLE_TRADER
            ]
        ]
    ];

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Organisation $organisation */
        $organisation = $this->getRepo()->fetchUsingId($command);

        // Check which transitions we need to apply
        $transitions = $this->getTransitions($organisation, $command->getBusinessType());

        // If we need to confirm, but haven't confirmed
        if (!empty($transitions) && $command->getConfirm() !== true) {
            throw new RequiresConfirmationException(json_encode($transitions), self::BUS_TYP_REQ_CONF);
        }

        $this->processTransitions($transitions, $organisation);

        $newType = $this->getRepo()->getRefdataReference($command->getBusinessType());

        $organisation->setType($newType);

        $this->getRepo()->save($organisation);

        return $this->result;
    }

    protected function processTransitions(array $transitions, Organisation $organisation)
    {
        foreach ($transitions as $transition) {
            switch ($transition) {
                case self::REG_TO_ST:
                    $organisation->setCompanyOrLlpNo(null);
                    $organisation->setContactDetails(null);

                    /** @var Licence $licence */
                    foreach ($organisation->getLicences() as $licence) {
                        /** @var CompanySubsidiary $companySubsidiary */
                        foreach ($licence->getCompanySubsidiaries() as $companySubsidiary) {
                            $this->getRepo('CompanySubsidiary')->delete($companySubsidiary);
                        }
                    }

                    break;
                case self::ANY_TO_ST:
                    $organisationPeople = $organisation->getOrganisationPersons();

                    $first = true;
                    /** @var OrganisationPerson $orgPerson */
                    foreach ($organisationPeople as $orgPerson) {
                        if ($first) {
                            $first = false;
                            continue;
                        }

                        $this->getRepo('OrganisationPerson')->delete($orgPerson);
                    }

                    break;
            }
        }
    }

    protected function getTransitions(Organisation $organisation, $toType)
    {
        $fromType = $organisation->getType()->getId();

        $transitions = [];

        foreach ($this->transitions as $code => $config) {
            if (in_array($fromType, $config['from']) && in_array($toType, $config['to'])) {
                $transitions[] = $code;
            }
        }

        return $transitions;
    }
}
