<?php

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Olcs\Controller\Application\AbstractApplicationController;

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
abstract class AbstractVariationController extends AbstractApplicationController
{
    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'variation';
}
