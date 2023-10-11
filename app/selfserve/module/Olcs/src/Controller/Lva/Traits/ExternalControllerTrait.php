<?php

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Traits;

use Common\RefData;
use Common\View\Model\Section;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ExternalControllerTrait
{
    /**
     * Redirect back to overview
     *
     * @param int $lvaId Lva id
     *
     * @return \Laminas\Http\Response
     */
    protected function handleCancelRedirect($lvaId)
    {
        return $this->goToOverview($lvaId);
    }

    /**
     * Get current user
     *
     * @return array
     */
    protected function getCurrentUser()
    {
        // get user data from Controller Plugin
        return $this->currentUser()->getUserData();
    }

    /**
     * Get current organisation
     *
     * @NOTE at the moment this will just return the users first organisation,
     * eventually the user will be able to select which organisation they are managing
     *
     * @return array
     */
    protected function getCurrentOrganisation()
    {
        $data = $this->getCurrentUser();
        return $data['organisationUsers'][0]['organisation'] ?? null;
    }

    /**
     * Get current organisation ID only
     *
     * @return int|null
     */
    protected function getCurrentOrganisationId()
    {
        $organisation = $this->getCurrentOrganisation();

        return (isset($organisation['id'])) ? $organisation['id'] : null;
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId Lva id
     *
     * @return null|\Laminas\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if ($this->lva === 'application' || $this->lva === 'variation') {
            $summaryRouteName = 'lva-' . $this->lva . '/summary';
            $submissionRouteName = 'lva-' . $this->lva . '/submission-summary';
            $allowedRoutes = [
                $summaryRouteName,
                $submissionRouteName,
                'lva-' . $this->lva . '/transport_manager_details',
                'lva-' . $this->lva . '/transport_manager_details/action',
                'lva-' . $this->lva . '/withdraw',
                'lva-' . $this->lva . '/upload-evidence',
            ];
            $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

            if (!in_array($matchedRouteName, $allowedRoutes) && !$this->checkAppStatus($lvaId)) {
                return $this->redirect()->toRoute($submissionRouteName, ['application' => $lvaId]);
            }
        }

        return parent::checkForRedirect($lvaId);
    }

    /**
     * Render the section
     *
     * @param string $titleSuffix Title suffix
     * @param Form   $form        Form
     * @param array  $variables   Variables
     *
     * @return ViewModel
     */
    protected function render($titleSuffix, Form $form = null, $variables = array())
    {
        $this->attachCurrentMessages();

        if ($titleSuffix instanceof ViewModel) {
            return $titleSuffix;
        }

        $params = array_merge(
            array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form),
            $variables
        );
        if ($this->lva === 'licence') {
            // query is already cached
            $dto = LicenceQry::create(['id' => $this->getLicenceId()]);
            $response = $this->handleQuery($dto);
            $data = $response->getResult();
            $params['startDate'] = $data['inForceDate'];
            $params['renewalDate'] = $data['expiryDate'];
            $params['status'] = $data['status']['id'] ?? null;
            $params['licNo'] = $data['licNo'];
            $params['lva'] = 'licence';
            $params['title'] = $this->getSectionTitle($titleSuffix, $data);
        }

        return $this->renderView(new Section($params));
    }

    /**
     * Render view
     *
     * @param Section $section Section
     *
     * @return ViewModel
     */
    protected function renderView($section)
    {
        $template = $this->getRequest()->isXmlHttpRequest() ? 'ajax' : 'layout';

        $base = new ViewModel();
        $base->setTemplate('layout/' . $template)
            ->setTerminal(true)
            ->addChild($section, 'content');

        return $base;
    }

    /**
     * get section title
     *
     * @param string $sectionName
     * @param array $data
     *
     * @return string
     */
    protected function getSectionTitle(string $sectionName, array $data): string
    {
        // default section title
        $sectionTitle = 'lva.section.title.' . $sectionName;

        switch ($sectionName) {
            case 'people':
                // change the section name based on org type
                $orgType = isset($data['licence']['organisation']['type']['id']) ?
                    $data['licence']['organisation']['type']['id'] : $data['organisation']['type']['id'];

                $sectionTitle .= '.' . $orgType;
                break;
            case 'operating_centres':
                // change the section name if it is LGV only
                if (RefData::APP_VEHICLE_TYPE_LGV === $data['vehicleType']['id']) {
                    $sectionTitle .= '.lgv';

                    // overwrite page title too
                    $this->placeholder()->setPlaceholder('pageTitle', $sectionTitle);
                }
                break;
        }

        return $sectionTitle;
    }
}
