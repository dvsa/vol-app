<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\PublishedPublicationFilter;
use DateInterval;
use DateTimeImmutable;
use Dvsa\Olcs\Transfer\Query\Publication\PublishedList;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class PublishedPublicationController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-publication/published';
    protected $inlineScripts = ['indexAction' => ['file-link']];
    protected $listDto = PublishedList::class;
    protected $tableName = 'admin-published-publication';
    protected $filterForm = PublishedPublicationFilter::class;

    /**
     * Get view for menu on left of page
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-publication',
                'navigationTitle' => 'Publications'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Convert from form values to query values
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters = parent::modifyListQueryParameters($parameters);

        $now = new DateTimeImmutable('today');
        $pubDateMonth = $now->format('m');
        $pubDateYear = $now->format('Y');

        if (array_key_exists('pubDate', $parameters)) {
            $pubDate = $parameters['pubDate'];
            if (array_key_exists('month', $pubDate)) {
                $pubDateMonth = intVal($pubDate['month']);
            }
            if (array_key_exists('year', $pubDate)) {
                $pubDateYear = intVal($pubDate['year']);
            }
        }

        $monthStart = $now
            ->setDate($pubDateYear, $pubDateMonth, 1)
            ->setTime(0, 0, 0);

        $parameters['pubDateFrom'] = $monthStart->format('Y-m-d H:i:s');

        $parameters['pubDateTo'] = $monthStart->add(new DateInterval('P1M'))->format('Y-m-d H:i:s');

        unset($parameters['pubDate']);

        return $parameters;
    }
}
