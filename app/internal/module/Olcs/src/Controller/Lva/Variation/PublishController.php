<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Variation PublishController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishController extends \Olcs\Controller\Lva\AbstractPublishController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
