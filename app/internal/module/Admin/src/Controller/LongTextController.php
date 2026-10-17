<?php

declare(strict_types=1);

namespace Admin\Controller;

use Laminas\View\Model\ViewModel;

final class LongTextController extends EditableTranslationsController
{
    protected $navigationId = 'admin-dashboard/content-management/long-text';
    protected $tableName = 'admin-long-text';
    protected $resultsTableTitle = 'Long Text';
    protected $translationRoute = 'admin-dashboard/admin-long-text';
    protected $detailsContentTitle = 'Long Text';
    protected $longTextMode = true;

    #[\Override]
    protected function getListFilters(bool $includeText = false): array
    {
        return $includeText
            ? ['markupOnly' => true]
            : ['format' => 'editorjs'];
    }

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel([
            'navigationId' => 'admin-dashboard/content-management',
            'navigationTitle' => 'Long Text',
        ]);
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
