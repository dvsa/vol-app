<?php

declare(strict_types=1);

namespace OlcsTest\View;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LoginTitleTest extends TestCase
{
    #[DataProvider('loginStates')]
    public function testLoginTitle(bool $failed, string $formErrors, string $expectedTitle): void
    {
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack([
            'script_paths' => [__DIR__ . '/../../../../module/Olcs/view'],
        ]));
        $helpers = $renderer->getHelperPluginManager();
        $helpers->setService('form', new \Laminas\Form\View\Helper\Form());
        $renderer->headTitle()->setSeparator(' - ')->append('Olcs');

        foreach (
            [
            'flashMessengerAll' => fn () => '',
            'formErrors' => fn () => $formErrors,
            'formRow' => fn () => '',
            'formElement' => fn () => '',
            'pageTitle' => fn () => 'Sign in',
            'translate' => fn (string $text) => $text === 'Error' ? 'Translated error' : $text,
            'url' => fn () => '/forgot-password',
            'assetPath' => fn (string $path) => $path,
            'applicationName' => fn () => 'internal',
            'pageId' => fn () => 'login',
            'version' => fn () => '',
            'systemInfoMessages' => fn () => '',
            'currentUser' => fn () => new class {
                public function isLoggedIn(): bool
                {
                    return false;
                }
            },
            ] as $name => $callback
        ) {
            $helpers->setService($name, new class ($callback) extends AbstractHelper {
                public function __construct(private \Closure $callback)
                {
                }

                public function __invoke(...$args): mixed
                {
                    return ($this->callback)(...$args);
                }
            });
        }

        $form = new Form('login');
        foreach (['username', 'password', 'declarationRead', 'submit', 'security'] as $name) {
            $form->add(new Element($name));
        }

        $content = $renderer->render('pages/auth/login', [
            'form' => $form,
            'failed' => $failed,
            'failureReason' => 'Authentication Failed',
        ]);

        $partial = new class extends AbstractHelper {
            public function __invoke(string $template, ?array $values = null): string
            {
                return $template === 'partials/head' ? $this->getView()->render($template, $values) : '';
            }
        };
        $partial->setView($renderer);
        $helpers->setService('partial', $partial);
        $html = $renderer->render('layout/signin', ['content' => $content]);

        $this->assertStringContainsString('<title>' . $expectedTitle . '</title>', $html);
    }

    public static function loginStates(): array
    {
        return [
            'initial login' => [false, '', 'Olcs - Sign in'],
            'rejected credentials' => [true, '', 'Translated error: Olcs - Sign in'],
            'invalid form' => [false, '<div class="govuk-error-summary">Invalid</div>', 'Translated error: Olcs - Sign in'],
            'both errors' => [true, '<div class="govuk-error-summary">Invalid</div>', 'Translated error: Olcs - Sign in'],
        ];
    }
}
