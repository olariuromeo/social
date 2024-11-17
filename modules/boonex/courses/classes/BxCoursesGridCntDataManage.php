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

class BxCoursesGridCntDataManage extends BxTemplGrid
{
    protected $_sModule;
    protected $_oModule;

    protected $_sPageUrl;
    protected $_iEntryId;
    protected $_iEntryPid;

    protected $_iNodeId;
    protected $_aNodeInfo;

    protected $_iLevelMax;

    public function __construct ($aOptions, $oTemplate = false)
    {
        $this->_sModule = 'bx_courses';
        $this->_oModule = BxDolModule::getInstance($this->_sModule);

        parent::__construct ($aOptions, $oTemplate);

        $CNF = &$this->_oModule->_oConfig->CNF;

        $this->_iEntryId = 0;
        if(($iEntryId = bx_get('entry_id')) !== false)
            $this->setEntryId($iEntryId);

        $this->setNodeId(($iNodeId = bx_get('parent_id')) !== false ? $iNodeId : 0);            

        $this->_iLevelMax = $this->_oModule->_oConfig->getContentLevelMax();
    }

    public function setEntryId($iEntryId)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        $this->_iEntryId = (int)$iEntryId;
        $this->_iEntryPid = ($oProfile = BxDolProfile::getInstanceByContentAndType($this->_iEntryId, $this->_sModule)) !== false ? $oProfile->id() : 0;

        $this->_sPageUrl = BxDolPermalinks::getInstance()->permalink($CNF['URL_MANAGE_STRUCTURE'], ['profile_id' => $this->_iEntryPid]);

        $this->_aQueryAppend['entry_id'] = $this->_iEntryId;
    }
    
    public function setNodeId($iNodeId)
    {
        $this->_iNodeId = (int)$iNodeId;

        $this->_aNodeInfo = [];
        if($this->_iNodeId)
            $this->_aNodeInfo = $this->_oModule->_oDb->getContentNodes(['sample' => 'id_full', 'id' => $this->_iNodeId]);

        $this->_aQueryAppend['parent_id'] = $this->_iNodeId;
    }

    public function getCode ($isDisplayHeader = true)
    {
        if(empty($this->_aNodeInfo) || !is_array($this->_aNodeInfo) || $this->_aNodeInfo['level'] != $this->_iLevelMax)
            return '';

        return parent::getCode($isDisplayHeader);
    }

    public function getCodeAPI($bForceReturn = false)
    {
        if(empty($this->_aNodeInfo) || !is_array($this->_aNodeInfo) || $this->_aNodeInfo['level'] != $this->_iLevelMax)
            return [];

        return parent::getCodeAPI($bForceReturn);
    }    

    protected function _getCellContentId($mixedValue, $sKey, $aField, $aRow)
    {
        $iContentId = (int)$mixedValue;
        $mixedValue = '';

        $sModule = $aRow['content_type'];
        if(($sMethod = 'get_title') && bx_is_srv($sModule, $sMethod))
            $mixedValue = bx_srv($sModule, $sMethod, [$iContentId]);
        if(!$mixedValue && ($sMethod = 'get_text') &&  bx_is_srv($sModule, $sMethod))
            $mixedValue = bx_srv($sModule, $sMethod, [$iContentId]);
        if(!$mixedValue)
            $mixedValue = _t('_undefined');

        if(($sMethod = 'get_link') && bx_is_srv($sModule, $sMethod))
            $mixedValue = $this->_oModule->_oTemplate->parseHtmlByName('name_link.html', [
                'href' => bx_srv($sModule, $sMethod, [$iContentId]),
                'title' => bx_html_attribute($mixedValue),
                'content' => $mixedValue
            ]);

        return parent::_getCellDefault($mixedValue, $sKey, $aField, $aRow);
    }

    protected function _getCellContentType($mixedValue, $sKey, $aField, $aRow)
    {
        return parent::_getCellDefault(_t('_' . $mixedValue), $sKey, $aField, $aRow);
    }

    protected function _getCellUsage($mixedValue, $sKey, $aField, $aRow)
    {
        return parent::_getCellDefault(_t('_bx_courses_txt_data_usage_' . $mixedValue), $sKey, $aField, $aRow);
    }

    protected function _getCellAdded($mixedValue, $sKey, $aField, $aRow)
    {
        if($this->_bIsApi)
            return ['type' => 'time', 'data' => $mixedValue];

        return parent::_getCellDefault(bx_time_js($mixedValue), $sKey, $aField, $aRow);
    }

    protected function _getActionBack($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = [])
    {
        if(empty($this->_aNodeInfo))
            return $this->_bIsApi ? [] : '';

        $sUrl = $this->_sPageUrl;
        if(!empty($this->_aNodeInfo['parent_id']))
            $sUrl = bx_append_url_params($sUrl, ['parent_id' => $this->_aNodeInfo['parent_id']]);
        
        if($this->_bIsApi)
            return array_merge($a, ['name' => $sKey, 'type' => 'link', 'link' => bx_api_get_relative_url($sUrl)]);

        $a['attr'] = array_merge($a['attr'], [
            "onclick" => "window.open('" . $sUrl . "', '_self');"
    	]);

    	return $this->_getActionDefault ($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _getActionAddSt($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = [])
    {
        if($this->_bIsApi)
            return array_merge($a, ['name' => $sKey, 'type' => 'menu', 'action' => $sKey, 'values' => $this->_getValues(BX_COURSES_CND_USAGE_ST)]);

        $CNF = &$this->_oModule->_oConfig->CNF;

        $a['attr'] = array_merge($a['attr'], [
            "onclick" => "$(this).off('click'); bx_menu_popup('" . $CNF['OBJECT_MENU_CONTENT_ADD'] . "', this, {removeOnClose: 1}, {entry_pid: " . $this->_iEntryPid . ", node_id: " . $this->_iNodeId . ", usage:0});"
    	]);
    	return $this->_getActionDefault ($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _getActionAddAt($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = [])
    {
        if($this->_bIsApi)
            return array_merge($a, ['name' => $sKey, 'type' => 'menu', 'action' => $sKey, 'values' => $this->_getValues(BX_COURSES_CND_USAGE_AT)]);

        $CNF = &$this->_oModule->_oConfig->CNF;

        $a['attr'] = array_merge($a['attr'], [
            "onclick" => "$(this).off('click'); bx_menu_popup('" . $CNF['OBJECT_MENU_CONTENT_ADD'] . "', this, {removeOnClose: 1}, {entry_pid: " . $this->_iEntryPid . ", node_id: " . $this->_iNodeId . ", usage:1});"
    	]);
    	return $this->_getActionDefault ($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _getActionEdit($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = [])
    {
        $oModule = BxDolModule::getInstance($aRow['content_type']);
        if(!$oModule)
            return '';
        
        $CNF_MODULE = &$oModule->_oConfig->CNF;
        if(!isset($CNF_MODULE['URI_EDIT_ENTRY']))
            return '';

        $sUrl = bx_absolute_url(BxDolPermalinks::getInstance()->permalink('page.php?i=' . $CNF_MODULE['URI_EDIT_ENTRY'] . '&id=' . $aRow['content_id']));

        if($this->_bIsApi)
            return array_merge($a, [
                'name' => $sKey, 
                'type' => 'modal', 
                'callback' => $aRow['content_type'] . '/entity_edit/&params[]=' . $aRow['content_id'],
                'content_type' => $aRow['content_type']
            ]);

    	$a['attr'] = array_merge($a['attr'], [
            "onclick" => "window.open('" . $sUrl . "', '_self');"
    	]);
    	return $this->_getActionDefault ($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _delete($mixedId)
    {
        $aData = $this->_oModule->_oDb->getContentData(['sample' => 'id', 'id' => (int)$mixedId]);
        if(!empty($aData) && is_array($aData)) {
            $sModule = $aData['content_type'];
            $sMethod = 'delete_entity';
            if(bx_is_srv($sModule, $sMethod) && bx_srv($sModule, $sMethod, [$aData['content_id']]) != '')
                return false;
        }

        return parent::_delete($mixedId) !== false;
    }

    protected function _getDataSql($sFilter, $sOrderField, $sOrderDir, $iStart, $iPerPage)
    {
        if(!$this->_iEntryId)
            return [];

        $this->_aOptions['source'] .= $this->_oModule->_oDb->prepareAsString(" AND `entry_id`=? AND `node_id`=?", $this->_iEntryId, $this->_iNodeId);

        return parent::_getDataSql($sFilter, $sOrderField, $sOrderDir, $iStart, $iPerPage);
    }

    protected function _getIds()
    {
        $aIds = bx_get('ids');
        if(!$aIds || !is_array($aIds)) {
            $iId = (int)bx_get('id');
            if(!$iId) 
                return false;

            $aIds = [$iId];
        }

        return $aIds;
    }

    protected function _getValues($iUsage)
    {
        $aValues = [];
        $oPermalink = BxDolPermalinks::getInstance();

        $aModules = $this->_oModule->_oConfig->getContentModules($iUsage);
        foreach($aModules as $sModule) {
            $oModule = BxDolModule::getInstance($sModule);
            if(!$oModule)
                continue;

            $CNF = &$oModule->_oConfig->CNF;
            if(!isset($CNF['URI_ADD_ENTRY']))
                continue;

            $aParams = [
                'context_pid' => $this->_iEntryPid,
                'context_nid' => $this->_iNodeId,
                'context_usage' => $iUsage
            ];

            $aValues[] = [
                'key' => $sModule, 
                'title' => _t('_' . $sModule),
                'link' => bx_api_get_relative_url(BX_DOL_URL_ROOT . $oPermalink->permalink('page.php?i=' . $CNF['URI_ADD_ENTRY'], $aParams)), 
                'callback' => $sModule . '/entity_create/&params[]=' . json_encode($aParams)
            ];
        }

        return $aValues;
    }
}

/** @} */
