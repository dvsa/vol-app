<?php

namespace Common\Preference;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\Http\Request as HttpRequest;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\I18n\Translator\Translator;
use Psr\Container\ContainerInterface;

class LanguageListener implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var Language
     */
    private $languagePref;

    /**
     * @var FlashMessengerHelperService
     */
    private $flashMessenger;

    /**
     * @var Translator
     */
    private $translator;

    #[\Override]
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
    }

    public function onRoute(MvcEvent $e): void
    {
        $request = $e->getRequest();
        if (!($request instanceof HttpRequest)) {
            return;
        }

        $lang = $request->getQuery('lang');

        if ($lang !== null) {
            try {
                $this->languagePref->setPreference($lang);
            } catch (\Exception) {
                $this->flashMessenger->addCurrentErrorMessage('Only English and Welsh languages are supported');
            }
        }

        $this->translator->setLocale($this->languagePref->getPreference() . '_GB');
    }

    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LanguageListener
    {
        $this->languagePref = $container->get('LanguagePreference');
        $this->flashMessenger = $container->get('Helper\FlashMessenger');
        $this->translator = $container->get('translator');
        return $this;
    }
}
