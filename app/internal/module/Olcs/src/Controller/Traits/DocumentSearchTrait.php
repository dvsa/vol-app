<?php

namespace Olcs\Controller\Traits;

use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Zend\Form\Element\Select;

/**
 * Document Search Trait
 */
trait DocumentSearchTrait
{
    /**
     * Get Document Table Name
     *
     * @return string
     */
    protected abstract function getDocumentTableName();

    /**
     * Inspect the request to see if we have any filters set, and if necessary, filter them down to a valid subset
     *
     * @param array $extra Filters data
     *
     * @return array
     */
    protected function mapDocumentFilters($extra = [])
    {
        $defaults = [
            'sort' => 'issuedDate',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 10,
            'showDocs' => FilterOptions::SHOW_ALL,
        ];

        $filters = array_merge(
            $defaults,
            $extra,
            $this->getRequest()->getQuery()->toArray()
        );

        if (isset($filters['isExternal'])) {
            if ($filters['isExternal'] === 'external') {
                $filters['isExternal'] = 'Y';
            } elseif ($filters['isExternal'] === 'internal') {
                $filters['isExternal'] = 'N';
            } else {
                unset($filters['isExternal']);
            }
        }

        // nuke any empty values
        return array_filter(
            $filters,
            function ($v) {
                return $v === false || !empty($v);
            }
        );
    }

    /**
     * Create filter form
     *
     * @param array $filters Filters data
     *
     * @return \Zend\Form\FormInterface
     */
    protected function getDocumentForm($filters = [])
    {
        /** @var \Zend\Form\FormInterface $form */
        $form = $this->getForm('DocumentsHome');
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());

        $category = (isset($filters['category'])) ? (int) $filters['category'] : null;

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $selects = [
            'category' => $this->getListDataCategoryDocs('All'),
            'documentSubCategory' => $this->getListDataSubCategoryDocs($category, 'All'),
        ];

        // insert relevant data into the corresponding form inputs
        foreach ($selects as $name => $options) {
            $form->get($name)->setValueOptions($options);
        }

        //  show document field
        /** @var Select $option */
        $option = $form->get('showDocs');
        $option->setValueOptions(
            [
                FilterOptions::SHOW_ALL => 'documents.filter.option.all-docs',
            ]
        );

        // setting $this->enableCsrf = false won't sort this; we never POST
        $form->remove('csrf');

        $form->setData($filters);

        return $form;
    }

    /**
     * Create table and populate with data from Api
     *
     * @param array $filters Filters data
     *
     * @return \Common\Service\Table\TableBuilder
     * @throws \Exception
     */
    protected function getDocumentsTable($filters = [])
    {
        if (isset($filters['documentSubCategory'])) {
            // query requires array of subcategories
            $filters['documentSubCategory'] = [$filters['documentSubCategory']];
        }

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery(DocumentList::create($filters));
        if (!$response->isOk()) {
            throw new \Exception('Error retrieving document list');
        }

        $documents = $response->getResult();

        $filters['query'] = $this->getRequest()->getQuery();

        $table = $this->getTable($this->getDocumentTableName(), $documents, $filters);
        $this->updateTableActionWithQuery($table);

        return $table;
    }

    /**
     * Update table action with query
     *
     * @param \Common\Service\Table\TableBuilder $table Table
     *
     * @return void
     */
    protected function updateTableActionWithQuery($table)
    {
        $query = $this->getRequest()->getUri()->getQuery();
        if ($query) {
            $action = $table->getVariable('action') . '?' . $query;
            $table->setVariable('action', $action);
        }
    }

    /**
     * Add/Remove Select options
     *
     * @param Select $el      Target element
     * @param array  $options Add/remove options (for remove value should be null)
     *
     * @return void
     */
    protected function updateSelectValueOptions(Select $el, array $options = [])
    {
        $el->setValueOptions(
            array_filter(
                $options + $el->getValueOptions(),
                function ($arg) {
                    return $arg !== null;
                }
            )
        );
    }
}
