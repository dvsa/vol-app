<?php

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Common\Controller\Traits\Lva;
use Olcs\Controller\Application\AbstractApplicationController;

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
abstract class AbstractVariationController extends AbstractApplicationController
{
    use Lva\VariationControllerTrait;

    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'variation';

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
