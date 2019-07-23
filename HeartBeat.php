<?php
/**
 * HeartBeat Plugin for LimeSurvey
 * A heartbeat plugin for LimeSurvey to ensure the session is kept alive.
 *
 * @author Frederik Prijck <http://www.frederikprijck.net/>
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016-2019 Frederik Prijck <http://www.frederikprijck.net/>
 * @license MIT
 * @license https://opensource.org/licenses/MIT MIT License (MIT)
 * @version 1.0.5
 *
 */
class HeartBeat extends PluginBase {

    static protected $name = 'HeartBeat';
    static protected $description = 'Implement a heartbeat to ensure the session is kept alive.';

    protected $storage = 'DbStorage';
    protected $settings = array(
        'useSessionLifeTime'=>array(
            'type'=>'checkbox',
            'label' => 'Use 90% of the session lifetime as interval',
            'default' => true,
        ),
        'interval'=>array(
            'type'=>'int',
            'label' => 'Interval of the heartbeat (in seconds, minimum: 5s)',
            'default' => 120,
            'htmlOptions'=>array(
                'min'=>5,
            ),
        ),
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
        // Create the endpoint url, Using YII since API have issue with publicurl …
        $aOption['endpoint'] = Yii::app()->getController()->createUrl('plugins/direct', array('plugin' => get_class($this),'function' => 'beat'));

        // Get the settings : we are in survey, then need null, null for default value
        $interval = (int) $this->getInterval();
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
        if ($oEvent->get('target') == "HeartBeat") {
            $this->actionBeat();
        }
    }

    /*
     * Set default when show setting
     */
    public function getPluginSettings($getValues=true)
    {
        /* We fix the default before read the parent: the parent set the current to actual default */
        $this->settings['interval']['default'] = self::getDefaultInterval();
        $this->settings['useSessionLifeTime']['help'] = sprintf($this->_translate("Your actual session.gc_maxlifetime is %s seconds"),App()->session->getTimeout());
        $this->settings['interval']['label'] = $this->_translate("Interval of the heartbeat (in seconds, minimum: 5s)");
        $aPluginSettings = parent::getPluginSettings($getValues);

        $useSessionLifeTime = (boolean) $this->get('useSessionLifeTime', null, null, false);
        if (App()->request->isPostRequest) {
            $useSessionLifeTime = App()->request->getPost('useSessionLifeTime', $useSessionLifeTime);
            if(empty($useSessionLifeTime)) {
                if(empty(App()->request->getPost('interval'))) {
                    /* Saved in saveSettings, but call is done before */
                    $useSessionLifeTime = 1;
                }
            }
        }
        if($useSessionLifeTime) {
            $this->settings['interval']['help'] = sprintf($this->_translate("This value is not used, current value used : %s seconds."),$this->getDefaultInterval());
            $this->settings['interval']['htmlOptions']['placeholder'] = $this->getDefaultInterval();
        }
        return $aPluginSettings;
    }

    /**
     * Fix when saving
     * @param type $settings
     */
    public function saveSettings($settings)
    {
        if(empty($settings['useSessionLifeTime']) && empty($settings['interval'])) {
            $settings['useSessionLifeTime'] = 1;
            /* Add a JS warning */
            App()->setFlashMessage($this->_translate("HeartBeat setttings fixed to use 90% of the session lifetime as interval"),'warning');
        }
        if(empty($settings['useSessionLifeTime']) && intval($settings['interval']) < 5) {
            $settings['interval'] = 5;
            /* Add a JS warning */
            App()->setFlashMessage($this->_translate("HeartBeat setttings fixed to 5 seconds as interval"),'warning');
        }
        parent::saveSettings($settings);
    }

    /**
     * Default value for interval
     */
    private function getDefaultInterval() {
        return intval(App()->session->getTimeout()*0.9); // Set default to 90% of timeout
    }

    /*
    * Get the interval to be used by the heartbeat
    */
    private function getInterval(){
        // Get the boolean indicating to use the session life time or not
        $useSessionLifeTime = (boolean)$this->get('useSessionLifeTime', null, null, false);

        if($useSessionLifeTime == true){
            return $this->getDefaultInterval();
        }else{
            return (int)$this->get('interval', null, null, self::getDefaultInterval());
        }
    }

    /**
     * Action called in ajax to reset session time out
     */
    private function actionBeat()
    {
        // Access the session to make sure it stays alive
        $iSurveyId = Yii::app()->session['LEMsid'];
        // If LS debug is set : show the survey id
        if(App()->getConfig('debug')) {
            echo $iSurveyId;
        }
    }

    /**
     * @see parent::gT for LimeSurvey 3.0
     * @param string $sToTranslate The message that are being translated
     * @param string $sEscapeMode unescaped by default
     * @param string $sLanguage use current language if is null
     * @return string
     */
    private function _translate($sToTranslate, $sEscapeMode = 'unescaped', $sLanguage = null)
    {
        if(is_callable($this, 'gT')) {
            return $this->gT($sToTranslate,$sEscapeMode,$sLanguage);
        }
        return $sToTranslate;
    }
}
