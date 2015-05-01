<?php

/**
 * ReviveApplicationController.php
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractReviveApplicationController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Class ReviveApplicationController
 *
 * @package Olcs\Controller\Lva\Variation
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class ReviveApplicationController extends AbstractReviveApplicationController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
