<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Abstract Irhp Permit Controller
 */
abstract class AbstractIrhpPermitController extends AbstractController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider,
    ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::BACKEND_PERMITS
        ],
    ];
}
