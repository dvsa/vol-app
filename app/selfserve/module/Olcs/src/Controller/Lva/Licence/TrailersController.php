<?php

/**
 * TrailersController.php
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Zend\Mvc\MvcEvent;

/**
 * Class TrailersController
 *
 * {@inheritdoc}
 *
 * @package Olcs\Controller\Lva\Licence
 *
 * @author  Josh Curtis <josh.curtis@valtech.co.uk>
 */
class TrailersController extends Lva\AbstractTrailersController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * Prevent access to NI:w
     *
     * @param MvcEvent $e
     *
     * @return array|null|\Zend\Http\Response
     */
    public function onDispatch(MvcEvent $e)
    {
        return $this->fetchDataForLva()['niFlag'] === 'Y'
            ? $this->notFoundAction()
            : parent::onDispatch($e);
    }
}
