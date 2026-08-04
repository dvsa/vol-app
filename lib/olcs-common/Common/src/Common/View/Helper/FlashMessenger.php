<?php

namespace Common\View\Helper;

use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\View\Helper\FlashMessenger as LaminasFlashMessenger;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger as PluginFlashMessenger;

/**
 * Flash messenger view helper (Extends laminas flash messenger)
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FlashMessenger extends LaminasFlashMessenger
{
    /**
     * Templates for the open/close/separators for message tags
     *
     * @var string
     */
    protected $messageCloseString = '</p></div>';

    protected $messageOpenFormat = '<div %s><p role="alert">';

    protected $messageSeparatorString = '</p></div><div %s><p>';

    /**
     * Whether the template has already been rendered
     *
     * @var bool
     */
    protected $isRendered = false;

    /**
     * Holds the wrapper format
     *
     * @var string
     */
    private $wrapper = '<div class="notice-container">%s</div>';

    /** @var FlashMessengerHelperService */
    protected $flashMessengerHelperService;

    public function __construct(FlashMessengerHelperService $flashMessengerHelperService)
    {
        $this->flashMessengerHelperService = $flashMessengerHelperService;
    }

    /**
     * Invoke
     *
     * @param string $namespace Namespace
     *
     * @return static|string
     */
    #[\Override]
    public function __invoke($namespace = null): string|static
    {
        if ($namespace === 'norender') {
            return $this;
        }

        return $this->render();
    }

    /**
     * Get messages from namespace
     *
     * @param string $namespace Namespace
     *
     * @return array
     */
    public function getMessagesFromNamespace($namespace)
    {
        $fm = $this->getPluginFlashMessenger();

        return $fm->getMessagesFromNamespace($namespace);
    }

    /**
     * Set isRendered
     *
     * @param bool $isRendered Is rendered
     *
     * @return FlashMessenger
     */
    public function setIsRendered($isRendered)
    {
        $this->isRendered = $isRendered;
        return $this;
    }

    /**
     * Get isRendered
     *
     * @return bool
     */
    public function getIsRendered()
    {
        return $this->isRendered;
    }

    /**
     * Render messages
     *
     * @param string    $namespace  Namespace
     * @param array     $classes    Classes
     * @param bool|null $autoEscape AutoEscape
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function render(
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $classes = [],
        $autoEscape = null
    ) {
        if ($this->getIsRendered()) {
            return '';
        }

        $markup = $this->renderAllFromNamespace('error', ['notice--danger']);
        $markup .= $this->renderAllFromNamespace('success', ['notice--success']);
        $markup .= $this->renderAllFromNamespace('warning', ['notice--warning']);
        $markup .= $this->renderAllFromNamespace('info', ['notice--info']);
        $markup .= $this->renderAllFromNamespace('default', ['notice--info']);

        if ($markup === '' || $markup === '0') {
            return '';
        }

        $this->setIsRendered(true);

        return sprintf($this->wrapper, $markup);
    }

    /**
     * Render all from namespace
     *
     * @param string $namespace Namespace
     * @param array  $classes   Classes
     *
     * @return string
     */
    protected function renderAllFromNamespace(
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $classes = []
    ) {
        return parent::render($namespace, $classes) .
            $this->renderCurrent($namespace, $classes);
    }

    /**
     * Render current messages
     *
     * @param string    $namespace  Namespace
     * @param array     $classes    Classes
     * @param bool|null $autoEscape AutoEscape
     *
     * @return string
     */
    #[\Override]
    public function renderCurrent(
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $classes = [],
        $autoEscape = null
    ) {
        $content = parent::renderCurrent($namespace, $classes);

        return $content . $this->renderMessages(
            $namespace,
            $this->flashMessengerHelperService->getCurrentMessages($namespace),
            $classes,
            $autoEscape
        );
    }

    /**
     * Majority of this is copied from Laminas however I have removed the code to escape html, as we need to display HTML
     * in our flash messengers, and we shouldn't ever need to escape it as our messages will never contain user entered
     * info
     *
     * @param string    $namespace  Namespace
     * @param array     $messages   Messages
     * @param array     $classes    Classes
     * @param bool|null $autoEscape AutoEscape
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    protected function renderMessages(
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $messages = [],
        array $classes = [],
        $autoEscape = null
    ) {
        // Flatten message array
        $messagesToPrint = [];
        $translator = $this->getTranslator();
        $translatorTextDomain = $this->getTranslatorTextDomain();

        array_walk_recursive(
            $messages,
            static function ($item) use (&$messagesToPrint, $translator, $translatorTextDomain) {
                if ($translator !== null) {
                    $item = $translator->translate(
                        $item,
                        $translatorTextDomain
                    );
                }
                $messagesToPrint[] = $item;
            }
        );

        if ($messagesToPrint === []) {
            return '';
        }

        // Generate markup
        $markup = sprintf(
            $this->getMessageOpenFormat(),
            'class="' . implode(' ', $classes) . '"'
        );

        $markup .= implode(
            sprintf(
                $this->getMessageSeparatorString(),
                'class="' . implode(' ', $classes) . '"'
            ),
            $messagesToPrint
        );

        return $markup . $this->getMessageCloseString();
    }
}
