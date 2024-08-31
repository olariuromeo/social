<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    BaseGeneral Base classes for modules
 * @ingroup     UnaModules
 *
 * @{
 */

class BxBaseModGeneralMenuSnippetMeta extends BxTemplMenuUnitMeta
{
    protected $_bShowZeros;

    protected $_sModule;
    protected $_oModule;

    protected $_iContentId;
    protected $_aContentInfo;

    public function __construct($aObject, $oTemplate = false)
    {
        $this->_oModule = BxDolModule::getInstance($this->_sModule);
        if(empty($oTemplate))
            $oTemplate = $this->_oModule->_oTemplate;

        parent::__construct($aObject, $oTemplate);

        $this->_sStylePrefix = 'bx-base-general-unit-meta';
        $this->_bShowZeros = false;
    }

    public function getCode()
    {
        if(empty($this->_aContentInfo) || !is_array($this->_aContentInfo))
            return '';

        return parent::getCode();
    }

    public function setContentId($iContentId)
    {
        $this->_iButtons = 0;
        $this->_iContentId = (int)$iContentId;

        $this->_aContentInfo = $this->_oModule->_oDb->getContentInfoById($this->_iContentId);
        if($this->_aContentInfo)
            $this->addMarkers(array('content_id' => (int)$this->_iContentId));
    }

    protected function _getMenuItemAuthor($aItem)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'profile', [
                'data' => BxDolProfile::getData($this->_aContentInfo[$CNF['FIELD_AUTHOR']])
            ]);

        $oProfile = BxDolProfile::getInstanceMagic($this->_aContentInfo[$CNF['FIELD_AUTHOR']]);

        return $this->getUnitMetaItemLink($oProfile->getDisplayName(), array(
            'href' => $oProfile->getUrl(),
            'class' => 'bx-base-text-unit-author'
        ));
    }

    protected function _getMenuItemDate($aItem)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;
        
        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'time', [
                'title' => $this->_aContentInfo[$CNF['FIELD_ADDED']]
            ]);

        if ($aItem['icon'] == '')
            return $this->getUnitMetaItemText(bx_time_js($this->_aContentInfo[$CNF['FIELD_ADDED']], BX_FORMAT_DATE));
        else
            return $this->getUnitMetaItemExtended(bx_time_js($this->_aContentInfo[$CNF['FIELD_ADDED']], BX_FORMAT_DATE), $aItem['icon'], '');
    }

    protected function _getMenuItemCategory($aItem)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_CATEGORY']) || empty($CNF['FIELD_CATEGORY']) || empty($this->_aContentInfo[$CNF['FIELD_CATEGORY']]))
            return false;

        $oCategory = BxDolCategory::getObjectInstance($CNF['OBJECT_CATEGORY']);
        if(!$oCategory)
            return false;

        $sTitle = $oCategory->getCategoryTitle($this->_aContentInfo[$CNF['FIELD_CATEGORY']]);
        $sLink = $oCategory->getCategoryUrl($this->_aContentInfo[$CNF['FIELD_CATEGORY']]);

        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'text', [
                'title' => $sTitle,
                'link' => $sLink
            ]);

        return $this->getUnitMetaItemLink($sTitle, [
            'href' => $sLink
        ]);
    }

    protected function _getMenuItemTags($aItem)
    {
        if($this->_bIsApi) //--- API: Isn't supported
            return false;

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_METATAGS']))
            return false;

        $oMetatags = BxDolMetatags::getObjectInstance($CNF['OBJECT_METATAGS']);
        if(!$oMetatags || !$oMetatags->keywordsIsEnabled())
            return false;

        return $this->getUnitMetaItemCustom($oMetatags->getKeywordsList($this->_aContentInfo[$CNF['FIELD_ID']], 3));
    }

    protected function _getMenuItemViews($aItem, $aParams = [])
    {
        $bShowAsObject = isset($aParams['show_as_object']) && (bool)$aParams['show_as_object'] === true;

        if($bShowAsObject && !$this->_bIsApi) //--- API: Object based views aren't supported
            return $this->_getMenuItemViewsObject($aItem, $aParams);

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['FIELD_VIEWS']) || (empty($this->_aContentInfo[$CNF['FIELD_VIEWS']]) && !$this->_bShowZeros))
            return false;

        $sTitle = _t('_view_n_views', $this->_aContentInfo[$CNF['FIELD_VIEWS']]);

        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'text', [
                'title' => $sTitle
            ]);

        return $this->getUnitMetaItemText($sTitle);
    }
    protected function _getMenuItemViewsObject($aItem, $aParams = []) 
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_VIEWS']))
            return false;

        $oObject = BxDolView::getObjectInstance($CNF['OBJECT_VIEWS'], $this->_aContentInfo[$CNF['FIELD_ID']]);
        if(!$oObject || !$oObject->isEnabled())
            return false;

        $aObjectOptions = [
            'show_counter' => true
        ];
        if(!empty($aParams['object_options']) && is_array($aParams['object_options']))
            $aObjectOptions = array_merge($aObjectOptions, $aParams['object_options']);

        if($this->_bIsApi)
            return false;

        return $this->getUnitMetaItemCustom($oObject->getElementInline($aObjectOptions));
    }

    protected function _getMenuItemVotes($aItem, $aParams = [])
    {
        $bShowAsObject = isset($aParams['show_as_object']) && (bool)$aParams['show_as_object'] === true;

        if($bShowAsObject || $this->_bIsApi)  //--- API: Object base votes are used by default
            return $this->_getMenuItemVotesObject($aItem, $aParams);

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['FIELD_VOTES']) || (empty($this->_aContentInfo[$CNF['FIELD_VOTES']]) && !$this->_bShowZeros))
            return false;

        $sTitle = _t('_vote_n_votes', $this->_aContentInfo[$CNF['FIELD_VOTES']]);

        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'text', [
                'title' => $sTitle
            ]);

        return $this->getUnitMetaItemText($sTitle);
    }

    protected function _getMenuItemVotesObject($aItem, $aParams = [])
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_VOTES']))
            return false;

        $oObject = BxDolVote::getObjectInstance($CNF['OBJECT_VOTES'], $this->_aContentInfo[$CNF['FIELD_ID']]);
        if(!$oObject || !$oObject->isEnabled())
            return false;

        $aObjectOptions = [
            'show_counter' => true
        ];
        if(!empty($aParams['object_options']) && is_array($aParams['object_options']))
            $aObjectOptions = array_merge($aObjectOptions, $aParams['object_options']);

        if($this->_bIsApi)
            return $this->_getMenuItemElementAPI($aItem, $oObject->getElementApi($aObjectOptions));

        return $this->getUnitMetaItemCustom($oObject->getElementInline($aObjectOptions));
    }

    protected function _getMenuItemRating($aItem)
    {
        if($this->_bIsApi) //--- API: Isn't supported
            return false;

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_VOTES_STARS']))
            return false;

        $oVotes = BxDolVote::getObjectInstance($CNF['OBJECT_VOTES_STARS'], $this->_aContentInfo[$CNF['FIELD_ID']]);
        if(!$oVotes)
            return false;

        return $this->getUnitMetaItemCustom($oVotes->getElementInline(array('show_counter' => true)));
    }
    
    protected function _getMenuItemReactions($aItem)
    {
        if($this->_bIsApi) //--- API: Isn't supported
            return false;

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_REACTIONS']))
            return false;

        $oVotes = BxDolVote::getObjectInstance($CNF['OBJECT_REACTIONS'], $this->_aContentInfo[$CNF['FIELD_ID']]);
        if(!$oVotes)
            return false;

        return $this->getUnitMetaItemCustom($oVotes->getElementInline(array('show_counter' => false)));
    }

    protected function _getMenuItemScore($aItem, $aParams = [])
    {
        $bShowAsObject = isset($aParams['show_as_object']) && (bool)$aParams['show_as_object'] === true;

        if($bShowAsObject || $this->_bIsApi)  //--- API: Object base scores are used by default
            return $this->_getMenuItemScoreObject($aItem, $aParams);

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['FIELD_SCORE']) || (empty($this->_aContentInfo[$CNF['FIELD_SCORE']]) && !$this->_bShowZeros))
            return false;

        $sTitle = _t('_sys_score_n_score', $this->_aContentInfo[$CNF['FIELD_SCORE']]);

        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'text', [
                'title' => $sTitle
            ]);

        return $this->getUnitMetaItemText($sTitle);
    }

    protected function _getMenuItemScoreObject($aItem, $aParams = [])
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_SCORES']))
            return false;

        $oObject = BxDolScore::getObjectInstance($CNF['OBJECT_SCORES'], $this->_aContentInfo[$CNF['FIELD_ID']], true, $this->_oModule->_oTemplate);
        if(!$oObject || !$oObject->isEnabled())
            return false;

        $aObjectOptions = [
            'show_counter' => true
        ];
        if(!empty($aParams['object_options']) && is_array($aParams['object_options']))
            $aObjectOptions = array_merge($aObjectOptions, $aParams['object_options']);

        if($this->_bIsApi)
            return $this->_getMenuItemElementAPI([
                'id' => $aItem['id'],
                'name' => 'scores',
            ], $oObject->getElementApi($aObjectOptions));

        return $this->getUnitMetaItemCustom($oObject->getElementInline($aObjectOptions));
    }

    protected function _getMenuItemComments($aItem, $aParams = [])
    {
        $bShowAsObject = isset($aParams['show_as_object']) && (bool)$aParams['show_as_object'] === true;

        if($bShowAsObject || $this->_bIsApi)  //--- API: Object base comments are used by default
            return $this->_getMenuItemCommentsObject($aItem, $aParams);

        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_COMMENTS']) || empty($CNF['FIELD_COMMENTS']) || (empty($this->_aContentInfo[$CNF['FIELD_COMMENTS']]) && !$this->_bShowZeros))
            return false;

        $oComments = BxDolCmts::getObjectInstance($CNF['OBJECT_COMMENTS'], $this->_aContentInfo[$CNF['FIELD_ID']]);
        if(!$oComments || !$oComments->isEnabled())
            return false;
        
        $sTitle = _t('_cmt_txt_n_comments', $oComments->getCommentsCountAll(0, true));
        $sLink =  $oComments->getListUrl();

        if($this->_bIsApi)
            return $this->_getMenuItemAPI($aItem, 'text', [
                'title' => $sTitle,
                'link' => $sLink
            ]);

        return $this->getUnitMetaItemLink($sTitle, [
            'href' => $sLink
        ]);
    }

    protected function _getMenuItemCommentsObject($aItem, $aParams = [])
    {
        $CNF = &$this->_oModule->_oConfig->CNF;

        if(empty($CNF['OBJECT_COMMENTS']))
            return false;

        $oObject = BxDolCmts::getObjectInstance($CNF['OBJECT_COMMENTS'], $this->_aContentInfo[$CNF['FIELD_ID']], true, $this->_oModule->_oTemplate);
        if(!$oObject || !$oObject->isEnabled())
            return false;

        $aObjectOptions = [
            'show_counter' => true
        ];
        if(!empty($aParams['object_options']) && is_array($aParams['object_options']))
            $aObjectOptions = array_merge($aObjectOptions, $aParams['object_options']);

        if($this->_bIsApi)
            return $this->_getMenuItemElementAPI($aItem, $oObject->getElementApi($aObjectOptions));

        return $this->getUnitMetaItemCustom($oObject->getElementInline($aObjectOptions));
    }

    protected function _getMenuItemDefault($aItem)
    {
        $sResult = false;

        $a = [
            'res' => &$sResult, 
            'menu' => $this->_sObject, 
            'menu_object' => $this, 
            'item' => $aItem,
            'module' => $this->_sModule,
            'content_id' => $this->_iContentId,
            'content_data' => $this->_aContentInfo,
        ];
        
        /**
         * @hooks
         * @hookdef hook-bx_base_general-menu_custom_item '{module_name}', 'menu_custom_item' - hook to override menu item
         * - $unit_name - module name
         * - $action - equals `menu_custom_item`
         * - $object_id - not used
         * - $sender_id - not used
         * - $extra_params - array of additional params with the following array keys:
         *      - `res` - [string] by ref, menu item code, can be overridden in hook processing
         *      - `menu` - [string] menu name
         *      - `menu_object` - [object] an instance of menu, @see BxDolMenu
         *      - `item` - [array] menu item array as key&value pairs
         *      - `module` - [string] module name
         *      - `content_id` - [int] content id
         *      - `content_data` - [array] content info array as key&value pairs
         * @hook @ref hook-bx_base_general-menu_custom_item
         */
        bx_alert($this->_sModule, 'menu_custom_item', 0, 0, $a);
        
        /**
         * @hooks
         * @hookdef hook-menu-menu_custom_item 'menu', 'menu_custom_item' - hook to override menu item
         * It's equivalent to @ref hook-bx_base_general-menu_custom_item
         * @hook @ref hook-menu-menu_custom_item
         */
        bx_alert('menu', 'menu_custom_item', 0, 0, $a);

        if (false !== $sResult)
            return $sResult;

        return parent::_getMenuItemDefault($aItem);
    }
}

/** @} */
