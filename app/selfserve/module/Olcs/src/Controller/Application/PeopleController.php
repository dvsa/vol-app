<?php

/**
 * People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;

/**
 * People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PeopleController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'People' // @NOTE: context-sensitive, e.g. 'Directors'
            )
        );
    }
}
