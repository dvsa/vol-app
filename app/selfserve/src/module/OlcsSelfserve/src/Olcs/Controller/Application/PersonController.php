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
class PersonController extends AbstractActionController
{

    /**
     * The action that shows the person search form and overlay page
     */
    public function searchAction()
    {
        $values = $this->params()->fromQuery();
        $sortColumn = $this->params()->fromQuery('column', 'column-name');
        $sortReversed = $this->params()->fromQuery('dir') == 'dn';
        $type = $this->params()->fromQuery('type', null);
        $list = $this->params()->fromQuery('list', null);
        $fieldgroup = $this->params()->fromQuery('fieldgroup', null);
        $ajax = $this->getRequest()->isXmlHttpRequest();

        $sortColumn = substr($sortColumn, strlen('column-'));

        $searchForm = new Form\Application\PersonSearchForm('popup-' . $type . '-');
        $searchForm->setData($values);
        $searchForm->setAttribute('action', '/application/search/person?' . http_build_query(array_filter(array(
            'type' => $type,
            'list' => $list,
            'fieldgroup' => $fieldgroup,
        ))));

        if ($ajax) {
            $this->layout('layout/popup');
        }

        switch ($type) {
            case 'application-director':
                $header = 'application-search-person-header-director';
                break;
            case 'application-partner':
                $header = 'application-search-person-header-partner';
                break;
            case 'application-sole-trader':
                $header = 'application-add-new-sole-trader';
                break;
            default:
                $header = 'application-search-person-header-person';
        }

        $view = new ViewModel(array(
            'header' => $header,
            'form' => $searchForm,
            'embedded' => !$ajax,
            'list' => $list,
        ));
        $view->setTemplate('olcs/application/person-search');

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
                    'primary-column column-name',
                    'column-dob',
                    '',
                    'columntype-numeric',
                    'column-disqualified columntype-bool',
                ),
                'headers' => array(
                    'name' => array('sortable' => true, 'title' => 'name'),
                    'dob' => array('sortable' => true, 'title' => 'dob'),
                    'licence'  => array('sortable' => true, 'title' => 'licence-number-status'),
                    'cases'  => array('sortable' => true, 'title' => 'cases'),
                    'disqualification'  => array('sortable' => true, 'title' => 'disqualification-column'),
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
                ->setAjaxUrl('/application/search/person');

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

        $result = $this->service('Olcs\Lookup')->get(array_filter(array(
            'type' => 'person-licence',
            'sortColumn' => $sortColumn,
            'sortReversed' => $sortReversed,
            'offset' => $offset,
            'limit' => $limit,
            'search' => array_filter(array(
                'firstName'   => isset($search['personFirstName']) ? $search['personFirstName'] : null,
                'lastName'    => isset($search['personSurname']) ? $search['personSurname'] : null,
                'dateOfBirth' => isset($search['personDob']) ? $search['personDob'] : null,
            )),
        )));

        if ($result) {
            foreach ($result['rows'] as $key => $row) {
                if (empty($row['licence'])) {
                    $licence = '';
                } else {
                    $licence = $row['licence']['licenceNumber'] . "\n" . $row['licence']['licenceStatus'];
                }

                $escaper = new \Zend\Escaper\Escaper('utf-8');

                $dob = new DateTime($row['dob']);

                $result['rows'][$key] = array(
                    array(
                        'attributes' => array(
                            'data' => array('entity-id' => $row['personId']),
                        ),
                        'escape' => false,
                        'value' => '<span class="column-firstname">' . $escaper->escapeHtml($row['firstName']) . '</span> ' .
                            '<span class="column-surname">' . $escaper->escapeHtml($row['lastName']) . '</span>',
                    ),
                    array(
                        'escape' => false,
                        'value' => '<span class="column-dob-day">' . $dob->format('d') . '</span>-' .
                            '<span class="column-dob-month">' . $dob->format('m') . '</span>-' .
                            '<span class="column-dob-year">' . $dob->format('Y') . '</span>',
                    ),
                    $licence,
                    '0',
                    $row['disqualified'] ? 'Y' : 'N',
                );
            }
        }

        return $result ? $result : false;
    }
}
