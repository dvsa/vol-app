<?php

/**
 * Variation Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Common\Controller\Traits\Lva;

/**
 * Variation Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesController extends AbstractVariationController
{
    use Lva\AddressesTrait;
}
