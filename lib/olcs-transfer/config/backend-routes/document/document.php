<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'document' => RouteConfig::getRouteConfig(
        'document',
        [
            'generate-and-store' => RouteConfig::getRouteConfig(
                'generate-and-store',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Document\GenerateAndStore::class),
                ]
            ),
            'template' => RouteConfig::getRouteConfig(
                'template',
                [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'paragraphs' => RouteConfig::getRouteConfig(
                                'paragraphs',
                                [
                                    'GET' => QueryConfig::getConfig(Query\Document\TemplateParagraphs::class),
                                ]
                            )
                        ]
                    )
                ]
            ),
            'letter' => RouteConfig::getRouteConfig(
                'letter',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Document\CreateLetter::class),
                ]
            ),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Document\Document::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Document\DeleteDocument::class),
                    'letter' => RouteConfig::getRouteConfig(
                        'letter',
                        [
                            'GET' => QueryConfig::getConfig(Query\Document\Letter::class),
                            'print' => RouteConfig::getRouteConfig(
                                'print',
                                [
                                    'POST' => CommandConfig::getPostConfig(Command\Document\PrintLetter::class),
                                    'GET' => QueryConfig::getConfig(Query\Document\PrintLetter::class),
                                ]
                            )
                        ]
                    ),
                    'links' => RouteConfig::getRouteConfig(
                        'links',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Document\UpdateDocumentLinks::class),
                        ]
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Document\CreateDocument::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\Document\DeleteDocuments::class),
            'GET' => QueryConfig::getConfig(Query\Document\DocumentList::class),
            'copy' => RouteConfig::getRouteConfig(
                'copy',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Document\CopyDocument::class),
                ]
            ),
            'move' => RouteConfig::getRouteConfig(
                'move',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Document\MoveDocument::class),
                ]
            ),
            'download' => RouteConfig::getRouteConfig(
                'download',
                [
                    'GET' => QueryConfig::getConfig(Query\Document\Download::class),
                ]
            ),
            'download-guide' => RouteConfig::getRouteConfig(
                'download-guide',
                [
                    'GET' => QueryConfig::getConfig(Query\Document\DownloadGuide::class),
                ]
            ),
            'bucket-browser' => RouteConfig::getRouteConfig(
                'bucket-browser',
                [
                    'GET' => QueryConfig::getConfig(Query\Document\BucketBrowserList::class),
                    'download' => RouteConfig::getRouteConfig(
                        'download',
                        [
                            'GET' => QueryConfig::getConfig(Query\Document\BucketBrowserDownload::class),
                        ]
                    ),
                    'overwrite' => RouteConfig::getRouteConfig(
                        'overwrite',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Document\BucketBrowserOverwrite::class),
                        ]
                    ),
                ]
            ),
            'upload' => RouteConfig::getRouteConfig(
                'upload',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Document\Upload::class),
                ]
            ),
            'overwrite-content' => RouteConfig::getRouteConfig(
                'overwrite-content',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Document\OverwriteContent::class),
                ]
            ),
        ]
    )
];
