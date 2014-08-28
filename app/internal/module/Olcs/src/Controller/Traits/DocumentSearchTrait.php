<?php

namespace Olcs\Controller\Traits;

/**
 * Class DocumentSearchTrait
 * @package Olcs\Controller
 */
trait DocumentSearchTrait
{

    /**
     * Inspect the request to see if we have any filters set, and
     * if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function mapDocumentFilters($extra = array())
    {
        $defaults = array(
            'sort'   => 'id',
            'order'  => 'ASC',
            'page'   => 1,
            'limit'  => 10
        );

        $filters = array_merge(
            $defaults,
            $extra,
            $this->getRequest()->getQuery()->toArray()
        );

        // nuke any empty values too
        return array_filter(
            $filters,
            function ($v) {
                return !empty($v);
            }
        );
    }

    protected function getDocumentForm($filters = array())
    {
        $form = $this->getForm('documents-home');

        // setting $this->enableCsrf = false won't sort this; we never POST
        $form->remove('csrf');

        $form->setData($filters);

        return $form;
    }

    protected function getDocumentsTable($filters = array(), $render = true)
    {
        $documents = $this->makeRestCall(
            'Document',
            'GET',
            $filters
        );

        $table = $this->getTable(
            'documents',
            $documents,
            array_merge(
                $filters,
                array('query' => $this->getRequest()->getQuery())
            )
        );

        if ($render) {
            return $table->render();
        }
        return $table;
    }
}
