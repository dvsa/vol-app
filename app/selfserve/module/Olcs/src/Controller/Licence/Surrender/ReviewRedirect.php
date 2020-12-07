<?php

namespace Olcs\Controller\Licence\Surrender;

use Laminas\Mvc\MvcEvent;

trait ReviewRedirect
{
    /**
     * @param MvcEvent $e
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->data['fromReview'] = $this->params()->fromRoute('review') ?? false;
        parent::onDispatch($e);
    }
}
