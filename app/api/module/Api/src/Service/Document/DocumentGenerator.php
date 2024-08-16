<?php

namespace Dvsa\Olcs\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;

/**
 * Document Generator
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentGenerator implements FactoryInterface
{
    /**
     * Hold an in memory cache of templates fetched from the store;
     * Useful when multiple copies of the same template are printed
     * during a single request
     */
    private $templateCache = [];

    /**
     * @var Document
     */
    private $documentService;

    /**
     * @var QueryHandlerManager
     */
    private $queryHandlerManager;

    /**
     * @var ContentStoreFileUploader
     */
    private $uploader;

    /**
     * @var DocumentStoreInterface
     */
    private $contentStore;

    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\Document
     */
    private $documentRepo;

    private $disableBookmarksFlag = false;

    /**
     * Helper method to generate a string of content from a given template and
     * query parameters
     *
     * @param string|int $template             Template path or id
     * @param array  $queryData            Query Data
     * @param array  $knownValues          Values
     * @param bool $disableBookmarksFlag should the bookmark rendering be disabled?
     *
     * @return string
     */
    public function generateFromTemplate($template, $queryData = [], $knownValues = [], $disableBookmarksFlag = false)
    {
        $this->disableBookmarksFlag = $disableBookmarksFlag;
        // if template is an int then assume it is an ID to a document ID
        if (is_int($template)) {
            try {
                /** @var \Dvsa\Olcs\Api\Entity\Doc\Document $documentTemplate */
                $documentTemplate = $this->documentRepo->fetchById($template);
            } catch (\Dvsa\Olcs\Api\Domain\Exception\NotFoundException) {
                throw new \Exception('Template not found whilst trying to fetch document id' . $template);
            }
            $possibleTemplatePaths = [$documentTemplate->getIdentifier()];
        } else {
            $templateWithPrefix = '/templates/' . $this->addTemplatePrefix($queryData, $template) . '.rtf';
            $templateWithoutPrefix = '/templates/' . $template . '.rtf';

            $possibleTemplatePaths = [
                $template => $template,
                $templateWithPrefix => $templateWithPrefix,
                $templateWithoutPrefix => $templateWithoutPrefix
            ];
        }

        return $this->generateFromTemplateIdentifier($possibleTemplatePaths, $queryData, $knownValues);
    }

    /**
     * Upload the generated content
     *
     * @param string $content  File Content
     * @param string $fileName File name at Storage
     *
     * @return DsFile
     */
    public function uploadGeneratedContent($content, $fileName)
    {
        $file = new DsFile();
        $file->setContent($content);

        try {
            return $this->uploader->upload($fileName, $file);
        } catch (\Exception $e) {
            unset($file);
            throw $e;
        }
    }

    /**
     * Generate From Template Identifier
     *
     * @param array $possibleTemplatePaths Template Paths
     * @param array $queryData             Query Data
     * @param array $knownValues           Values
     *
     * @return string
     * @throws \Exception
     */
    private function generateFromTemplateIdentifier(array $possibleTemplatePaths, $queryData = [], $knownValues = [])
    {
        $file = $this->getTemplate($possibleTemplatePaths);

        if ($file === null) {
            throw new \Exception('Unable to get template file: ' . implode(',', $possibleTemplatePaths));
        }

        try {
            $queries =
                $this->disableBookmarksFlag ? [] :
                $this->documentService->getBookmarkQueries($file, $queryData);
        } catch (\Exception $e) {
            throw new \Exception('Error generating the document: ' . $e->getMessage());
        }

        $result = [];

        foreach ($queries as $token => $query) {
            if ($query instanceof QueryInterface) {
                try {
                    $result[$token] = $this->queryHandlerManager->handleQuery($query);
                } catch (\Exception $ex) {
                    throw new \Exception('Error fetching data for bookmark: ' . $token . ': ' . $ex->getMessage());
                }
            } elseif (is_array($query)) {
                $list = [];
                foreach ($query as $qry) {
                    $list[] = $this->queryHandlerManager->handleQuery($qry);
                }
                $result[$token] = $list;
            }
        }
        $result = ArrayUtils::merge($result, $knownValues, true);

        return $this->documentService->populateBookmarks($file, $result);
    }

    /**
     * Add the template prefix
     *
     * @param array  $queryData Query Data
     * @param string $template  Template path
     *
     * @return string
     */
    private function addTemplatePrefix($queryData, $template)
    {
        foreach (['application', 'licence'] as $key) {
            if (isset($queryData[$key])) {
                if ($key === 'licence') {
                    $qry = LicenceBundle::create(['id' => $queryData[$key]]);
                } else {
                    $qry = ApplicationBundle::create(['id' => $queryData[$key]]);
                }

                $result = $this->queryHandlerManager->handleQuery($qry);

                return $this->getPrefix($result['niFlag']) . '/' . $template;
            }
        }

        return $template;
    }

    /**
     * Get the template prefix
     *
     * @param string $niFlag Flag
     *
     * @return string
     */
    private function getPrefix($niFlag)
    {
        return $niFlag === 'N' ? 'GB' : 'NI';
    }

    /**
     * Grab the template from the possible paths
     *
     * @param array $possibleTemplatePaths Path to template
     *
     * @return DsFile|null
     */
    private function getTemplate(array $possibleTemplatePaths)
    {
        foreach ($possibleTemplatePaths as $template) {
            if (isset($this->templateCache[$template])) {
                return $this->templateCache[$template];
            }

            $file = $this->contentStore->read($template);
            if ($file === null || $file === false) {
                continue;
            }

            $this->templateCache[$template] = $file;
            return $this->templateCache[$template];
        }

        return null;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->documentService = $container->get('Document');
        $this->queryHandlerManager = $container->get('QueryHandlerManager');
        $this->uploader = $container->get('FileUploader');
        $this->contentStore = $container->get('ContentStore');
        $this->documentRepo = $container->get('RepositoryServiceManager')->get('Document');
        return $this;
    }
}
