<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class GenerateName extends AbstractCommandHandler implements TransactionedInterface
{
    public const ERR_ORG_TYPE_INVALID = 'Can only generate Organisation name for a sole trader or partnership';
    public const ERR_ONLY_NEW_APP = 'Can only generate Organisation name for a new application';

    protected $repoServiceName = 'Organisation';
    protected $extraRepos = ['Application'];

    /** @var  \Dvsa\Olcs\Transfer\Command\Organisation\GenerateName */
    private $command;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Organisation\GenerateName $command Command
     *
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->command = $command;

        if ($command->getApplication() !== null) {
            $this->checkForApplication();
        }

        /** @var  Entity\Organisation\Organisation $organisation */
        $organisation = $this->getRepo()->fetchById($command->getOrganisation());

        if (!$organisation->isSoleTrader() && !$organisation->isPartnership()) {
            throw new ValidationException([self::ERR_ORG_TYPE_INVALID]);
        }

        //  define name
        $name = $this->generateName($organisation);
        if ($name === null) {
            return $this->result->addMessage('Unable to generate name');
        }

        //  save new name
        $organisation->setName($name);

        $this->getRepo()->save($organisation);

        $this->result->merge(
            $this->clearOrganisationCacheSideEffect($organisation->getId())
        );

        return $this->result->addMessage('Name succesfully generated');
    }

    /**
     * Check for Application
     *
     * @return void
     * @throws ValidationException
     */
    private function checkForApplication()
    {
        /** @var Entity\Application\Application $app */
        $app = $this->getRepo('Application')->fetchById($this->command->getApplication());

        if ($app->getIsVariation()) {
            throw new ValidationException([self::ERR_ONLY_NEW_APP]);
        }
    }

    /**
     * Generate the name for an organisation
     *
     * @param Organisation $organisation Organistion
     *
     * @return string|boolean false if cannot generate, otherwise the generated name
     */
    private function generateName(Organisation $organisation)
    {
        $orgPersons = $organisation->getOrganisationPersons();

        //  check have persons
        $cnt = $orgPersons->count();
        if ($cnt == 0) {
            return null;
        }

        //  sole traider
        /** @var Entity\Person\Person $person */
        if ($organisation->isSoleTrader()) {
            $person = $orgPersons->first()->getPerson();

            return $person->getForename() . ' ' . $person->getFamilyName();
        }

        // must be a partnership
        $person = $orgPersons->first()->getPerson();

        $name = [
            $person->getForename(),
            $person->getFamilyName(),
        ];

        if ($cnt == 2) {
            $person = $orgPersons->next()->getPerson();
            array_push($name, '&', $person->getForename(), $person->getFamilyName());
        } elseif ($cnt > 2) {
            array_push($name, '&', 'Partners');
        }

        return implode(' ', $name);
    }
}
