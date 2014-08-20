<?php

/**
 * Case Prohibition Defect Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Olcs\Controller\Traits\DeleteActionTrait;

use Common\Controller\CrudInterface;

/**
 * Case Prohibition Defect Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseProhibitionDefectController extends CaseController implements CrudInterface
{
    use DeleteActionTrait;

    public function addAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        $prohibitionId = $this->fromRoute('id');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_prohibition/defect' => array('licence' => $licenceId, 'case' => $caseId, 'id' => $prohibitionId)
            )
        );

        $form = $this->generateFormWithData(
            'prohibition-defect',
            'processAddProhibitionDefect',
            array(
                'case_id' => $caseId,
                'prohibition' => $prohibitionId
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add prohibition defect',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('prohibition/defect');

        return $view;
    }

    public function editAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        $prohibitionId = $this->fromRoute('id');
        $defectId = $this->fromRoute('defect');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_prohibition/defect' => array('licence' => $licenceId, 'case' => $caseId, 'id' => $prohibitionId, 'defect' => $defectId)
            )
        );

        $details = $this->makeRestCall(
            'ProhibitionDefect',
            'GET',
            array(
                'id' => $defectId,
                'bundle' => json_encode($this->getBundle())
            )
        );

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForForm($details);

        $form = $this->generateFormWithData(
            'prohibition-defect',
            'processEditProhibitionDefect',
            $data
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit prohibition defect',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('prohibition/defect');

        return $view;
    }

    /**
     * @param $data
     * @return mixed|\Zend\Http\Response
     */
    public function processAddProhibitionDefect($data)
    {


        $result = $this->processAdd($data['main'], 'ProhibitionDefect');

        if (isset($result['id'])) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute('case_prohibition', ['action' => 'edit'], [], true);
    }

    /**
     * @param $data
     * @return mixed|\Zend\Http\Response
     */
    public function processEditProhibitionDefect($data)
    {
        $result = $this->processEdit($data, 'ProhibitionDefect');

        if (empty($result)) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute('case_prohibition', ['action' => 'edit'], [], true);
    }

    /**
     * Formats data for use in the form
     *
     * @param array $results
     * @return array
     */
    private function formatDataForForm($results)
    {
        $formatted = array();

        $formatted['main']['defectType'] = $results['defectType'];
        $formatted['main']['notes'] = $results['notes'];

        $formatted['id'] = $results['id'];
        $formatted['case_id'] = $results['prohibition']['case']['id'];
        $formatted['prohibition'] = $results['prohibition']['id'];
        $formatted['version'] = $results['version'];

        return $formatted;
    }

    public function getBundle()
    {
        return array(
            'children' => array(
                'prohibition' => array(
                    'properties' => array(
                        'id'
                    ),
                    'children' => array(
                        'case' => array(
                            'properties' => array(
                                'id'
                            )
                        )
                    )
                )
            )
        );
    }

    public function getDeleteServiceName()
    {
        return 'ProhibitionDefect';
    }
}