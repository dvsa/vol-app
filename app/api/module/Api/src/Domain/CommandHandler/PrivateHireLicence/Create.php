<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PrivateHireLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\PrivateHireLicence\Create as Command;
use Dvsa\Olcs\Api\Entity\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Create PrivateHireLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    public const string PHL_INVALID_TA = 'PHL_INVALID_TA';

    protected $repoServiceName = 'PrivateHireLicence';

    protected $extraRepos = ['ContactDetails'];

    public function __construct(protected AddressHelperService $addressHelperService)
    {
    }

    public function handleCommand(CommandInterface $command): \Dvsa\Olcs\Api\Domain\Command\Result
    {
        /* @var $command Command */
        $address = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $address->updateAddress(
            $command->getAddress()['addressLine1'],
            $command->getAddress()['addressLine2'],
            $command->getAddress()['addressLine3'],
            $command->getAddress()['addressLine4'],
            $command->getAddress()['town'],
            $command->getAddress()['postcode'],
            $this->getRepo()->getReference(ContactDetails\Country::class, $command->getAddress()['countryCode'])
        );

        $cd = new ContactDetails\ContactDetails(
            $this->getRepo()->getRefdataReference(ContactDetails\ContactDetails::CONTACT_TYPE_HACKNEY)
        );
        $cd->setDescription($command->getCouncilName())
            ->setAddress($address);

        $phl = new PrivateHireLicence();
        $phl->setLicence($this->getRepo()->getReference(Licence::class, $command->getLicence()))
            ->setPrivateHireLicenceNo($command->getPrivateHireLicenceNo())
            ->setContactDetails($cd);

        $this->checkTrafficArea($command->getAddress()['postcode'], $phl);

        $this->getRepo('ContactDetails')->save($cd);
        $this->getRepo()->save($phl);

        $this->result->addId('address', $address->getId());
        $this->result->addId('contactDetails', $cd->getId());
        $this->result->addId('privateHireLicence', $phl->getId());
        $this->result->addMessage('PrivateHireLicence created');

        if (
            $this->isGranted(Permission::SELFSERVE_USER) &&
            ($command->getLva() === 'licence')
        ) {
            $data = [
                'licence' => $command->getLicence(),
                'category' => CategoryEntity::CATEGORY_APPLICATION,
                'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_CHANGE_TO_TAXI_PHV_DIGITAL,
                'description' => 'Taxi licence added - ' . $phl->getPrivateHireLicenceNo(),
                'isClosed' => 0,
                'urgent' => 0
            ];
            $this->result->merge($this->handleSideEffect(CreateTaskCmd::create($data)));
        }

        return $this->result;
    }

    /**
     * Check and possible update the licence traffic area
     *
     * @param string $postcode
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     */
    protected function checkTrafficArea(string $postcode, PrivateHireLicence $phl): void
    {
        $postCodeTrafficArea = $this->addressHelperService->fetchTrafficAreaByPostcodeOrUprn(
            $postcode
        );

        if (!$postCodeTrafficArea) {
            return;
        }

        // if TA not set
        if ($phl->getLicence()->getTrafficArea() === null) {
            // update the licence TA
            $data = [
                'id' => $phl->getLicence()->getId(),
                'version' => $phl->getLicence()->getVersion(),
                'trafficArea' => $postCodeTrafficArea->getId(),
            ];
            $this->result->merge(
                $this->handleSideEffect(\Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea::create($data))
            );
        } else {
            // check that the updated PHL's postcode is in the TA
            if ($phl->getLicence()->getTrafficArea() !== $postCodeTrafficArea) {
                $message = 'Your Taxi/PHV licence is in ' . $postCodeTrafficArea->getName() .
                    ' traffic area, which differs to your first Taxi/PHV Licence (' .
                    $phl->getLicence()->getTrafficArea()->getName() .
                    '). You will need to apply for more than one Special Restricted licence.';
                throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                    [self::PHL_INVALID_TA => $message]
                );
            }
        }
    }
}
