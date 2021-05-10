<?php

namespace Dvsa\Olcs\Application\Controller;

use Olcs\Controller\Lva\AbstractOverviewController;
use Dvsa\Olcs\Application\View\Model\ApplicationOverview;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

class OverviewController extends AbstractOverviewController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    protected function getOverviewView($data, $sections, $form)
    {
        return new ApplicationOverview($data, $sections, $form);
    }

    protected function isReadyToSubmit($sections)
    {
        foreach ($sections as $section) {
            if ($section['enabled'] && !$section['complete']) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     *
     * e.g. [ 'section_name' => ['enabled' => true, 'complete' => false] ]
     */
    protected function getSections($data)
    {
        return $this->setEnabledAndCompleteFlagOnSections(
            $data['sections'],
            $data['applicationCompletion']
        );
    }
}
