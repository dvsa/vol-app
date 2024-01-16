<?php

namespace Olcs\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSenderAwareInterface;
use Common\Service\Cqrs\Command\CommandSenderAwareTrait;
use Common\Service\Cqrs\Query\QuerySenderAwareInterface;
use Common\Service\Cqrs\Query\QuerySenderAwareTrait;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventInterface;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use \Dvsa\Olcs\Transfer\Query\Cases\Cases as ItemDto;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\View\Helper\PluginManagerAwareTrait as ViewHelperManagerAwareTrait;
use Common\Exception\ResourceNotFoundException;
use Laminas\View\Helper\Url;
use Laminas\View\Model\ViewModel;

class SubmissionsFurniture implements
    ListenerAggregateInterface,
    FactoryInterface,
    QuerySenderAwareInterface,
    CommandSenderAwareInterface
{
    use ListenerAggregateTrait,
        ViewHelperManagerAwareTrait,
        QuerySenderAwareTrait,
        CommandSenderAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            RouteParams::EVENT_PARAM . 'case',
            [$this, 'onSubmission'],
            $priority
        );
    }

    public function onSubmission(EventInterface $e)
    {
        $routeParam = $e->getTarget();

        $id = $routeParam->getValue();
        $case = $this->getCase($id);

        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $placeholder->getContainer('pageTitle')->set($this->getPageTitle($case));
        $placeholder->getContainer('status')->set($this->getStatusArray($case));
        $placeholder->getContainer('horizontalNavigationId')->set('case');

        // no right view
    }

    /**
     * Get the Case data
     *
     * @param int $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getCase($id)
    {
        $response = $this->getQuerySender()->send(
            ItemDto::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Case id [$id] not found");
        }

        return $response->getResult();
    }

    /**
     * Returns page title
     *
     * @param $case
     * @return string
     */
    private function getPageTitle($case)
    {
        $pageTitle = 'Case ' . $case['id'];

        /** @var Url $urlHelper */
        $urlHelper = $this->getViewHelperManager()->get('url');

        if (isset($case['application']['id'])) {
            // prepend with application link
            $appUrl = $urlHelper('lva-application/case', ['application' => $case['application']['id']], [], true);

            $pageTitle = sprintf('<a class="govuk-link" href="%1$s">%2$s</a> / %3$s', $appUrl, $case['application']['id'], $pageTitle);
        }

        if (isset($case['licence']['id'])) {
            // prepend with licence link
            $licUrl = $urlHelper('licence/cases', ['licence' => $case['licence']['id']], [], true);

            $pageTitle = sprintf('<a class="govuk-link" href="%1$s">%2$s</a> / %3$s', $licUrl, $case['licence']['licNo'], $pageTitle);
        }

        if (isset($case['transportManager']['id'])) {
            $url = $urlHelper(
                'transport-manager/details',
                ['transportManager' => $case['transportManager']['id']],
                [],
                true
            );

            $pageTitle = sprintf(
                '<a class="govuk-link" href="%s">%s %s</a> / %s',
                $url,
                $case['transportManager']['homeCd']['person']['forename'],
                $case['transportManager']['homeCd']['person']['familyName'],
                $pageTitle
            );
        }

        return $pageTitle;
    }

    /**
     * Get status array.
     *
     * @param $case
     *
     * @return array
     */
    private function getStatusArray($case)
    {
        $status = [
            'colour' => isset($case['closedDate']) ? 'grey' : 'orange',
            'value' => isset($case['closedDate']) ? 'Closed' : 'Open',
        ];

        return $status;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SubmissionsFurniture
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SubmissionsFurniture
    {
        $this->setQuerySender($container->get('QuerySender'));
        $this->setCommandSender($container->get('CommandSender'));
        $this->setViewHelperManager($container->get('ViewHelperManager'));
        return $this;
    }
}
