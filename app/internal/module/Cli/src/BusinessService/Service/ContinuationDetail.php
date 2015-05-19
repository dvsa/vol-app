<?php

/**
 * Continuation Detail Message
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\BusinessService\Service;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Common\Service\Entity\ContinuationDetailEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Helper\DocumentDispatchHelperService;
use Common\Service\File\File;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Continuation Detail Message
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationDetail implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function process(array $params)
    {
        $id = $params['id'];

        $continuationDetail = $this->getServiceLocator()->get('Entity\ContinuationDetail')
            ->getDetailsForProcessing($id);

        if ($continuationDetail['status']['id'] !== ContinuationDetailEntityService::STATUS_PRINTING) {
            return new Response(Response::TYPE_NO_OP);
        }

        $template = $this->getTemplateName($continuationDetail);

        if ($template === false) {
            return new Response(Response::TYPE_FAILED, [], 'Failed to determine document template');
        }

        try {
            $storedFile = $this->generateChecklist($continuationDetail, $template);

            if (!($storedFile instanceof File)) {
                return new Response(Response::TYPE_FAILED, [], 'Failed to generate file');
            }
        } catch (\Exception $ex) {
            return new Response(Response::TYPE_FAILED, [], 'Failed to generate file');
        }

        try {
            $documentId = $this->getServiceLocator()
                ->get('Helper\DocumentDispatch')
                ->process(
                    $storedFile,
                    [
                        'description' => 'Continuation checklist',
                        'filename' => $template . '.rtf',
                        'licence' => $continuationDetail['licence']['id'],
                        'category' => CategoryDataService::CATEGORY_LICENSING,
                        'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
                        'isReadOnly'  => true,
                        'isExternal'  => false
                    ],
                    // flag that this is a continuation document
                    DocumentDispatchHelperService::TYPE_CONTINUATION
                );

        } catch (\Exception $ex) {
            return new Response(Response::TYPE_FAILED, [], 'Failed to print document');
        }

        try {
            $this->getServiceLocator()->get('Entity\ContinuationDetail')
                ->processContinuationDetail($id, $documentId);
        } catch (\Exception $ex) {

            return new Response(
                Response::TYPE_FAILED,
                [],
                'Failed processing continuation detail: ' . $ex->getMessage()
            );
        }

        return new Response(Response::TYPE_SUCCESS);
    }

    protected function getTemplateName($continuationDetail)
    {
        $goodsOrPsv = $continuationDetail['licence']['goodsOrPsv']['id'];

        $template = '';

        if ($goodsOrPsv === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $template .= 'GV';
        } else {
            $template .= 'PSV';
        }

        $licenceType = $continuationDetail['licence']['licenceType']['id'];

        if ($licenceType === LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $template .= 'SR';
        }

        $template .= 'Checklist';
        return $template;
    }

    protected function generateChecklist($continuationDetail, $template)
    {
        // @TODO Not sure what we need as the bookmarks are being created as part of another story
        $query = ['licence' => $continuationDetail['licence']['id']];

        $storedFile = $this->getServiceLocator()
            ->get('Helper\DocumentGeneration')
            ->generateAndStore($template, 'Continuation checklist', $query);

        return $storedFile;
    }
}
