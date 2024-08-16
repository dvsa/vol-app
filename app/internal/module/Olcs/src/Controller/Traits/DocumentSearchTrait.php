<?php

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\Form\Element\Select;

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
    abstract protected function getDocumentTableName();

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
            fn($v) => $v === false || !empty($v)
        );
    }

    /**
     * Create filter form
     *
     * @param array $filters Filters data
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getDocumentForm($filters = [])
    {
        /**
         * @var \Common\Service\Helper\FormHelperService $formHelper
         */
        $formHelper = $this->formHelper;

        $form = $formHelper->createForm('DocumentsHome', false);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        $category = (isset($filters['category'])) ? (int) $filters['category'] : null;

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $this->docSubCategoryDataService
            ->setCategory($category);

        //  show document field
        /**
 * @var Select $option
*/
        $option = $form->get('showDocs');
        $option->setValueOptions(
            [
                FilterOptions::SHOW_ALL => 'documents.filter.option.all-docs',
            ]
        );

        // Populate the "Format" filter select element with values
        /**
 * @var \Laminas\Form\Element\Select $formatSelectElement
*/
        $formatSelectElement = $form->get('format');
        $formatSelectElement->setValueOptions(array_merge(['' => 'All'], $this->getDocumentsExtensionList($filters)));

        //  set data
        $form->setData($filters);

        return $form;
    }

    /**
     * Create table and populate with data from Api
     *
     * @param array $filters Filters data
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getDocumentsTable($filters = [])
    {
        $response = $this->getDocumentList($filters);
        $documents = $response->getResult();

        $filters['query'] = $this->getRequest()->getQuery();

        $table = $this->getTable($this->getDocumentTableName(), $documents, $filters);
        $this->updateTableActionWithQuery($table);

        return $table;
    }

    /**
     * Get a the list of used file extensions in the document list
     *
     * @param array $filters Filters
     *
     * @return array Eg ['RTF' => 'RTF']
     */
    protected function getDocumentsExtensionList(array $filters = [])
    {
        $response = $this->getDocumentList($filters);
        $result = $response->getResult();
        if (!isset($result['extra']['extensionList'])) {
            return [];
        }

        // Use array_combine to make the key = $value, Eg 'RTF' => 'RTF'
        return array_combine($result['extra']['extensionList'], $result['extra']['extensionList']);
    }

    /**
     * Get the Document List
     *
     * @param array $filters Filters
     *
     * @return \Common\Service\Cqrs\Response
     * @throws \Exception
     */
    private function getDocumentList($filters)
    {
        if (isset($filters['documentSubCategory'])) {
            // query requires array of subcategories
            $filters['documentSubCategory'] = [$filters['documentSubCategory']];
        }

        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->handleQuery(DocumentList::create($filters));
        if (!$response->isOk()) {
            throw new \Exception('Error retrieving document list');
        }

        return $response;
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
                fn($arg) => $arg !== null
            )
        );
    }
}
