<?php

declare(strict_types=1);

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

    public function __construct(protected FormHelperService $formHelper, protected DocumentSubCategory $docSubCategoryDataService)
    {
    }

    protected function getDocumentTableName(): string
    {
        return 'DocTableName';
    }

    public function traitUpdateSelectValueOptions(mixed $el, mixed $options): void
    {
        $this->updateSelectValueOptions($el, $options);
    }

    public function traitMapDocumentFilters(array $extra): array
    {
        return $this->mapDocumentFilters($extra);
    }

    public function traitGetDocumentForm(array $extra): mixed
    {
        return $this->getDocumentForm($extra);
    }
}
