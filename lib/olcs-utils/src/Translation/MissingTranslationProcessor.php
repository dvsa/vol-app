<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Translation;

use Closure;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;

/**
 * Resolves "missing" translation keys by:
 *   - expanding `{nested.key}` tokens inside the missing message
 *   - rendering `markup-*` keys as locale-scoped view partials
 *   - replacing `{{PLACEHOLDER:NAME}}` markers from a view placeholder helper
 *
 * Listens on the translator's own `EVENT_MISSING_TRANSLATION` — the event lives
 * on the translator, not on the MVC event manager, so it carries no MVC coupling.
 * Dependencies are constructor-injected; the matching factory builds the
 * collaborators and a delegator attaches it to the translator at service
 * construction time.
 */
class MissingTranslationProcessor implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @param Closure|null $placeholder The `getPlaceholder` view helper, which is
     *                                  registered as a closure of signature
     *                                  `fn(string $name): \Dvsa\Olcs\Utils\View\Helper\GetPlaceholder`.
     */
    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly ResolverInterface $resolver,
        private readonly ?Closure $placeholder = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            Translator::EVENT_MISSING_TRANSLATION,
            [$this, 'processEvent'],
            $priority,
        );
    }

    /**
     * @return string|void
     */
    public function processEvent(Event $e)
    {
        $translator = $e->getTarget();

        if (!$translator instanceof TranslatorInterface) {
            return;
        }

        $params = $e->getParams();

        $message = $params['message'];

        if (empty($message) || !is_string($message)) {
            return;
        }

        if (preg_match_all('/\{([^}]+)}/', $message, $matches)) {
            // handles text with translation keys inside curly braces {}
            foreach ($matches[0] as $key => $match) {
                $message = str_replace($match, $translator->translate($matches[1][$key]), $message);
            }
        }

        // handles partials as translations. Note we only try to resolve keys
        // that match a pattern, to avoid having to run the template resolver
        // against ALL missing translations
        if (str_starts_with($message, 'markup-')) {
            $locale    = $params['locale'];
            $partial   = $locale . '/' . $message; // e.g. en_GB/my-translation-key
            $foundPath = $this->resolver->resolve($partial);

            // Check for the non-NI version of the file
            if ($foundPath === false && str_contains($locale, 'NI')) {
                $fallbackLocale = str_replace('NI', 'GB', $locale);
                $partial   = $fallbackLocale . '/' . $message;
                $foundPath = $this->resolver->resolve($partial);
            }

            if ($foundPath !== false) {
                $message = $this->renderer->render($partial);
            }

            $message = $this->populatePlaceholder($message);
        }

        // if message has changed (ie its been translated) then return it
        if ($message !== $params['message']) {
            return $message;
        }

        // needs to return void so that the event is propagated to other listeners
    }

    private function populatePlaceholder(string $message): string
    {
        if ($this->placeholder === null) {
            return $message;
        }

        if (preg_match_all('/\{\{PLACEHOLDER:([a-zA-Z_0-9]+)}}/', $message, $matches)) {
            $placeholderHelper = $this->placeholder;

            foreach ($matches[0] as $index => $match) {
                $placeholder = $placeholderHelper($matches[1][$index])->asString();

                $message = str_replace($match, $placeholder, $message);
            }
        }

        return $message;
    }
}
