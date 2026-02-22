<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Organisation\UpdateTradingNames;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Save (Create/Update) Business Details
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
final class SaveBusinessDetails extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    public const string ERR_NOT_ALLOW_UPDATE_ORG_NAME = 'You are not allowed to update the organisation name';
    public const string ERR_NOT_ALLOW_UPDATE_ORG_NUM = 'You are not allowed to update the company number';

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Organisation'];

    //  contact details was changed
    private $hasChanged = false;

    /** @var  \Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails */
    private $command;
    /** @var  Organisation */
    private $org;

    /**
     * @inheritdoc
     * @param \Dvsa\Olcs\Api\Domain\Command\Licence\SaveBusinessDetails $command
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->command = $command;

        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($command);

        $this->org = $licence->getOrganisation();

        $version = $command->getVersion();

        $orgRepo = $this->getRepo('Organisation');

        // Optimistic locking on the org
        $orgRepo->lock($this->org, $version);

        $licenceId = $licence->getId();
        $this->setDetails();
        $this->setRegAddress();
        $this->setNatureOfBusiness();
        $this->setTradingNames($licenceId);

        $this->result->merge(
            $this->clearLicenceCacheSideEffect($licenceId)
        );

        $orgRepo->save($this->org);

        $hasChanged = (
            (bool) ($this->org->getVersion() - $version)
            || $this->hasChanged
        );

        $this->result->setFlag('hasChanged', $hasChanged);

        return $this->result;
    }

    /**
     * Method set organisation data
     *
     * @throws ForbiddenException
     */
    private function setDetails()
    {
        $canUpdate = ($this->isGranted(Permission::INTERNAL_USER) || !$this->org->hasInforceLicences());

        $name = $this->command->getName();
        if (!empty($name) && $name !== $this->org->getName()) {
            if (!$canUpdate) {
                throw new ForbiddenException(self::ERR_NOT_ALLOW_UPDATE_ORG_NAME);
            }

            $this->org->setName($name);
        }

        $number = $this->command->getCompanyOrLlpNo();
        if (!empty($number) && $number !== $this->org->getCompanyOrLlpNo()) {
            if (!$canUpdate) {
                throw new ForbiddenException(self::ERR_NOT_ALLOW_UPDATE_ORG_NUM);
            }

            $this->org->setCompanyOrLlpNo($number);
        }

        // update allowEmail flag regardless of all conditions
        $allowEmail = $this->command->getAllowEmail();
        if ($allowEmail !== null) {
            $this->org->setAllowEmail($allowEmail);
        }
    }

    /**
     * set organisation address of registration
     */
    private function setRegAddress()
    {
        $regAddress = $this->command->getRegisteredAddress();
        if (empty($regAddress)) {
            return;
        }

        $regAddress['contactType'] = ContactDetails::CONTACT_TYPE_REGISTERED_ADDRESS;

        $result = $this->handleSideEffect(
            SaveAddress::create($regAddress)
        );
        $this->handleSideEffectResult($result);

        //  attach contact details to organisation
        $contactDetailsId = $result->getId('contactDetails');
        if ($contactDetailsId !== null) {
            $this->org->setContactDetails(
                $this->getRepo()->getReference(ContactDetails::class, $contactDetailsId)
            );
        }
    }

    /**
     * Set nature of business of organisation
     */
    private function setNatureOfBusiness()
    {
        $this->org->setNatureOfBusiness($this->command->getNatureOfBusiness());
    }

    /**
     * Set trading names for licence
     *
     * @param $licenceId
     */
    private function setTradingNames($licenceId)
    {
        $result = $this->handleSideEffect(
            UpdateTradingNames::create(
                [
                    'licence' => $licenceId,
                    'tradingNames' => $this->command->getTradingNames(),
                ]
            )
        );

        if ($result->getFlag('hasChanged')) {
            // if trading names have changed, then set a flag on the result
            $this->result->setFlag('tradingNamesChanged', true);
        }

        $this->handleSideEffectResult($result);
    }

    /**
     * Update hasChanged status in depend of result of side effect command
     */
    private function handleSideEffectResult(Result $result)
    {
        $this->result->merge($result);

        $this->hasChanged = ($this->hasChanged || $result->getFlag('hasChanged'));
    }
}
