<?php

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

use SelfServe\Controller\Application\ApplicationController;

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TypeOfLicenceController extends ApplicationController
{
    /**
     * Holds the data
     *
     * @var array
     */
    protected $data;

    /**
     * this controller defines an over-arching form comprised
     * of the fieldsets in this array; depending on the user's
     * JS support we'll either show them as one form or as separate
     * steps
     */
    protected $fieldsets = [
        'operator-location',
        'operator-type',
        'licence-type'
    ];

    /**
     * each child class is expected to set this to one of
     * the above options. If it's not set, we assume we're
     * inside this index controller
     */
    protected $fieldset = null;

    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = 'Licence';

    /**
     * Opt-in to some inline JavaScript which will
     * be automatically inlined in the view
     */
    protected $inlineScripts = ['type-of-licence'];

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        if ($this->fieldset === null) {
            return $this->goToFirstSubSection();
        }
        return $this->renderSection();
    }

    /**
     * always load the same form, regardless of
     * sub-section. Each controller then selectively
     * chooses a fieldset to display, or displays all if we have JS
     *
     * @return string
     */
    protected function getFormName()
    {
        return 'application_type-of-licence';
    }

    /**
     * check whether the form submission is
     * partial (e.g non JS; just validate a single
     * fieldset)
     *
     * @return bool
     */
    protected function isPartialSubmission()
    {
        $request = $this->getRequest();
        return ($request->isPost() && $request->getPost('js-submit') === null);
    }

    /**
     * Cache the data for the form
     *
     * @param int $id
     * @return array
     */
    protected function loadData($id)
    {
        if (empty($this->data)) {

            $this->data = $this->getLicenceData();
        }

        return $this->data;
    }

    /**
     * Load data from id
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        $data = $this->loadData($id);

        foreach ($this->fieldsets as $fieldset) {
            $formData[$fieldset] = $data;
        }

        return $formData;
    }

    /**
     * bear in mind this method is called both on a GET (e.g. before
     * rendering a form) and POST (e.g. before validating it)
     *
     * @param Form $form
     *
     * @return Form
     */
    protected function alterForm($form)
    {
        // this looks a bit counter intuitive; but by removing the class
        // we ensure the current fieldset is shown
        $form->get($this->fieldset)->removeAttribute('class');

        // late static binding ensures the correct child method (if declared)
        // is invoked. It's a static because it's re-used by the review & declarations
        // page when $this obviously isn't the right controller
        $form = static::makeFormAlterations(
            $form,
            $this,
            $this->getFormAlterationOptions()
        );

        if ($this->isPartialSubmission()) {
            // if this is a single-step (e.g non JS) submission, make sure
            // we don't try and validate anything other than the current fieldset

            $remove = array_diff($this->fieldsets, [$this->fieldset]);
            foreach ($remove as $fieldset) {
                $form->remove($fieldset);
            }
        }

        return $form;
    }

    /**
     * make sure we map our data to save from all three fieldsets
     *
     * @return array
     */
    protected function getDataMap()
    {
        return [
            'main' => [
                'mapFrom' => $this->fieldsets
            ]
        ];
    }

    /**
     * Save data
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        if (isset($data['niFlag']) && $data['niFlag'] == 1) {
            $data['goodsOrPsv'] = self::GOODS_OR_PSV_GOODS_VEHICLE;
        }

        parent::save($data);
    }

    /**
     * Make form alterations
     *
     * This method enables the summary to apply the same form alterations. We declare
     * it here as an identity function but child classes can optionally override it
     *
     * @param Form $form
     * @param Controller $context
     * @param array $options
     *
     * @return $form
     */
    public static function makeFormAlterations($form, $context, $options = array())
    {
        return $form;
    }

    /**
     * child classes should override this if they want to pass any options through
     * to the form alteration method
     *
     * @return array
     */
    protected function getFormAlterationOptions()
    {
        return [];
    }

    /**
     * data map pre-processor when saving
     *
     * @param array $oldData
     * @param array $map
     * @param string $section
     *
     * @return array
     */
    public function processDataMapForSave($oldData, $map = array(), $section = 'main')
    {
        // we have to make sure each fieldset at least *exists*, otherwise
        // the default processDataMapSave behavour will exit too early and not
        // process any remaining fieldsets
        // this is particularly relevant when the operator type fieldset is
        // removed due to operator location being NI; the licence type otherwise
        // wouldn't be processed due to the early return
        foreach ($this->fieldsets as $fieldset) {
            if (!isset($oldData[$fieldset])) {
                $oldData[$fieldset] = [];
            }
        }
        return parent::processDataMapForSave($oldData, $map, $section);
    }
}
