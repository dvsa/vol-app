<?php

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Document\DocumentList;

/**
 * Document Search Trait
 */
trait DocumentSearchTrait
{
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

        // @see https://jira.i-env.net/browse/OLCS-6061
        $filters['isDoc'] = true;

        // the way this method is being called this sometimes comes
        // through as DESC; that's *never* right
        $filters['order'] = 'ASC';

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $selects = [
            // @todo These methods from ListDataTrait have not been migrated, as this will be done as part of another
            // story
            'category' => $this->getListDataFromBackend('Category', ['isDocCategory' => true], 'description'),
            'documentSubCategory' => $this->getListDataFromBackend('SubCategory', $filters, 'subCategoryName')
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
        $response = $this->handleQuery(DocumentList::create($filters));

        if (!$response->isOk()) {
            throw new \Exception('Error retrieving document list');
        }

        $documents = $response->getResult();

        $filters['query'] = $this->getRequest()->getQuery();

        $table = $this->getTable('documents', $documents, $filters);

        return $table;
    }
}
