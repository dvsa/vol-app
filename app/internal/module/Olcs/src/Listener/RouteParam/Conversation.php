<?php

declare(strict_types=1);

namespace Olcs\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Query\Search\Licence as LicenceQuery;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Navigation\Navigation as SideNavigation;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Listener\RouteParams;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Navigation\AbstractContainer;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Navigation;

class Conversation implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    private AbstractContainer $sidebarNavigationService;
    private AnnotationBuilder $annotationBuilder;
    private QueryService $queryService;
    private Navigation $navigationPlugin;
    private SideNavigation $sideNavigation;

    /** @param int $priority */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'licence',
            [$this, 'onConversation'],
            $priority,
        );
    }

    public function onConversation(EventInterface $e): void
    {
        $routeParam = $e->getTarget();

        $licence = $this->getLicence((int)$routeParam->getValue());
        $isMessagingDisabled = $licence['organisation']['isMessagingDisabled'];

        /** @var AbstractContainer $navigationPlugin */
        $navigationPlugin = $this->navigationPlugin->__invoke('navigation');

        $tag = $isMessagingDisabled ? 'conversation_list_disable_messaging' : 'conversation_list_enable_messaging';
        array_map(fn($page) => $page->setVisible(false), $navigationPlugin->findBy('tag', $tag, true));

        if (!$isMessagingDisabled) {
            $this->showFileUploadButtons($licence);
        }
    }


    protected function showFileUploadButtons(array $licence): void
    {
        $isFileUploadEnabled = $licence['organisation']['isMessagingFileUploadEnabled'];

        if ($isFileUploadEnabled) {
            $this->sideNavigation->findBy('id', 'licence-disable-file-uploads')->setVisible();
            $this->sideNavigation->findBy('id', 'application-disable-file-uploads')->setVisible();
        } else {
            $this->sideNavigation->findBy('id', 'licence-enable-file-uploads')->setVisible();
            $this->sideNavigation->findBy('id', 'application-enable-file-uploads')->setVisible();
        }
    }

    private function getLicence(int $id): array
    {
        $query = $this->annotationBuilder->createQuery(LicenceQuery::create(['id' => $id]));
        $response = $this->queryService->send($query);

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Licence id [$id] not found");
        }

        return $response->getResult();
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Conversation
    {
        $this->annotationBuilder = $container->get(AnnotationBuilder::class);
        $this->queryService = $container->get(QueryService::class);
        $this->navigationPlugin = $container->get('ViewHelperManager')->get('Navigation');
        $this->sideNavigation = $container->get('right-sidebar');

        return $this;
    }

    public function getAnnotationBuilder(): AnnotationBuilder
    {
        return $this->annotationBuilder;
    }

    public function getQueryService(): QueryService
    {
        return $this->queryService;
    }

    public function getNavigationPlugin(): Navigation
    {
        return $this->navigationPlugin;
    }

    public function getSideNavigationPlugin(): SideNavigation
    {
        return $this->sideNavigation;
    }

    public function setAnnotationBuilder(AnnotationBuilder $annotationBuilder): void
    {
        $this->annotationBuilder = $annotationBuilder;
    }

    public function setQueryService(QueryService $queryService): void
    {
        $this->queryService = $queryService;
    }

    public function setNavigationPlugin(Navigation $navigationPlugin): void
    {
        $this->navigationPlugin = $navigationPlugin;
    }

    public function setSideNavigationPlugin(SideNavigation $navigationPlugin): void
    {
        $this->sideNavigation = $navigationPlugin;
    }
}
