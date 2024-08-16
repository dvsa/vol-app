<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\PiHearing;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\VenueBundle;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareTrait;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;

/**
 * Class Venue
 * @package Dvsa\Olcs\Api\Service\Publication\Context\PiHearing
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Venue extends AbstractContext implements AddressFormatterAwareInterface
{
    use AddressFormatterAwareTrait;

    private static $bundle = [];

    /**
     * Provide
     *
     * @param PublicationLink $publication publication
     * @param \ArrayObject    $context     context
     *
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        if ($context->offsetGet('venue')) {
            $query = VenueBundle::create(['id' => $context->offsetGet('venue'), 'bundle' => self::$bundle]);

            /**
             * @var VenueEntity $venue
             */
            $venue = $this->handleQuery($query)->serialize();
            $address = $this->getAddressFromVenue($venue);
            $venueDetails = $venue['name'] . ', ' . $this->getAddressFormatter()->format($address);

            $context->offsetSet('venueOther', $venueDetails);
        }

        return $context;
    }

    /**
     * Get address from venue
     *
     * @param array $venue venue
     *
     * @return Address
     */
    protected function getAddressFromVenue($venue)
    {
        $address = new Address();
        $address->setAddressLine1($venue['address']['addressLine1']);
        $address->setAddressLine2($venue['address']['addressLine2']);
        $address->setAddressLine3($venue['address']['addressLine3']);
        $address->setAddressLine4($venue['address']['addressLine4']);
        $address->setTown($venue['address']['town']);
        $address->setAddressLine1($venue['address']['postcode']);
        return $address;
    }
}
