<?php

/**
 * Search Controller for creating a new application.
 *
 * OLCS-441
 *
 * @package		olcs
 * @subpackage	application
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk> and
 *                      J Rowbottom <joel.rowbottom@valtech.co.uk>
 */

namespace Olcs\Controller\Application;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;

class SearchController extends AbstractActionController
{
    protected $editTemplate = 'olcs/application/overlays/operatorEdit';

    /*
     * Generates a operator list using the DataListPlugin
     */    
    public function operatorAction()
    {
        $this->layout('layout/popup');

        $entityType = $this->getEvent()->getRouteMatch()->getParam('entityType');
        $operatorName = $this->getEvent()->getRouteMatch()->getParam('operatorName');
        $sortColumn = $this->params()->fromQuery('column', 'column-licenceNumber');
        $sortReversed = $this->params()->fromQuery('dir') == 'dn';

        $sortColumn = substr($sortColumn, strlen('column-'));

        $applicationSearchForm = new Form\Application\SearchForm();

        // set the value of the form to whatever is being searched for
        $applicationSearchForm->get('popupOperatorName')->setValue($operatorName);
        $applicationSearchForm->get('popupOperatorType')->setValue($entityType);

        $view = new ViewModel(array('applicationSearchForm' => $applicationSearchForm));
        $view->setTemplate('olcs/application/search');

        if (!empty($operatorName)) {
            $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
            $pageDetails = $paginator->getPageDetails($this);

            $result = $this->getListValues(
                array('operatorName' => $operatorName, 'entityType' => $entityType),
                $sortColumn,
                $sortReversed,
                $pageDetails['limit'],
                $pageDetails['offset']
            );
        }

        if (isset($result)) {
            $header = array(
                'sortColumn' => $sortColumn,
                'sortReversed' => $sortReversed,
                'columnClasses' => array(
                    'primary-column column-name',
                    '',
                ),
                'headers' => array(
                    'operatorName' => array('sortable' => true, 'title' => 'operator-name'),
                    'licenceNumber'  => array('sortable' => true, 'title' => 'licence-number-status'),
                ),
            );

            $dataList = $this->DataListPlugin();
            $dataList->setListWrapper('olcs/common/generic-list/genericListWrapperGenericPartials')
                ->setListPaging('olcs/common/generic-list/listPageBarQueryString')
                ->setListWrapperAttributes(array(
                    'data' => array(
                        'mapped-fields' => '#operatorNameContainer',
                        'fields-prefix' => 'operator',
                    ),
                ))
                ->setTableHeaderParams($header)
                ->setAjaxUrl('/application/search/operator/' . urlencode($operatorName).'/page/1/' . $pageDetails['limit']);

            $paginated = $paginator->createPaginator(
                $this,
                array(
                    'listCount' => $result['count'],
                    'listData' => $result['rows'],
                ),
                $pageDetails['page'],
                $this->getEvent()->getRouteMatch()->getMatchedRouteName()
            );

            $result['columnClasses'] = $header['columnClasses'];

            $listView = $dataList->createList($result, $paginated);
            $view->addChild($listView, 'listView');
        }

        return $view;
    }
    
    /*
     * Gets the data for the list
     */
    protected function getListValues(array $search, $sortColumn = null, $sortReversed = false, $limit = null, $offset = null)
    {
        $result = $this->service('Olcs\Lookup')->get(array_filter(array(
            'type' => 'licence',
            'sortColumn' => $sortColumn,
            'sortReversed' => $sortReversed,
            'offset' => $offset,
            'limit' => $limit,
            'search' => array_filter(array(
                'operatorName' => isset($search['operatorName']) ? $search['operatorName'] : null,
                'entityType' => isset($search['entityType']) ? $search['entityType'] : null,
            )),
        )));

        if ($result) {
            foreach ($result['rows'] as $key => $row) {
                $result['rows'][$key] = array(
                    array(
                        'attributes' => array(
                            'data' => array(
                                'entity-id' => $row['operator']['operatorId'],
                                'entity-version' => $row['operator']['version'],
                            ),
                        ),
                        'value' => $row['operator']['operatorName'],
                    ),
                    (isset($row['licenceNumber']) ? $row['licenceNumber'] : '-') . "\n" . $row['licenceStatus'],
                );
            }
        }

        return $result ? $result : false;
   }

   public function operatorEditAction()
   {
        $applicationOperatorEditForm = new Form\Application\OperatorEditForm();

        $this->layout('layout/popup');
        $editView = new ViewModel(array("applicationOperatorEditForm"=>$applicationOperatorEditForm));
        $editView->setTemplate($this->editTemplate);

        return $editView;
   }


}
