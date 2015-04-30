<?php

/**
 * ReviveApplicationController.php
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractReviveApplicationController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Class ReviveApplicationController
 *
 * @package Olcs\Controller\Lva\Application
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class ReviveApplicationController extends AbstractReviveApplicationController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
