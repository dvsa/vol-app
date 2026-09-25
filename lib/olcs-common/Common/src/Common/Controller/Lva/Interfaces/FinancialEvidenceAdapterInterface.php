<?php

/**
 * Financial Evidence Adapter Interface
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Controller\Lva\Interfaces;

/**
 * Financial Evidence Adapter Interface
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
interface FinancialEvidenceAdapterInterface extends AdapterInterface
{
    public function getData($id);

    public function getDocuments($id, $showAnalysisStatus = false);

    public function getUploadMetaData($file, $id);

    public function alterFormForLva($form);
}
