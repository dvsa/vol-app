<?php

namespace Olcs\Controller\Cookie;

use Common\Controller\AbstractOlcsController;
use Olcs\Service\Cookie\CurrentPreferencesProvider;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use Olcs\Service\Cookie\SetCookieGenerator;
use RuntimeException;
use Zend\Validator\Csrf as CsrfValidator;
use Zend\View\Model\ViewModel;

class SettingsController extends AbstractOlcsController
{
    const SUCCESS_QUERY_PARAM = 'success';
    const SUCCESS_QUERY_VALUE = 'true';

    /** @var CurrentPreferencesProvider */
    private $currentPreferencesProvider;

    /** @var SetCookieGenerator */
    private $setCookieGenerator;

    /** @var PreferencesFactory */
    private $preferencesFactory;

    /**
     * Create service instance
     *
     * @param CurrentPreferencesProvider $currentPreferencesProvider
     * @param SetCookieGenerator $setCookieGenerator
     * @param PreferencesFactory $preferencesFactory
     *
     * @return SettingsController
     */
    public function __construct(
        CurrentPreferencesProvider $currentPreferencesProvider,
        SetCookieGenerator $setCookieGenerator,
        PreferencesFactory $preferencesFactory
    ) {
        $this->currentPreferencesProvider = $currentPreferencesProvider;
        $this->setCookieGenerator = $setCookieGenerator;
        $this->preferencesFactory = $preferencesFactory;
    }

    /**
     * Generic action
     */
    public function genericAction()
    {
        if ($this->request->isPost()) {
            $parsedPreferences = $this->getParsedPreferenceValues(
                $this->params()->fromPost()
            );

            try {
                $preferences = $this->preferencesFactory->create($parsedPreferences);

                $this->getResponse()->getHeaders()->addHeader(
                    $this->setCookieGenerator->generate($preferences)
                );
            } catch (RuntimeException $e) {
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
     * @param array $preferences
     *
     * @return array
     */
    private function getParsedPreferenceValues(array $preferences)
    {
        $parsedPreferences = [];

        foreach (Preferences::KEYS as $key) {
            $parsedValue = isset($preferences[$key]) && ($preferences[$key] == 'true');
            $parsedPreferences[$key] = $parsedValue;
        }

        return $parsedPreferences;
    }
}
