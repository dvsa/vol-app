<?php

namespace Dvsa\Olcs\Email\Service;

use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Email\Data\Message;

/**
 * Class TemplateRenderer
 */
class TemplateRenderer
{
    /**
     * StrategySelectingViewRenderer
     */
    protected $viewRenderer;

    protected bool $notifyMode = false;

    /** @var array<string, string> locale → Notify passthrough template UUID */
    protected array $passthroughTemplateUuids = [];

    /**
     * @return StrategySelectingViewRenderer
     */
    public function getViewRenderer()
    {
        return $this->viewRenderer;
    }

    /**
     * @return StrategySelectingViewRenderer
     */
    public function setViewRenderer(StrategySelectingViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
        return $this;
    }

    public function setNotifyMode(bool $enabled): self
    {
        $this->notifyMode = $enabled;
        return $this;
    }

    /**
     * Whether the active mailer DSN routes through GOV.UK Notify. Set by the factory from
     * `config['mail']['dsn']` at boot. Used by EmailAwareTrait::sendEmailTemplate() to decide
     * whether to render a markdown body (`format='md'`) or the legacy html+plain bodies.
     */
    public function isNotifyMode(): bool
    {
        return $this->notifyMode;
    }

    /**
     * @param array<string, string> $uuids
     */
    public function setPassthroughTemplateUuids(array $uuids): self
    {
        $this->passthroughTemplateUuids = $uuids;
        return $this;
    }

    public function getPassthroughTemplateUuid(string $locale): ?string
    {
        $uuid = $this->passthroughTemplateUuids[$locale] ?? null;
        return is_string($uuid) && $uuid !== '' ? $uuid : null;
    }

    /**
     * Render a template into the message body.
     *
     * In Notify mode (mailer DSN starts `govuknotify`), this delegates to
     * {@see self::renderMarkdownBody()} — reads the matching `format='md'` row, sets it on
     * the message's markdownBody, stamps the passthrough Notify template UUID. Missing md rows
     * propagate as NotFoundException to enforce atomic per-env cutover (see VOL-7238).
     *
     * In SMTP mode (legacy default), renders html+plain bodies via the existing path.
     *
     * @param string|array $templates
     * @param array $variables
     * @param string|bool $layout
     */
    public function renderBody(Message $message, $templates, $variables = [], $layout = 'default')
    {
        if ($this->notifyMode) {
            $uuid = $this->getPassthroughTemplateUuid($message->getLocale());
            if ($uuid !== null) {
                $message->setTemplateKey($uuid);
            }
            $this->renderMarkdownBody($message, $templates, is_array($variables) ? $variables : []);
            return;
        }

        $locale = $message->getLocale();

        $plainContent = $this->getEmailContent($locale, $templates, 'plain', $variables);
        $message->setPlainBody($this->getLayoutView($locale, $layout, 'plain', $plainContent));

        //works around inspection request email which doesn't have a HTML version, and sets the HTML variable to false
        if ($message->getHasHtml()) {
            $htmlContent = $this->getEmailContent($locale, $templates, 'html', $variables);
            $message->setHtmlBody($this->getLayoutView($locale, $layout, 'html', $htmlContent));
        }
    }

    /**
     * Renders a Markdown-Twig template (`format='md'`) directly into the message's markdownBody.
     *
     * Unlike {@see self::renderBody()}, there is no layout wrap — Notify provides its own email
     * chrome server-side (and the dev transport applies a GOV.UK-alike chrome locally).
     *
     * @param string|array<int, string> $templates
     * @param array<string, mixed> $variables
     */
    public function renderMarkdownBody(Message $message, $templates, array $variables = []): void
    {
        $locale = $message->getLocale();
        $markdown = $this->getEmailContent($locale, $templates, 'md', $variables);
        $message->setMarkdownBody($markdown);
    }

    /**
     * @param string $locale
     * @param string $layout
     * @param string $format
     * @param string $content
     *
     * @return string
     */
    private function getLayoutView($locale, $layout, $format, $content)
    {
        return $this->viewRenderer->render($locale, $format, $layout, ['content' => $content]);
    }

    /**
     * @param string $locale
     * @param string|array $templates
     * @param string $format
     * @return string
     */
    private function getEmailContent($locale, $templates, $format, array $variables = [])
    {
        $templateViews = [];

        if (!is_array($templates)) {
            $templates = [$templates];
        }

        foreach ($templates as $template) {
            $templateViews[] = $this->getTemplateView($locale, $template, $format, $variables);
        }

        return implode('', $templateViews);
    }

    /**
     * @param string $locale
     * @param string $template
     * @param string $format
     * @return string
     */
    private function getTemplateView($locale, $template, $format, array $variables = [])
    {
        return $this->viewRenderer->render($locale, $format, $template, $variables);
    }
}
