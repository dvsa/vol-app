<?php

namespace Common\Service\Helper;

use Common\Service\Table\TableBuilder;
use Laminas\Http\Response;

/**
 * Miscellaneous response helper service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ResponseHelperService
{
    protected static $ignoreColumnsByType = ['ActionLinks'];

    protected static $ignoreColumnsByName = ['action'];

    /**
     * Convert table to CSV
     *
     * @param Response     $response Response object
     * @param TableBuilder $table    table
     * @param string       $fileName Name of file
     *
     * @return Response
     */
    public function tableToCsv(Response $response, TableBuilder $table, $fileName)
    {
        foreach ($table->getColumns() as $column) {
            $name = ($column['name'] ?? null);
            if (in_array($name, self::$ignoreColumnsByName, true)) {
                $table->removeColumn($name);

                continue;
            }

            $type = ($column['type'] ?? null);
            if (in_array($type, self::$ignoreColumnsByType, true)) {
                $table->removeColumn($name);

                continue;
            }
        }

        $table->setContentType(TableBuilder::CONTENT_TYPE_CSV);

        $body = $table->render();

        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'text/csv')
            ->addHeaderLine('Content-Disposition', sprintf('attachment; filename="%s.csv"', $fileName))
            ->addHeaderLine('Content-Length', strlen($body));

        $response->setContent($body);

        return $response;
    }
}
