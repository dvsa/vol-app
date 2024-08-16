<?php

/**
 * Document Naming Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Document Naming Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NamingService implements FactoryInterface
{
    /**
     * e.g. documents/{Category}/{SubCategory}/{Date:Y}/{Date:m}/{Date:YmdHis}_{Context}_{Description}.{Extension}
     *
     * @var string
     */
    protected $pattern;

    /**
     * @param string $description
     * @param string $extension
     * @param Category|null $category
     * @param SubCategory|null $subCategory
     * @param ContextProviderInterface|null $entity
     * @return string
     */
    public function generateName(
        $description,
        $extension,
        Category $category = null,
        SubCategory $subCategory = null,
        ContextProviderInterface $entity = null
    ) {
        $description = $this->formatDescription($description);
        $model = new NamingModel(new DateTime(), $description, $extension, $category, $subCategory, $entity);

        $name = preg_replace_callback(
            '/{([a-zA-Z]+)(?:\:([^:}]+))?}/',
            function ($matches) use ($model) {
                if (isset($matches[2])) {
                    return $this->valueOrAlt($model->{'get' . $matches[1]}($matches[2]), 'Unknown');
                }

                return $this->valueOrAlt($model->{'get' . $matches[1]}(), 'Unknown');
            },
            $this->pattern
        );

        return str_replace(' ', '_', $name);
    }

    protected function valueOrAlt($value = null, $alt = 'Unknown')
    {
        return $value ?? $alt;
    }

    /**
     * Formats a description so that illegal characters don't make it into the file paths
     *
     * @param $input
     * @return mixed
     */
    private function formatDescription($input)
    {
        $input = str_replace([' ', '/'], '_', $input);

        // Only allow alpha-num plus "_()"
        return preg_replace('/[^a-zA-Z0-9_\(\)\-\&]/', '', $input);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!isset($config['document_share']['path'])) {
            throw new \RuntimeException('document_share/path has not been defined in config');
        }
        $this->pattern = $config['document_share']['path'];
        return $this;
    }
}
