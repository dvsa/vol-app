<?php

namespace Olcs\Controller\IrhpPermits;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Abstract Irhp Permit Controller
 */
abstract class AbstractIrhpPermitController extends AbstractController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider
{
}
