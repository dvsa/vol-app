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
use Common\RefData;

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

    protected function isReadyToSubmit($sections)
    {
        $updated = 0;
        foreach ($sections as $section) {
            if ($section['status'] == RefData::VARIATION_STATUS_REQUIRES_ATTENTION) {
                return false;
            }
            if ($section['status'] == RefData::VARIATION_STATUS_UPDATED) {
                $updated++;
            }
        }
        return ($updated > 0);
    }

    /**
     * @return array
     *
     * e.g. [ 'section_name' => ['status' => 2] ]
     */
    protected function getSections($data)
    {
        $completions = $data['variationCompletion'];

        $accessible = array_keys($data['sections']);

        // @todo there must be an easier way to do this, but it's late on a friday and my brain hurts
        $accessible = array_flip($accessible);
        $sections = array_intersect_key(
            array_merge(
                $accessible,
                $completions
            ),
            $accessible
        );

        return array_map(
            function ($value) {
                return ['status' => $value];
            },
            $sections
        );
    }
}
