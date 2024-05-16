<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

/**
 * Informer representation.
 * @see BxDolInformer
 */
class BxBaseInformer extends BxDolInformer
{
    protected $_bJsCssAdded = false;

    protected $_oTemplate;

    protected $_aMapType2Class = array(
        BX_INFORMER_ALERT => 'bx-informer-msg-alert',
        BX_INFORMER_INFO => 'bx-informer-msg-info',
        BX_INFORMER_ERROR => 'bx-informer-msg-error',
    );

    public function __construct ($oTemplate)
    {
        if ($oTemplate)
            $this->_oTemplate = $oTemplate;
        else
            $this->_oTemplate = BxDolTemplate::getInstance();

        parent::__construct ();
    }

    /**
     * Display Informer.
     */
    public function display ()
    {
    	if(!$this->_bEnabled)
            return '';

        if (!$this->_aMessages)
            return '';

        $aTmplVarsMessages = [];
        foreach ($this->_aMessages as $sId => $a) {
            $a['class'] = $this->_aMapType2Class[$a['type']];

            $aTmplVarsMessages[] = $a;
        }
        
        if (bx_is_api()){
            return $aTmplVarsMessages;
        }

        $this->_addJsCss();
        return $this->_oTemplate->parseHtmlByName('informer.html', [
            'bx_repeat:messages' => $aTmplVarsMessages
        ]);
    }

    /**
     * Add css/js files which are needed for display and functionality.
     */
    protected function _addJsCss()
    {
        if ($this->_bJsCssAdded)
            return;

        $this->_oTemplate->addCss(['informer.css']);

        $this->_bJsCssAdded = true;
    }
}

/** @} */
