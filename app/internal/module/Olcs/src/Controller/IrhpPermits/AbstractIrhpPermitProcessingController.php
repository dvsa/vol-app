<?php

namespace Olcs\Controller\IrhpPermits;

use Olcs\Controller\Traits\ProcessingControllerTrait;

/**
 * Abstract Irhp Permit Processing Controller
 */
abstract class AbstractIrhpPermitProcessingController extends AbstractIrhpPermitController
{
    use ProcessingControllerTrait;
}
