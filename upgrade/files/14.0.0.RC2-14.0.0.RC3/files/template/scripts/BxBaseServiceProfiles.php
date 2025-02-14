<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

/**
 * System service for profiles handling functionality.
 */
class BxBaseServiceProfiles extends BxDol
{
    protected $_bIsApi;
    public function __construct()
    {
        parent::__construct();

        $this->_bIsApi = bx_is_api();
    }

    public function serviceGetProfileTypes()
    {
        $aTypes = array(
			'' => _t('_Select_one')
        );

        $aModules = $this->_getProfilesModules();
        foreach($aModules as $aModule) {
			if(empty($aModule) || !is_array($aModule))
				continue;

            $sTitleKey = '_' . $aModule['name'];
            $sTitle = _t($sTitleKey);

            $aTypes[$aModule['name']] = !empty($sTitle) && $sTitle != $sTitleKey ? $sTitle : $aModule['title'];
		}

        return $aTypes;
    }

    public function serviceProfileAvatar ($iProfileId = 0)
    {
        if (!$iProfileId && !($iProfileId = bx_get_logged_profile_id()))
            return '';

        $oProfile = BxDolProfile::getInstance($iProfileId);
        if(!$oProfile)
            return '';

        if(bx_is_api())
            return [
                bx_api_get_block ('profile_avatar', $oProfile->getData())
            ];

        $oTemplate = BxDolTemplate::getInstance();

        $sSwitcher = '';
        $aSwitcher = bx_srv('system', 'account_profile_switcher', array(false, null, '', true), 'TemplServiceProfiles');
        if($aSwitcher !== false)
            $sSwitcher = BxTemplFunctions::getInstance()->popupBox('bx-profile-switcher', _t('_sys_txt_switch_profiles'), $oTemplate->parseHtmlByName('profile_avatar_switcher.html', array(
                'profile_switcher' => $aSwitcher['content'],
                'bx_if:multiple_profiles_mode' => array(
                    'condition' => empty($aSwitcher['content']) || BxDolAccount::isAllowedCreateMultiple($oProfile->id()),
                    'content' => array(
                        'url_switch_profile' => BxDolPermalinks::getInstance()->permalink('page.php?i=account-profile-switcher')
                    )
                )
            )), true);
        $bSwitcher = !empty($sSwitcher);        
        $sSwitcherUrl = $bSwitcher ? 'javascript:void(0)' : $sUrl;
        $sSwitcherOnclick = $bSwitcher ? "javascript:$('#bx-profile-switcher').dolPopup({});" : "";

        $sDisplayName = $oProfile->getDisplayName();
        $sUrl = $oProfile->getUrl();

        $oAcl = BxDolAcl::getInstance();
        $aAcl = $oAcl->getMemberMembershipInfo($iProfileId);
        $aAclInfo = $oAcl->getMembershipInfo($aAcl['id']);
        list($sIcon, $sIconUrl, $sIconA, $sIconHtml) = $this->_getIcon($aAclInfo['icon']);

        $aVars = array(
            'profile_id' => $oProfile->id(),
            'profile_url' => $sUrl,
            'profile_edit_url' => $oProfile->getEditUrl(),
            'profile_title' => $sDisplayName,
            'profile_title_attr' => bx_html_attribute($sDisplayName),
            'profile_ava_url' => $oProfile->getAvatar(),
            'profile_unit' => $oProfile->getUnit(0, array('template' => array(
                'name' => 'unit_wo_info_links',
                'size' => 'thumb'
            ))),
            'profile_acl_title' => _t($aAclInfo['name']),
            'bx_if:image' => array (
                'condition' => (bool)$sIconUrl,
                'content' => array('icon_url' => $sIconUrl),
            ),
            'bx_if:image_inline' => array (
                'condition' => false,
                'content' => array('image' => ''),
            ),
            'bx_if:icon' => array (
                'condition' => (bool)$sIcon,
                'content' => array('icon' => $sIcon),
            ),
            'bx_if:icon-a' => array (
                'condition' => (bool)$sIconA,
                'content' => array('icon-a' => $sIconA),
            ),
            'bx_if:icon-html' => array (
                'condition' => (bool)$sIconHtml,
                'content' => array('icon' => $sIconHtml),
            ),
            'switcher_url' => $sSwitcherUrl,
            'switcher_onclick' => $sSwitcherOnclick,
            'bx_if:show_switcher_icon' => array(
                'condition' => $bSwitcher,
                'content' => array(
                    'switcher_url' => $sSwitcherUrl,
                    'switcher_onclick' => $bSwitcher ? "javascript:$('#bx-profile-switcher').dolPopup({});" : "",
                )
            ),
            'switcher' => $sSwitcher
        );

        return $oTemplate->parseHtmlByName('profile_avatar.html', $aVars);
    }

    public function serviceProfileMenu ($iProfileId = 0)
    {
        if(bx_is_api()){
            $oMenu = BxDolMenu::getObjectInstance('sys_site');
                return [
                    bx_api_get_block ('profile_menu', $oMenu->getCodeAPI())
                ];
        }
        if(($oMenu = BxDolMenu::getObjectInstance('sys_profile_stats')) !== false) {
            return $oMenu->getCode();
        }
        else
            return '';
    }

    public function serviceProfileFollowings ($iProfileId = 0)
    {
        if(($oMenu = BxDolMenu::getObjectInstance('sys_profile_followings')) !== false)
            return $oMenu->getCode();
        else
            return '';
    }
    
    public function serviceBefriend ($sParams)
    {
        $aParams = json_decode($sParams, true);
        $iProfileId = $aParams['profile_id'];
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_friends'); 
        $oProfile = BxDolProfile::getInstance($iProfileId);
        if ($aParams['action'] == 'info'){
            return ['profile' => BxDolProfile::getData($iProfileId), 'result' => $oConnection->isConnected((int)bx_get_logged_profile_id(), $iProfileId, true)];
        }
       
        if ($oProfile && $oConnection){
            if ($oConnection->isConnected((int)bx_get_logged_profile_id(), $iProfileId, true)){
                 return ['profile' => BxDolProfile::getData($iProfileId), 'result' => false];
            }
            else{
                $oConnection->addConnection((int)bx_get_logged_profile_id(), $iProfileId);
                $oConnection->addConnection($iProfileId, (int)bx_get_logged_profile_id());
                $oProfile = BxDolProfile::getInstance($iProfileId);
                return ['profile' => BxDolProfile::getData($iProfileId), 'result' => true];
            }
        }
    }

    public function serviceProfileStats ($iProfileId = 0)
    {
        if (!$iProfileId && !($iProfileId = bx_get_logged_profile_id()))
            return '';

        $oProfile = BxDolProfile::getInstance($iProfileId);
        if(!$oProfile)
            return '';

        $oMenu = BxDolMenu::getObjectInstance('sys_profile_stats');
        if(!$oMenu)
            return '';

        if(bx_is_api())
            return [
                bx_api_get_block ('profile_avatar', $oProfile->getData(), ['id' => 1]),
                bx_api_get_block ('profile_menu', $oMenu->getCodeAPI(), ['id' => 2])
            ];

        $oAcl = BxDolAcl::getInstance();
        $aAcl = $oAcl->getMemberMembershipInfo($iProfileId);
        $aAclInfo = $oAcl->getMembershipInfo($aAcl['id']);

        list ($sIcon, $sIconUrl, $sIconA, $sIconHtml) = $this->_getIcon($aAclInfo['icon']);

        $aVars = array(
            'profile_id' => $oProfile->id(),
            'profile_url' => $oProfile->getUrl(),
            'profile_edit_url' => $oProfile->getEditUrl(),
            'profile_title' => $oProfile->getDisplayName(),
            'profile_title_attr' => bx_html_attribute($oProfile->getDisplayName()),
            'profile_ava_url' => $oProfile->getAvatar(),
            'profile_unit' => $oProfile->getUnit(0, array('template' => array(
                'name' => 'unit_wo_info',
                'size' => 'ava'
            ))),
            'profile_acl_title' => _t($aAclInfo['name']),
            'menu' => $oMenu->getCode(),
        );
		
        $aVars['bx_if:image'] = array (
            'condition' => (bool)$sIconUrl,
            'content' => array('icon_url' => $sIconUrl),
        );
        $aVars['bx_if:image_inline'] = array (
            'condition' => false,
            'content' => array('image' => ''),
        );
        $aVars['bx_if:icon'] = array (
            'condition' => (bool)$sIcon,
            'content' => array('icon' => $sIcon),
        );
        $aVars['bx_if:icon-html'] = array (
            'condition' => (bool)$sIconHtml,
            'content' => array('icon' => $sIconHtml),
        );
        $aVars['bx_if:icon-a'] = array (
            'condition' => (bool)$sIconA,
            'content' => array('icon-a' => $sIconA),
        );

        return BxDolTemplate::getInstance()->parseHtmlByName('profile_stats.html', $aVars);
    }

    public function serviceGetMenuAddonProfileEdit($iProfileId = 0, $sCaption = '')
    {
        /**
         * Disabled.
         */
        return '';

    	if(empty($sCaption))
            $sCaption = _t('_Edit');

        $oProfile = BxDolProfile::getInstance($iProfileId);
        if(!$oProfile)
            return '';

        $sModule = $oProfile->getModule();
        $sMethod = 'profile_edit_url';
        if(!BxDolRequest::serviceExists($sModule, $sMethod))
            return '';

        return BxDolTemplate::getInstance()->parseLink(BxDolService::call($sModule, $sMethod, array($oProfile->getContentId())), $sCaption);
    }
    
    public function serviceGetMenuAddonProfileConnections($iProfileId = 0)
    {
        $oProfile = BxDolProfile::getInstance($iProfileId);
        if(!$oProfile)
            return '';

        $iCount = 0;

        //--- Add friend requests.
        $iCount += (int)bx_srv('system', 'get_unconfirmed_connections_num', ['sys_profiles_friends'], 'TemplServiceConnections');

        //--- Add subscriptions.
        $iCount += (int)bx_srv('system', 'get_connected_content_num', ['sys_profiles_subscriptions'], 'TemplServiceConnections');

        //--- Add subscribed me.
        $iCount += (int)bx_srv('system', 'get_connected_initiators_num', ['sys_profiles_subscriptions'], 'TemplServiceConnections');

        return $iCount > 0 ? $iCount : '';
    }   

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-profiles Profiles
     * @subsubsection bx_system_general-profile_membership profile_membership
     * 
     * @code bx_srv('system', 'profile_membership', [2], 'TemplServiceProfiles'); @endcode
     * @code {{~system:profile_membership:TemplServiceProfiles[2]~}} @endcode
     * 
     * Get membership level for specified profile.
     * @param $iProfileId profile ID
     * 
     * @see BxBaseServiceProfiles::serviceProfileMembership
     */
    /** 
     * @ref bx_system_general-profile_membership "profile_membership"
     */
    public function serviceProfileMembership ($iProfileId = 0)
    {
        if (!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();

		return BxDolAcl::getInstance()->getProfileMembership($iProfileId);
    }

    public function serviceProfileMembershipStats ($iProfileId = 0)
    {
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();

        $sTxtUnlimited = _t('_unlimited');
        $sTxtFiles = _t('_sys_storage_files');
        $aQuota = BxDolProfileQuery::getInstance()->getProfileQuota($iProfileId);

        $aTmplVarsStats = array();
        if(!empty($aQuota['quota_size'])) {
            $iWidth = (int)round(100 * $aQuota['current_size']/$aQuota['quota_size']);
            $sPercent = $iWidth . '%';
        }
        else {
            $iWidth = 0;
            $sPercent = $sTxtUnlimited;
        }
        $aTmplVarsStats[] = array('title' => _t('_sys_profile_storage_quota_size'), 'width' => $iWidth, 'value' => _t_format_size($aQuota['current_size']), 'percent' => $sPercent);

        if(!empty($aQuota['quota_number'])) {
            $iWidth = (int)round(100 * $aQuota['current_number']/$aQuota['quota_number']);
            $sPercent = $iWidth . '%';
        }
        else {
            $iWidth = 0;
            $sPercent = $sTxtUnlimited;
        } 
        $aTmplVarsStats[] = array('title' => _t('_sys_profile_storage_quota_number'), 'width' => $iWidth, 'value' => $aQuota['current_number'] . ' ' . $sTxtFiles, 'percent' => $sPercent);

        return BxDolTemplate::getInstance()->parseHtmlByName('profile_membership_stats.html', array(
            'membership' => BxDolAcl::getInstance()->getProfileMembership($iProfileId),
            'bx_repeat:stat_items' => $aTmplVarsStats
        ));
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-profiles Profiles
     * @subsubsection bx_system_general-profile_notifications profile_notifications
     * 
     * @code bx_srv('system', 'profile_notifications', [], 'TemplServiceProfiles'); @endcode
     * @code {{~system:profile_notifications:TemplServiceProfiles~}} @endcode
     * 
     * Get number of unread notifications for logged in profile
     * 
     * @see BxBaseServiceProfiles::serviceProfileNotifications
     */
    /** 
     * @ref bx_system_general-profile_notifications "profile_notifications"
     */
    public function serviceProfileNotifications ($iProfileId = 0)
    {
        if (!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();

        $oMenu = BxDolMenu::getObjectInstance('sys_account_notifications');
        if(!$oMenu)
            return 0;

        $iNum = 0;
        $aMenuItems = $oMenu->getMenuItems ();
        foreach ($aMenuItems as $r) {
            if (isset($r['bx_if:addon']) && $r['bx_if:addon']['condition'])
                $iNum += $r['bx_if:addon']['content']['addon'];
        }

        return $iNum;
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-profiles Profiles
     * @subsubsection bx_system_general-get_count_online_profiles get_count_online_profiles
     * 
     * @code bx_srv('system', 'get_count_online_profiles', [], 'TemplServiceProfiles'); @endcode
     * @code {{~system:get_count_online_profiles:TemplServiceProfiles~}} @endcode
     * 
     * Get number of online profiles
     * 
     * @see BxBaseServiceProfiles::serviceGetCountOnlineProfiles
     */
    /** 
     * @ref bx_system_general-get_count_online_profiles "get_count_online_profiles"
     */
    public function serviceGetCountOnlineProfiles ()
    {
        $oProfilesQuery = BxDolProfileQuery::getInstance();
        return $oProfilesQuery->getOnlineCount();
    }
    
    public function serviceGetProfilesModules ($bForceActAsProfile = true)
    {
        if (getParam('sys_db_cache_enable')) { // get list of profiles  modules from db cache, cache is invalidated when new module is installed

            $oDb = BxDolDb::getInstance();

            $oCache = $oDb->getDbCacheObject ();

            $sKey = $oDb->genDbCacheKey('profiles_' . (!$bForceActAsProfile ? 'all_' : '') . 'modules_array');
            $sKeyTs = $oDb->genDbCacheKey('profiles_modules_ts');

            $mixedRetTs = $oCache->getData($sKeyTs);
            $mixedRet = $oCache->getData($sKey);

            $iNewestModuleTs = $this->_getLatestModuleTimestamp ();

            if ($mixedRetTs != null && $mixedRet !== null && $mixedRetTs == $iNewestModuleTs) {

                $aModulesArray = $mixedRet;

            } else {

                $aModulesArray = $this->_getProfilesModules ($bForceActAsProfile);

                $oCache->setData($sKey, $aModulesArray);
                $oCache->setData($sKeyTs, $iNewestModuleTs);
            }

        } else {

            $aModulesArray = $this->_getProfilesModules ($bForceActAsProfile);

        }

        return $aModulesArray;
    }

    public function serviceProfilesFriends ($iLimit = 20)
    {
        if (!($iProfileId = bx_get_logged_profile_id()))
            return array();
        
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_friends');
        if (!$oConnection)
            return array();

        if (!($a = $oConnection->getConnectedContent ($iProfileId, true, 0, $iLimit)))
            return array();

        $aRet = array();
        foreach ($a as $iId) {
            $oProfile = BxDolProfile::getInstance($iId);

            $aRet[] = array (
            	'label' => $oProfile->getDisplayName(), 
                'value' => $iId, 
                'url' => $oProfile->getUrl(),
            	'thumb' => $oProfile->getThumb(),
                'unit' => $oProfile->getUnit(0, array('template' => 'unit_wo_info'))
            );
        }
        return $aRet;
    }

    public function serviceProfilesSearch ($sTerm, $mixedParems = [])
    {
        $iLimit = (int)getParam('sys_profiles_search_limit');
        if(is_int($mixedParems)) 
            $iLimit = (int)$mixedParems;
        else if(is_array($mixedParems) && isset($mixedParems['limit']))
            $iLimit = (int)$mixedParems['limit'];

        // display friends by default
        if (!$sTerm)
            return $this->serviceProfilesFriends($iLimit);

        // get list of "profiles" modules
        $aModules = $this->serviceGetProfilesModules();

        // search in each module
        $a = array();
        foreach ($aModules as $aModule) {
            if (!BxDolService::call($aModule['name'], 'act_as_profile'))
                continue;
            $a = array_merge($a, BxDolService::call($aModule['name'], 'profiles_search', array($sTerm, getParam('sys_per_page_search_keyword_single'))));
        }

        // sort result
        usort($a, function($r1, $r2) {
            return strcmp($r1['label'], $r2['label']);
        });

         /**
         * @hooks
         * @hookdef hook-system-profiles_search_by_location 'system', 'profiles_search_by_location' - hook on serach profiles by location
         * - $unit_name - equals `system`
         * - $action - equals `profiles_search_by_location` 
         * - $object_id - not used 
         * - $sender_id - not used 
         * - $extra_params - array of additional params with the following array keys:
         *      - `module` - [string]  module name
         *      - `term` - [string] term for searching
         *      - `result` - [array] by ref, array of results, can be overridden in hook processing
         * @hook @ref hook-account-profiles_search_by_location
         */
        bx_alert('system', 'profiles_search', 0, 0, array(
            'module' => is_array($mixedParems) && isset($mixedParems['module']) ? $mixedParems['module'] : '',
            'term' => $sTerm,
            'result' => &$a
        ));

        // return as array
        return array_slice($a, 0, $iLimit);
    }

    public function serviceProfilesSearchByLocation ($aLocation, $iRadius, $mixedParems = [])
    {
        $sModule = !empty($mixedParems['module']) ? $mixedParems['module'] : '';
        $aModules = !empty($sModule) ? [BxDolModuleDb::getInstance()->getModuleByName($sModule)] : $this->serviceGetProfilesModules();

        $iStart = 0;
        $iLimit = 20;
        if(is_int($mixedParems)) 
            $iLimit = (int)$mixedParems;
        else if(is_array($mixedParems) && isset($mixedParems['limit']))
            $iLimit = (int)$mixedParems['limit'];

        $aLocation[] = $iRadius;
        $oProfileQuery = BxDolProfileQuery::getInstance();

        $aResult = [];
        foreach($aModules as $aModule) {
            if(!bx_srv($aModule['name'], 'act_as_profile'))
                continue;

            $aContentIds = bx_srv($aModule['name'], 'get_search_result_extended', [[
                'location' => ['type' => 'location_radius', 'operator' => 'locate', 'value' => [
                    'array' => $aLocation
                ]]
            ], $iStart, $iLimit]);

            $aResult = array_merge($aResult, $oProfileQuery->getProfiles(['type' => 'id_by_module_content_id', 'module' => $aModule['name'], 'content_id' => $aContentIds]));
        }

        /**
         * @hooks
         * @hookdef hook-system-profiles_search_by_location 'system', 'profiles_search_by_location' - hook on serach profiles by location
         * - $unit_name - equals `system`
         * - $action - equals `profiles_search_by_location` 
         * - $object_id - not used 
         * - $sender_id - not used 
         * - $extra_params - array of additional params with the following array keys:
         *      - `module` - [string]  module name
         *      - `location` - [array] location's info
         *      - `radius` - [int] radius for search
         *      - `result` - [array] by ref, array of results, can be overridden in hook processing
         * @hook @ref hook-account-profiles_search_by_location
         */
        bx_alert('system', 'profiles_search_by_location', 0, 0, [
            'module' => $sModule,
            'location' => $aLocation,
            'radius' => $iRadius,
            'result' => &$aResult
        ]);

        return array_slice($aResult, 0, $iLimit);
    }

    public function serviceProfilesList ($iAccountId = 0)
    {
        $oProfilesQuery = BxDolProfileQuery::getInstance();

        $aProfiles = $oProfilesQuery->getProfilesByAccount($iAccountId ? $iAccountId : getLoggedId());
        if (!$aProfiles)
            return false;

        $s = '';
        foreach ($aProfiles as $aProfile)
            if ($aProfile['type'] != 'system')
                $s .= BxDolService::call($aProfile['type'], 'profile_unit', array($aProfile['content_id']));

        if (!$s)
            $s = MsgBox(_t('_sys_txt_empty'));

        return $s . '<div class="bx-clear"></div>';
    }

    public function serviceBrowseFriends ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = ['profile' => $iProfileId];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('friends', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }
    
    public function serviceProfileFriendsRequestsCount ($iProfileId = 0)
    {
        
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';
        
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_friends');
        if(!$oConnection)
            return false;

        return $oConnection->getConnectedInitiatorsCount($iProfileId, false) - $oConnection->getConnectedInitiatorsCount($iProfileId, true);
    }
    
    public function serviceProfileSentFriendsRequestsCount ($iProfileId = 0)
    {
        
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';
        
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_friends');
        if(!$oConnection)
            return false;

        return $oConnection->getConnectedContentCount($iProfileId, false) - $oConnection->getConnectedContentCount($iProfileId, true);
    }
    
    
    public function serviceProfileFriendsCount ($iProfileId = 0)
    {
        
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';
        
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_friends');
        if(!$oConnection)
            return false;

        return $oConnection->getConnectedInitiatorsCount($iProfileId, true);
    }
    
    public function serviceProfileFollowersCount ($iProfileId = 0)
    {
        
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';
        
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_subscriptions');
        if(!$oConnection)
            return false;

        return $oConnection->getConnectedInitiatorsCount($iProfileId);
    }

    public function serviceProfileFollowingCount ($iProfileId = 0, $bProfilesOnly = false)
    {
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';
        
        $oConnection = BxDolConnection::getObjectInstance('sys_profiles_subscriptions');
        if(!$oConnection)
            return false;

        if(!$bProfilesOnly)
            return $oConnection->getConnectedContentCount($iProfileId);

        $aProfileModules = bx_srv('system', 'get_modules_by_type', ['profile']);
        $aProfileModulesNames = array_map(function($item) {
            return $item['name'];
        }, $aProfileModules);

        return $oConnection->getConnectedContentCountExt($iProfileId, false, ['by_type' => $aProfileModulesNames]);
    }

    public function serviceProfileRecommendationFollowingCount ($iProfileId = 0, $aParams = [])
    {
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';
        
        $oRecommendation = BxDolRecommendation::getObjectInstance('sys_subscriptions');
        if(!$oRecommendation)
            return false;

        $aModules = bx_srv('system', 'get_modules_by_type', ['profile', ['name_as_key' => true]]);

        return $oRecommendation->getCount($iProfileId, array_merge([
            'type' => !empty($aModules) && is_array($aModules) ? array_keys($aModules) : '',
        ], $aParams));
    }
    
    public function serviceProfileRecommendationFriendsCount ($iProfileId = 0, $aParams = [])
    {
        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $oRecommendation = BxDolRecommendation::getObjectInstance('sys_friends');
        if(!$oRecommendation)
            return false;

        return $oRecommendation->getCount($iProfileId, $aParams);
    }

    public function serviceBrowseFriendRequests ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = ['profile' => $iProfileId];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('friend_requests', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }

    public function serviceBrowseFriendRequested ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = ['profile' => $iProfileId];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('friend_requested', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }

    public function serviceBrowseSubscriptions ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = ['profile' => $iProfileId];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('subscriptions', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }

    public function serviceBrowseSubscribedMe ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = ['profile' => $iProfileId];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('subscribed_me', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }

    public function serviceBrowseMembers($iProfileId, $sObject, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = [
            'profile' => $iProfileId, 
            'object' => $sObject
        ];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('members', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }

    public function serviceBrowseConnections ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'params' => false,
            'design_box' => BX_DB_PADDING_DEF,
            'empty_message' => false,
            'ajax_paginate' => true,
        ], $aParams);

        $aParamsBrowse = [
            'profile' => $iProfileId, 
            'object' => 'sys_profiles_friends',
            'type' => 'content',
            'mutual' => false,
            'profile2' => 0
        ];
        if(!empty($aParams['params']) && is_array($aParams['params']))
            $aParamsBrowse = array_merge($aParamsBrowse, $aParams['params']);

        return $this->_serviceBrowseConnections('connections', $aParamsBrowse, $aParams['design_box'], $aParams['empty_message'], $aParams['ajax_paginate']);
    }

    public function serviceBrowseRecommendationsFriends ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams, true);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aParams = array_merge([
            'empty_message' => false,
            'start' => 0,
            'per_page' => 0
        ], $aParams);

        if(($iStartGet = bx_get('start')) !== false)
            $aParams['start'] = (int)$iStartGet;

        if(($iPerPageGet = bx_get('per_page')) !== false)
            $aParams['per_page'] = (int)$iPerPageGet;

        $oRecommendation = BxDolRecommendation::getObjectInstance('sys_friends');
        if(!$oRecommendation)
            return false;

        if(bx_is_api()) {
            $aData = $oRecommendation->getCodeAPI($iProfileId, $aParams);
            $aData = array_merge($aData, [
                'module' => 'system',
                'unit' => 'mixed', 
                'request_url' => '/api.php?r=system/browse_recommendations_friends/TemplServiceProfiles&params[]=' . $iProfileId . '&params[]='
            ]);

            return [bx_api_get_block('browse', $aData)];
        }

        $sCode = $oRecommendation->getCode($iProfileId, $aParams);
        if(!$sCode && $aParams['empty_message'])
            $sCode = MsgBox(_t('_Empty'));

        return $sCode;
    }
    
    public function serviceSetMembership($mixedProfileId, $iAclLevelId, $iAclLevelDuration = 0)
    {
        if(!is_array($mixedProfileId))
            $mixedProfileId = array($mixedProfileId);

        $iPerformerId = bx_get_logged_profile_id();
        $aCheck = checkActionModule($iPerformerId, 'set acl level', 'system', false);
        if(!isAdmin() && $aCheck[CHECK_ACTION_RESULT] !== CHECK_ACTION_RESULT_ALLOWED)
            return true;//array('code' => 1, 'msg' => $aCheck[CHECK_ACTION_MESSAGE]);

        $iSet = 0;
        $oAcl = BxDolAcl::getInstance();
        foreach($mixedProfileId as $iProfileId) {
            if(!$oAcl->setMembership($iProfileId, $iAclLevelId, $iAclLevelDuration, true))
                continue;

            $iSet += 1;

            checkActionModule($iPerformerId, 'set acl level', 'system', true); // perform action
        }

        if(count($mixedProfileId) != $iSet)
            return true;//array('code' => 2, 'msg' => _t('_error occured'));

        return true;//$aResult;
    }

    public function serviceBrowseRecommendationsSubscriptions ($iProfileId = 0, $aParams = [])
    {
        if($this->_bIsApi)
            $aParams = bx_api_get_browse_params($aParams, true);

        if(!$iProfileId)
            $iProfileId = bx_get_logged_profile_id();
        if(!$iProfileId)
            return '';

        $aModules = bx_srv('system', 'get_modules_by_type', ['profile', ['name_as_key' => true]]);

        $aParams = array_merge([
            'empty_message' => false,
            'type' => !empty($aModules) && is_array($aModules) ? array_keys($aModules) : '',
            'start' => 0,
            'per_page' => 0
        ], $aParams);

        if(($iStartGet = bx_get('start')) !== false)
            $aParams['start'] = (int)$iStartGet;

        if(($iPerPageGet = bx_get('per_page')) !== false)
            $aParams['per_page'] = (int)$iPerPageGet;

        $oRecommendation = BxDolRecommendation::getObjectInstance('sys_subscriptions');
        if(!$oRecommendation)
            return false;

        if(bx_is_api()) {
            $aData = $oRecommendation->getCodeAPI($iProfileId, $aParams);
            $aData = array_merge($aData, [
                'module' => 'system',
                'unit' => 'mixed',
                'request_url' => '/api.php?r=system/browse_recommendations_subscriptions/TemplServiceProfiles&params[]=' . $iProfileId . '&params[]='
            ]);

            return [bx_api_get_block('browse', $aData)];
        }

        $sCode = $oRecommendation->getCode($iProfileId, $aParams);
        if(!$sCode && $aParams['empty_message'])
            $sCode = MsgBox(_t('_Empty'));

        return $sCode;
    }

    public function serviceAccountProfileSwitcher ($iAccountId = false, $iActiveProfileId = null, $sUrlProfileAction = '', $bShowAll = 0, $sButtonTitle = '', $sProfileTemplate = '')
    {
    	$oTemplate = BxDolTemplate::getInstance();

        $oProfilesQuery = BxDolProfileQuery::getInstance();

        $aProfiles = $oProfilesQuery->getProfilesByAccount($iAccountId ? $iAccountId : getLoggedId());
        if (!$aProfiles)
            return false;

        if (null === $iActiveProfileId)
            $iActiveProfileId = bx_get_logged_profile_id();

        $oModuleDb = BxDolModuleQuery::getInstance();

        $aVars = array (
            'bx_repeat:row' => array(),
        );
        
        /**
         * @hooks
         * @hookdef hook-system-account_profile_switcher 'system', 'account_profile_switcher' - hook to get account profile switcher
         * - $unit_name - equals `system`
         * - $action - equals `account_profile_switcher` 
         * - $object_id - not used 
         * - $sender_id - not used 
         * - $extra_params - array of additional params with the following array keys:
         *      - `account_id` - [int] account id 
         *      - `active_profile_id` - [int] by ref, current profile id, can be overridden in hook processing
         *      - `url_profile_action` - [string] url profile action
         *      - `show_all` - [bool] by ref, show all profiles or not, can be overridden in hook processing
         *      - `button_title` - [string] by ref,  title for button, can be overridden in hook processing
         *      - `profile_template` - [string] by ref,  profile template, can be overridden in hook processing
         *      - `profiles` - [array] by ref, array of profiles info , can be overridden in hook processing
         * @hook @ref hook-system-account_profile_switcher
         */
        
        bx_alert('system', 'account_profile_switcher', 0, false, array(
            'account_id' => $iAccountId,
            'active_profile_id' => &$iActiveProfileId,
            'url_profile_action' => &$sUrlProfileAction,
            'show_all' => &$bShowAll,
            'button_title' => &$sButtonTitle,
            'profile_template' => &$sProfileTemplate,
            'profiles' => &$aProfiles
        ));

        $aProfilesData = [];
        foreach ($aProfiles as $aProfile) {
            if (!$bShowAll && $iActiveProfileId == $aProfile['id'])
                continue;

            if(!$oModuleDb->isEnabledByName($aProfile['type']))
                continue;

            if (!BxDolService::call($aProfile['type'], 'act_as_profile'))
                continue;
            
            if($this->_bIsApi){
                $o = BxDolProfile::getInstance($aProfile['id']);
                $aProfilesData[] = [
                    'id' => $o->id(),
                    'display_name' => $o->getDisplayName(),
                    'url' => bx_api_get_relative_url($o->getUrl()),
                    'avatar' => $o->getAvatar()
                ];
            }

            $aVars['bx_repeat:row'][] = array (
                'class' => $iActiveProfileId == $aProfile['id'] ? '' : 'bx-def-color-bg-box',
                'bx_if:active' => array (
                    'condition' => $iActiveProfileId == $aProfile['id'],
                    'content' => array('id' => $aProfile['id']),
                ),
                'bx_if:inactive' => array (
                    'condition' => $iActiveProfileId != $aProfile['id'],
                    'content' => array(
                        'id' => $aProfile['id'], 
                        'button_title' => $sButtonTitle ? $sButtonTitle : _t('_sys_txt_switch_profile_context'),
                        'url_switch' => $sUrlProfileAction ? str_replace('{profile_id}', $aProfile['id'], $sUrlProfileAction) : BxDolProfile::getSwitchToProfileRedirectUrl($aProfile['id'])
                    ),
                ),
                'unit' => BxDolService::call($aProfile['type'], 'profile_unit', array($aProfile['content_id'], array('template' => $sProfileTemplate))),
            );
        }

        if($this->_bIsApi){
            return [bx_api_get_block ('profile_switcher', ['active_profile_id' => $iActiveProfileId, 'profiles' => $aProfilesData])];
        }
        
        $oTemplate->addCss('account.css');
        return array(
            'content' => $oTemplate->parseHtmlByName('profile_switch_row.html', $aVars),
        );
    }

    public function serviceAccountProfileSwitcherAll ($iAccountId = false, $iActiveProfileId = null, $sUrlProfileAction = '', $bShowAll = true, $sButtonTitle = '', $sProfileTemplate = '')
    {
        return $this->serviceAccountProfileSwitcher($iAccountId, $iActiveProfileId, $sUrlProfileAction, $bShowAll, $sButtonTitle, $sProfileTemplate);
    }

    public function serviceProfileSettingsCfilter($iProfileId = false)
    {
        if(!BxDolContentFilter::getInstance()->isEnabled()) {
            BxDolTemplate::getInstance()->displayPageNotFound();
            exit;
        }

        // set settings submenu
        $oMenuSubmenu = BxDolMenu::getObjectInstance('sys_site_submenu');
        if ($oMenuSubmenu) {
            $oMenuSubmenu->setObjectSubmenu('sys_account_settings_submenu', array (
                'title' => _t('_sys_menu_item_title_account_settings'),
                'link' => BX_DOL_URL_ROOT . 'member.php',
                'icon' => '',
            ));
        }

        if($iProfileId === false)
            $iProfileId = bx_get_logged_profile_id();

        $oProfile = BxDolProfile::getInstance($iProfileId);
        $aProfileInfo = $oProfile ? $oProfile->getInfo() : false;
        if(empty($aProfileInfo) || !is_array($aProfileInfo))
            return MsgBox(_t('_sys_txt_error_profile_is_not_defined'));

        $sForm = 'sys_profile';
        $sFormDisplay = 'sys_profile_cf_set';
        $oForm = BxDolForm::getObjectInstance($sForm, $sFormDisplay);
        if(!$oForm)
            return MsgBox(_t('_sys_txt_error_occured'));

        $oForm->initChecker($aProfileInfo);
        if(!$oForm->isSubmittedAndValid())
            return $sCode = $oForm->getCode();

        if(!$oForm->update($iProfileId)) {
            if (!$oForm->isValid())
                return $oForm->getCode();
            else
                return MsgBox(_t('_sys_txt_error_profile_update'));
        }

        // display result message
        $sMsg = MsgBox(_t('_' . $sFormDisplay . '_successfully_submitted'));
        return $sMsg . $oForm->getCode();
    }

    public function serviceIsAllowedCfilter($sAction, $iValue, $aProfileInfo)
    {
        if(!in_array($sAction, ['watch', 'use']) || !isset($aProfileInfo['birthday']))
            return false;

        $iAge = 0;
        if(!empty($aProfileInfo['birthday']) && !in_array($aProfileInfo['birthday'], array('0000-00-00', '0000-00-00 00:00:00'))) 
            $iAge = bx_birthday2age($aProfileInfo['birthday']);

        $iResult = 0;
        switch($iValue) {
            case 1:
                $iResult = $iValue;
                break;

            case 2: 
                if($iAge >= 6)
                   $iResult = $iValue;
                break;

            case 3:
                if($iAge >= 13)
                   $iResult = $iValue;
                break;

            case 4:
                if($iAge >= 17)
                   $iResult = $iValue;
                break;

            case 5:
                if($iAge >= 21)
                   $iResult = $iValue;
                break;
        }

        if($iResult != 0)
            $iResult = 1 << ($iResult - 1);

        return $iResult;
    }

    public function serviceIsEnabledCfilter()
    {
        return BxDolContentFilter::getInstance()->isEnabled();
    }

    public function serviceProfileCounters()
    {
        if(bx_is_api() && ($oLiveUpdates = BxDolLiveUpdates::getInstance()) !== false)
            return $oLiveUpdates->performApi();
    }

    public function serviceUpdateSettings($iProfileId = false, $sSettings = '')
    {
        $oProfile = BxDolProfile::getInstance($iProfileId);
        if(!$oProfile)
            return '';
        
        $sModule = $oProfile->getModule();
        
        $sMethod = 'profile_settings_set';
        if(!BxDolRequest::serviceExists($sModule, $sMethod))
            return '';

        BxDolService::call($sModule, $sMethod, [$oProfile->getContentId(), json_decode($sSettings)]);
    }

    
    protected function _getIcon ($sIcon)
    {
        return BxTemplFunctions::getInstance()->getIcon($sIcon);
    }
	
    protected function _getLatestModuleTimestamp ()
    {
        $aModules = BxDolModuleQuery::getInstance()->getModulesBy(array('type' => 'modules', 'active' => 1, 'order_by' => '`date` ASC'));
        if (empty($aModules))
            return 0;
        $aModuleNewest = array_pop($aModules);
        return $aModuleNewest['date'];
    }

    protected function _getProfilesModules($bForceActAsProfile = true)
    {
        $aRet = array();

        $aModules = BxDolModuleQuery::getInstance()->getModulesBy(array('type' => 'modules', 'active' => 1));
        foreach($aModules as $aModule) {
            $oModule = BxDolModule::getInstance($aModule['name']);
            if(!($oModule instanceof iBxDolProfileService)) 
                continue;

            if($bForceActAsProfile && !$oModule->serviceActAsProfile())
                continue;

            $aRet[] = $aModule;
        }

        return $aRet;
    }

    protected function _serviceBrowseConnections ($sMode, $aParams = [], $iDesignBox = BX_DB_PADDING_DEF, $bDisplayEmptyMsg = false, $bAjaxPaginate = true)
    {
        $sClass = 'BxTemplProfileSearchResult';
        bx_import($sClass);
        $o = new $sClass($sMode, $aParams);

        $o->setDesignBoxTemplateId($iDesignBox);
        $o->setDisplayEmptyMsg($bDisplayEmptyMsg);
        $o->setAjaxPaginate($bAjaxPaginate);
        $o->setUnitParams(['context' => $sMode]);

        if ($o->isError)
            return '';

        if ($s = $o->processing()) {
            if(!bx_is_api())
                return $s;

            return [bx_api_get_block('browse', $s)];
        }
        else
            return '';
    }
}

/** @} */
