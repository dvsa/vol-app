<?php

/**
 * Guides Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Guides Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GuidesController extends AbstractActionController
{
    const GUIDE_ADV_OC_GB = 'advertising-your-operating-centre-gb';
    const GUIDE_ADV_OC_NI = 'advertising-your-operating-centre-ni';

    protected $availableGuides = [
        self::GUIDE_ADV_OC_GB,
        self::GUIDE_ADV_OC_NI
    ];

    public function indexAction()
    {
        $guide = $this->params('guide');

        if (!in_array($guide, $this->availableGuides)) {
            return $this->notFoundAction();
        }

        $view = new ViewModel(['guide' => $guide]);
        $view->setTemplate('pages/guides');

        return $view;
    }
}
