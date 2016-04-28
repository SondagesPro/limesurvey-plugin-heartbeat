<?php

class HeartBeat extends \ls\pluginmanager\PluginBase {

    static protected $name = 'HeartBeat';
    static protected $description = 'Implement a heartbeat to ensure the session is kept alive.';

    protected $storage = 'DbStorage';
    protected $settings = array(
        'interval'=>array(
            'type'=>'int',
            'label' => 'Interval of the heartbeat (in seconds, minimum: 5s)',
            'default' => 120,
            'htmlOptions'=>array(
                'min'=>5
            )
        )
    );

    public function init() {
        $this->subscribe('beforeSurveyPage');
        $this->subscribe('newDirectRequest');
    }

    public function beforeSurveyPage()
    {
        // Get the js directory
        $jsPath=Yii::app()->assetManager->publish(dirname(__FILE__) . '/js/');
        // Register the js file
        Yii::app()->clientScript->registerScriptFile($jsPath.'/heartbeat.js');
        // Create the endpoint url
        $aOption['endpoint']=$this->api->createUrl('plugins/direct', array('plugin' => get_class($this),'function' => 'beat'));
        // Get the settings : we are in survey, then need nul,null for default value
        $interval = (int)$this->get('interval',null,null,self::getDefaultInterval());
        // Ensure the interval is not < 5 and convert to ms
        $aOption['interval']= ($interval < 5 ? 5 : $interval) * 1000;
        // Create the javascript code to inject in the page
        $heartBeatScript="heartBeat.beat(".ls_json_encode($aOption).");";
        // Inject js into the page
        Yii::app()->clientScript->registerScript("heartbeat", $heartBeatScript, CClientScript::POS_END);
    }

    public function newDirectRequest()
    {
        $oEvent = $this->event;
        $sAction=$oEvent->get('function');
        if ($oEvent->get('target') == "HeartBeat")
        {
            if($sAction == 'beat')
                $this->actionBeat();
            else
                throw new CHttpException(404,'Unknow action');
        }
    }
    /*
     * Set default when show setting
     */
    public function getPluginSettings($getValues=true)
    {
        $this->settings['interval']['default']=self::getDefaultInterval();
        $this->settings['interval']['help']="Your actual session.gc_maxlifetime is ".App()->session->getTimeout()."s";
        return parent::getPluginSettings($getValues);

    }

    /**
     * Default value for interval
     */
    private function getDefaultInterval()
    {
        return intval(App()->session->getTimeout()*0.9); // Set default to 90% of timeout
    }
    /**
     * Action called in ajax to reset session time out
     */
    private function actionBeat()
    {
        // Access the session to make sure it stays alive
        $iSurveyId=Yii::app()->session['LEMsid'];
        // If LS debug is set : show the survey id
        if(App()->getConfig('debug'))
        {
            echo $iSurveyId;
        }
    }
}
