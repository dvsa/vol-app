<?php
/**
*	Journey View Helper
*
*	@author		Jess Rowbottom <jess@wrenthorpe.net>
*/
namespace SelfServe\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Journey
    extends AbstractHelper
    implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

	public function __invoke ($stage,$completionStatus,$applicationId)
	{
        $this->renderer=$this->getView();

        $config=$this->getServiceLocator()->getServiceLocator()->get('config');

        $journeyConfig=Array();
        foreach($config['journey'] as $applicationKey=>$applicationStage) {

            $thisCompletionStatus=$completionStatus['section'.$applicationStage['dbkey'].'Status'];
            if ( $thisCompletionStatus == "" ) {
                $thisCompletionStatus=0;
            }

            $journeyItem=Array(
                'title' => $applicationStage['label'],
                'link' => $this->view->url("selfserve/".$applicationStage['route'],
                                        array('applicationId'=>$applicationId,
                                                'step'=>(isset($applicationStage['step'])?$applicationStage['step']:""))),
                'status' => $config['journeyCompletionStatus'][$thisCompletionStatus]
            );

            if ( $stage == $applicationKey ) {
                $journeyItem['status']="current";
            }

            $journeyConfig[$applicationKey]=$journeyItem;
        }

        $output=$this->renderer->render(
                    'self-serve/journey/header.phtml',
                    array(
                        'applicationProcess' => $journeyConfig
                        )
                );

        $journeyConfig=$config['journey'];

        return $output;
    }

}

?>
