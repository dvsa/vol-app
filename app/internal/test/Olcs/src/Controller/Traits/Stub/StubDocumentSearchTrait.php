<?php

namespace OlcsTest\Controller\Traits\Stub;

use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use Olcs\Service\Data\DocumentSubCategory;

/**
 * Stub for testing @see \Olcs\Controller\Traits\DocumentSearchTrait
 */
class StubDocumentSearchTrait
{
    use \Olcs\Controller\Traits\DocumentSearchTrait;

    protected FormHelperService $formHelper;
    protected DocumentSubCategory $docSubCategoryDataService;

    public function __construct(
        FormHelperService $formHelper,
        DocumentSubCategory $docSubCategoryDataService
    ) {
        $this->formHelper = $formHelper;
        $this->docSubCategoryDataService = $docSubCategoryDataService;
    }

    protected function getDocumentTableName()
    {
        return 'DocTableName';
    }

    public function traitUpdateSelectValueOptions($el, $options)
    {
        $this->updateSelectValueOptions($el, $options);
    }

    public function traitMapDocumentFilters(array $extra)
    {
        return $this->mapDocumentFilters($extra);
    }

    public function traitGetDocumentForm(array $extra)
    {
        return $this->getDocumentForm($extra);
    }
}
