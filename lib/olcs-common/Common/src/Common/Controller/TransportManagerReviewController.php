<?php

/**
 * Transport Manager Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller;

use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\Review;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Transport Manager Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerReviewController extends LaminasAbstractActionController
{
    #[\Override]
    public function indexAction()
    {
        $response = $this->handleQuery(Review::create(['id' => $this->params('id')]));
        $data = $response->getResult();

        $view = new ViewModel(['content' => $data['markup']]);

        $view->setTerminal(true);
        $view->setTemplate('layout/blank');

        return $view;
    }
}
