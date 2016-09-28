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

    const UNDERTAKINGS_KEY = 'undertakings';

    protected $lva = 'variation';
    protected $location = 'external';

    protected function getOverviewView($data, $sections, $form)
    {
        return new VariationOverview($data, $sections, $form);
    }

    /**
     * Is ready to submit
     *
     * @param array $sections                 variation sections
     * @param bool  $shouldIgnoreUndertakings should we ignore undertakings section
     *
     * @return bool
     */
    protected function isReadyToSubmit($sections, $shouldIgnoreUndertakings = false)
    {
        $updated = 0;
        foreach ($sections as $key => $section) {
            if ($shouldIgnoreUndertakings && $key === self::UNDERTAKINGS_KEY) {
                continue;
            }
            if ($section['status'] === RefData::VARIATION_STATUS_REQUIRES_ATTENTION) {
                return false;
            }
            if ($section['status'] === RefData::VARIATION_STATUS_UPDATED) {
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

        $sections = array_map(
            function ($value) {
                return ['status' => $value];
            },
            $sections
        );
        $sections[self::UNDERTAKINGS_KEY]['enabled'] = $this->isReadyToSubmit($sections, true);
        return $sections;
    }
}
