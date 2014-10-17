<?php

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Lva\Traits\CommonVariationControllerTrait;

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
trait VariationControllerTrait
{
    use ApplicationControllerTrait,
        CommonVariationControllerTrait {
            CommonVariationControllerTrait::preDispatch insteadof ApplicationControllerTrait;
        }

    /**
     * Complete section
     *
     * @param string $section
     * @return \Zend\Http\Response
     */
    protected function completeSection($section)
    {
        $this->addSectionUpdatedMessage($section);

        if ($this->isButtonPressed('saveAndContinue')) {
            return $this->goToNextSection($section);
        }

        return $this->goToOverviewAfterSave();
    }
}
