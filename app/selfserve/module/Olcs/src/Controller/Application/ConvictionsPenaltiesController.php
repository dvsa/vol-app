<?php

/**
 * Convictions Penalties Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

/**
 * Convictions Penalties Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionsPenaltiesController extends AbstractApplicationController
{
    public function indexAction()
    {
        return $this->render('conviction_penalties');
    }
}
