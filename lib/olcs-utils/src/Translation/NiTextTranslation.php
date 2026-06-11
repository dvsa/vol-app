<?php

namespace Dvsa\Olcs\Utils\Translation;

use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\Translator;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NiTextTranslation implements FactoryInterface
{
    /**
     * @var Translator
     */
    private $translator;

    public function setLocaleForNiFlag($niFlag)
    {
        if (!ValueHelper::isOn($niFlag)) {
            return;
        }

        $this->translator->setFallbackLocale($this->translator->getLocale());
        $this->translator->setLocale(str_replace('GB', 'NI', $this->translator->getLocale()));
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return $this
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NiTextTranslation
    {
        $this->translator = $container->get('translator');
        return $this;
    }
}
