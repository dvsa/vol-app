<?php

namespace Olcs\Controller\Variation;

use Common\Controller\Lva\Schedule41Controller;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

class VariationSchedule41Controller extends Schedule41Controller implements ApplicationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected $section = 'operating_centres';
}
