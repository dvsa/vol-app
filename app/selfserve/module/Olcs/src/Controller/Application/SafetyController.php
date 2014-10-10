<?php

/**
 * Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;

/**
 * Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class SafetyController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Safety'
            )
        );
    }
}
