<?php

/**
 * Controller Aware Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Lva\Interfaces;

use Laminas\Mvc\Controller\AbstractController;

/**
 * Controller Aware Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ControllerAwareInterface
{
    public function setController(AbstractController $controller);

    public function getController();
}
