<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareTrait;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;

/**
 * Class LicenceAddress
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Licence
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class LicenceAddress extends AbstractContext implements AddressFormatterAwareInterface
{
    use AddressFormatterAwareTrait;

    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $licence = $publicationLink->getLicence();
        $licenceAddress = '';

        //make sure we have a licence
        if ($licence instanceof LicenceEntity) {
            $contactDetails = $licence->getCorrespondenceCd();

            //make sure the licence has contact details
            if ($contactDetails instanceof ContactDetailsEntity) {
                $licenceAddress = $this->getAddressFormatter()->format($contactDetails->getAddress());
            }
        }

        $context->offsetSet('licenceAddress', $licenceAddress);

        return $context;
    }
}
