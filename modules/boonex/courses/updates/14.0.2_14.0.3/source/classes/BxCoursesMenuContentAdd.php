<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Courses Courses
 * @ingroup     UnaModules
 *
 * @{
 */

class BxCoursesMenuContentAdd extends BxTemplMenu
{
    protected $_sModule;
    protected $_oModule;
    
    protected $_iEntryPid;
    protected $_iNodeId;
    protected $_iUsage;

    public function __construct($aObject, $oTemplate = false)
    {
        $this->_sModule = 'bx_courses';
        $this->_oModule = BxDolModule::getInstance($this->_sModule);

        parent::__construct($aObject, $oTemplate);

        $this->_iEntryPid = 0;
        if(($iEntryPid = bx_get('entry_pid')) !== false)
            $this->_iEntryPid = (int)$iEntryPid;

        $this->_iNodeId = 0;
        if(($iNodeId = bx_get('node_id')) !== false)
            $this->_iNodeId = (int)$iNodeId;
        
        $this->_iUsage = 0;
        if(($iUsage = bx_get('usage')) !== false)
            $this->_iUsage = (int)$iUsage;
    }

    protected function getMenuItemsRaw()
    {
        $aResults = [];

        $oPermalink = BxDolPermalinks::getInstance();

        $aModules = $this->_oModule->_oConfig->getContentModules($this->_iUsage);
        foreach($aModules as $sModule) {
            $oModule = BxDolModule::getInstance($sModule);
            if(!$oModule)
                continue;

            $CNF = &$oModule->_oConfig->CNF;
            if(!isset($CNF['URI_ADD_ENTRY']))
                continue;

            $sUrl = $oPermalink->permalink('page.php?i=' . $CNF['URI_ADD_ENTRY'], [
                'context_pid' => $this->_iEntryPid,
                'context_nid' => $this->_iNodeId,
                'context_usage' => $this->_iUsage
            ]);

            $aResults[] = [
                'id' => $sModule, 
                'name' => $sModule, 
                'class' => '', 
                'link' => $sUrl, 
                'onclick' => "$(this).parents('.bx-popup-applied:visible:first').dolPopupHide()",
                'target' => '_self', 
                'title' => _t('_' . $sModule)
            ];
        }

        return $aResults;
    }
}

/** @} */
