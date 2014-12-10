<?php

namespace Olcs\View\Helper;

use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\Exception;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionOverview extends AbstractHelper
{
    const DEFAULT_VIEW = '/view-new/partials/submission-details';

    /**
     * Type map to views
     *
     * @var array
     */
    protected $typeViewMap = array(
        'case-summary'   => '/view-new/partials/submission-summary',
        'case-outline'   => '/view-new/partials/submission-outline',
        'penalties'      => '/view-new/partials/submission-penalties',
        'conviction-fpn-offence-history'   => '/view-new/partials/submission-table',
    );

    /**
     * Renders the data for a SubmissionSection details
     *
     * @param  String $submissionSection
     * @param Array $data
     * @return string
     */
    public function __invoke($submissionSection = '', $data = array())
    {

        if (empty($submissionSection)) {
            return '';
        }

        return $this->render($submissionSection, $data);
    }

    public function render($submissionSection, $data)
    {
        $data['data']['overview'] = isset($data['data']['overview']) ? $data['data']['overview'] : [];
        $viewTemplate = isset($this->typeViewMap[$submissionSection]) ?
            $this->typeViewMap[$submissionSection] : self::DEFAULT_VIEW;
        return $this->getView()->render($viewTemplate, ['data' => $data]);
    }
}
