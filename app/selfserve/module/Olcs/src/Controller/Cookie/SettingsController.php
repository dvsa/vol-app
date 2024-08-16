<?php

namespace Olcs\Controller\Cookie;

use Common\Controller\AbstractOlcsController;
use Laminas\Validator\Csrf as CsrfValidator;
use Laminas\View\Model\ViewModel;
use Olcs\Service\Cookie\CurrentPreferencesProvider;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use Olcs\Service\Cookie\SetCookieArrayGenerator;
use RuntimeException;

class SettingsController extends AbstractOlcsController
{
    public const SUCCESS_QUERY_PARAM = 'success';
    public const SUCCESS_QUERY_VALUE = 'true';

    /**
     * Create service instance
     *
     *
     * @return SettingsController
     */
    public function __construct(private readonly CurrentPreferencesProvider $currentPreferencesProvider, private readonly SetCookieArrayGenerator $setCookieArrayGenerator, private readonly PreferencesFactory $preferencesFactory)
    {
    }

    /**
     * Generic action
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function genericAction()
    {
        if ($this->request->isPost()) {
            $parsedPreferences = $this->getParsedPreferenceValues(
                $this->params()->fromPost()
            );

            try {
                $preferences = $this->preferencesFactory->create($parsedPreferences);

                $cookies = $this->setCookieArrayGenerator->generate(
                    $preferences,
                    $this->request->getCookie()
                );

                $headers = $this->getResponse()->getHeaders();
                foreach ($cookies as $cookie) {
                    $headers->addHeader($cookie);
                }
            } catch (RuntimeException) {
            }

            return $this->redirect()->toRoute(
                'cookies/settings',
                [],
                [
                    'query' => [self::SUCCESS_QUERY_PARAM => self::SUCCESS_QUERY_VALUE]
                ]
            );
        }

        $preferences = $this->currentPreferencesProvider->getPreferences(
            $this->request->getCookie()
        );

        $csrfValidator = new CsrfValidator(['name' => 'security']);
        $security = $csrfValidator->getHash();

        $view = new ViewModel();
        $view->setVariable('preferences', $preferences->asArray());
        $view->setVariable('security', $security);
        $view->setVariable(
            'success',
            $this->params()->fromQuery(self::SUCCESS_QUERY_PARAM) == self::SUCCESS_QUERY_VALUE
        );

        $view->setTemplate('pages/cookie/settings');

        return $view;
    }

    /**
     * Convert post data into a value suitable for the PreferencesFactory
     *
     *
     * @return array
     */
    private function getParsedPreferenceValues(array $preferences)
    {
        $parsedPreferences = [];

        foreach (Preferences::KEYS as $key) {
            $parsedValue = isset($preferences[$key]) && ($preferences[$key] === 'true');
            $parsedPreferences[$key] = $parsedValue;
        }

        return $parsedPreferences;
    }
}
