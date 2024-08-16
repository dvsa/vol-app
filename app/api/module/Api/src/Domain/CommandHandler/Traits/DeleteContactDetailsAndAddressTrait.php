<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;

trait DeleteContactDetailsAndAddressTrait
{
    private function injectRepos()
    {
        $requiredRepos = ['ContactDetails', 'Address'];
        foreach ($requiredRepos as $requiredRepo) {
            if (!in_array($requiredRepo, $this->extraRepos)) {
                $this->extraRepos[] = $requiredRepo;
            }
        }
    }

    /**
     * @param ContactDetails $contactDetails
     *
     * @return void
     */
    private function maybeDeleteContactDetailsAndAddress($contactDetails)
    {
        if ($contactDetails === null) {
            return;
        }
        $this->injectRepos();
        if ($contactDetails->getAddress() !== null) {
            $addressRepo = $this->getRepo('Address');
            $address = $contactDetails->getAddress();
            $addressRepo->refresh($address);
            $addressRepo->delete($address);
        }
        $this->getRepo('ContactDetails')->delete($contactDetails);
    }
}
