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
use Common\Service\Entity\VariationCompletionEntityService as Completion;

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
        foreach ($sections as $section) {
            if ($section['status'] == Completion::STATUS_REQUIRES_ATTENTION) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     *
     * e.g. [ 'section_name' => ['status' => 2] ]
     */
    protected function getSections($data)
    {
        $completions = $this->getServiceLocator()->get('Processing\VariationSection')
            ->setApplicationId($data['id'])
            ->getSectionCompletion();
        $accessible = $this->getAccessibleSections();

        $sections = array_intersect_key($completions, array_flip($accessible));

        return array_map(
            function ($value) {
                return ['status' => $value];
            },
            $sections
        );
    }
}
