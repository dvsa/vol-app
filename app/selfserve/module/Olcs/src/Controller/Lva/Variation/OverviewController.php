<?php

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractOverviewController;
use Olcs\View\Model\Variation\VariationOverview;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractOverviewController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';

    protected function getOverviewView($data, $sections, $form)
    {
        return new VariationOverview($data, $sections, $form);
    }

    protected function isApplicationComplete($sections)
    {
        // @TODO
        return true;
    }

    protected function getSections($data)
    {
        return $this->getAccessibleSections();
    }
}
