<?php
namespace Olcs\Controller\Application;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;
use DateTime;

/**
 * Handles the overlay for person search
 *
 * @package    olcs
 * @subpackage submission
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */
class SubsidiaryController extends AbstractActionController
{
    /**
     * The action that shows the person search form and overlay page
     */
    public function searchAction()
    {
        $values = $this->params()->fromQuery();
        $sortColumn = $this->params()->fromQuery('column', 'column-companyname');
        $sortReversed = $this->params()->fromQuery('dir') == 'dn';
        $type = $this->params()->fromQuery('type', null);
        $list = $this->params()->fromQuery('list', null);
        $fieldgroup = $this->params()->fromQuery('fieldgroup', null);
        $ajax = $this->getRequest()->isXmlHttpRequest();

        $sortColumn = substr($sortColumn, strlen('column-'));

        $searchForm = new Form\Application\SubsidiarySearchForm('popup-');
        $searchForm->setData($values);
        $searchForm->setAttribute('action', '/application/search/subsidiary?' . http_build_query(array_filter(array(
            'type' => $type,
            'list' => $list,
            'fieldgroup' => $fieldgroup,
        ))));

        if ($ajax) {
            $this->layout('layout/popup');
        }


        $header = 'application-search-subsidiary-header';

        $view = new ViewModel(array(
            'header' => $header,
            'form' => $searchForm,
            'embedded' => !$ajax,
            'list' => $list,
        ));
        $view->setTemplate('olcs/application/subsidiary-search');

        if (!empty($values)) {
            $searchForm->isValid();

            $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
            $pageDetails = $paginator->getPageDetails($this);

            $result = $this->getListValues(
                $searchForm->getData(),
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
                    'primary-column column-companynumber',
                    'column-companyname',
                ),
                'headers' => array(
                    'companynumber' => array('sortable' => true, 'title' => 'companynumber'),
                    'companyname' => array('sortable' => true, 'title' => 'companyname'),
                    'licencestatus' => array('sortable' => false, 'title' => 'licence-number')
                ),
            );

            $dataList = $this->DataListPlugin();
            $dataList->setListWrapper('olcs/common/generic-list/genericListWrapperGenericPartials')
                ->setListPaging('olcs/common/generic-list/listPageBarQueryString')
                ->setListWrapperAttributes(array(
                    'data' => array_filter(array(
                        'id-list' => $list,
                        'mapped-fields' => $fieldgroup,
                    )),
                ))
                ->setTableHeaderParams($header)
                ->setAjaxUrl('/application/search/subsidiary');

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

    protected function getListValues(array $search, $sortColumn = null, $sortReversed = false, $limit = null, $offset = null)
    {
        $search = array_filter($search);
        unset($search['submit']);

        if (empty($search)) {
            return null;
        }

        if ( $sortColumn == "companyname" ) {
            $sortColumn="name";
        } else if ( $sortColumn == "companynumber" ) {
            $sortColumn="number";
        }

        $result = $this->service('Olcs\Organisation')->get(array_filter(array(
            'includeLicenceInfo' => 1,
            'sortColumn' => $sortColumn,
            'sortReversed' => $sortReversed,
            'offset' => $offset,
            'limit' => $limit,
            'search' => array_filter(array(
                'number'  => isset($search['companyNumber']) ? $search['companyNumber'] : null,
                'name'    => isset($search['companyName']) ? $search['companyName'] : null
            )),
        )));

        $thisId=0;
        if ($result) {
            foreach ($result['rows'] as $key => $row) {

                $escaper = new \Zend\Escaper\Escaper('utf-8');

                // Concatenate the licence data
                $licenceArray=Array();
                foreach($row['licences'] as $licenceData) {
                    array_push($licenceArray,$escaper->escapeHtml($licenceData['licenceNumber'])." ".$escaper->escapeHtml($licenceData['licenceStatus']));
                }
                $row['licence']['status']=implode("<br/>",$licenceArray);

                $result['rows'][$key] = array(
                    array(
                        'attributes' => array(
                            'data' => array('entity-id' => $row['organisationId']),
                        ),
                        'escape' => false,
                        'value' => $escaper->escapeHtml($row['registeredCompanyNumber'])
                    ),
                    array(
                        'escape' => false,
                        'value' => $escaper->escapeHtml($row['name'])
                    ),
                    array(
                        'escape' => false,
                        'value' => '<span class="column-licencestatus">' . $row['licence']['status'] . '</span>',
                    ),
                );
            }
        }

        return $result ? $result : false;
    }
}
