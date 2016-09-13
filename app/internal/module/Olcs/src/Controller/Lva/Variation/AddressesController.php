<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\AbstractAddressesController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Internal Variation Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesController extends AbstractAddressesController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
