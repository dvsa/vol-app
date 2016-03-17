<?php

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Document\DocumentList;

/**
 * Document Search Trait
 */
trait DocumentSearchTrait
{
    protected abstract function getDocumentTableName();

    /**
     * Inspect the request to see if we have any filters set, and if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function mapDocumentFilters($extra = [])
    {
        $defaults = [
            'sort' => 'issuedDate',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 10
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

    protected function getDocumentForm($filters = [])
    {
        $form = $this->getForm('DocumentsHome');

        $category = (isset($filters['category'])) ? (int) $filters['category'] : null;

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $selects = [
            'category' => $this->getListDataCategoryDocs('All'),
            'documentSubCategory' => $this->getListDataSubCategoryDocs($category, 'All')
        ];

        // insert relevant data into the corresponding form inputs
        foreach ($selects as $name => $options) {
            $form->get($name)->setValueOptions($options);
        }

        // setting $this->enableCsrf = false won't sort this; we never POST
        $form->remove('csrf');

        $form->setData($filters);

        return $form;
    }

    protected function getDocumentsTable($filters = [])
    {
        if (isset($filters['documentSubCategory'])) {
            // query requires array of subcategories
            $filters['documentSubCategory'] = [$filters['documentSubCategory']];
        }
        $response = $this->handleQuery(DocumentList::create($filters));
        if (!$response->isOk()) {
            throw new \Exception('Error retrieving document list');
        }

        $documents = $response->getResult();

        $filters['query'] = $this->getRequest()->getQuery();

        $table = $this->getTable($this->getDocumentTableName(), $documents, $filters);

        return $table;
    }
}
