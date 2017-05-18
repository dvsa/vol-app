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
            CommonVariationControllerTrait::postSave insteadof ApplicationControllerTrait;
            CommonVariationControllerTrait::goToNextSection insteadof ApplicationControllerTrait;
        }

    /**
     * Complete section
     *
     * @param string $section Section
     * @param array  $prg     Prg
     *
     * @todo this logic is the same as CommonApplicationControllerTrait, this could potentially be re-used however I am
     *   not sure whether there would be any complications
     *
     * @return \Zend\Http\Response
     */
    protected function completeSection($section, $prg = [])
    {
        if ($this->isButtonPressed('saveAndContinue')) {
            return $this->goToNextSection($section);
        }

        return $this->goToOverviewAfterSave();
    }

    /**
     * Get variation sections
     *
     * @param array $data variation data
     *
     * @return array
     */
    protected function getVariationSections($data)
    {
        $completions = $data['variationCompletion'];

        $accessible = array_keys($data['sections']);

        $accessible = array_flip($accessible);
        $sections = array_intersect_key(
            array_merge(
                $accessible,
                $completions
            ),
            $accessible
        );

        $sections = array_map(
            function ($value) {
                return ['status' => $value];
            },
            $sections
        );
        return $sections;
    }
}
