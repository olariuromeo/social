<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

/**
 * System services.
 */
class BxBaseServices extends BxDol implements iBxDolProfileService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function serviceIsPublicService($s)
    {
        $sService = bx_gen_method_name($s);
        $aServices = $this->serviceGetPublicServices();
        return isset($aServices[$sService]);
    }

    public function serviceGetPublicServices()
    {
        return array (
            'GetProductsNames' => 'BxBaseServices',
            'GetPageByRequest' => 'BxBaseServicePages',
        );
    }

    public function serviceIsSafeService($s)
    {
        $sService = bx_gen_method_name($s);
        $aSafeServices = $this->serviceGetSafeServices();
        return isset($aSafeServices[$sService]);
    }

    public function serviceGetSafeServices()
    {
        return array(
            'GetCreatePostForm' => 'BxBaseServices',
            'GetProductsNames' => 'BxBaseServices',
            'KeywordSearch' => 'BxBaseServices',
            'GetDataSearchApi' => 'BxBaseServices',
            'Cmts' => 'BxBaseServices',
            'GetFooter' => 'BxBaseServices',

            'CreateAccountForm' => 'BxBaseServiceAccount',
            'AccountSettingsEmail' => 'BxBaseServiceAccount',
            'AccountSettingsPassword' => 'BxBaseServiceAccount',
            'AccountSettingsDelAccount' => 'BxBaseServiceAccount',
            'AccountSettingsInfo' => 'BxBaseServiceAccount',
            'ForgotPassword' => 'BxBaseServiceAccount',
            'SwitchProfile' => 'BxBaseServiceAccount',
            'AccountProfileSwitcher' => 'BxBaseServiceAccount',
            'EmailConfirmation' => 'BxBaseServiceAccount',
            'ConfirmEmail' => 'BxBaseServiceAccount',
            
            'CategoriesList' => 'BxBaseServiceCategory',

            'Test' => 'BxBaseServiceLogin',
            'MemberAuthCode' => 'BxBaseServiceLogin',
            'LoginForm' => 'BxBaseServiceLogin',
            'LoginFormOnly' => 'BxBaseServiceLogin',
            'Logout' => 'BxBaseServiceLogin',
        
            'KeywordsCloud' => 'BxBaseServiceMetatags',

            'AccountProfileSwitcher' => 'BxBaseServiceProfiles',
            'ProfileMembership' => 'BxBaseServiceProfiles',
            'ProfileNotifications' => 'BxBaseServiceProfiles',
            'GetCountOnlineProfiles' => 'BxBaseServiceProfiles',
            'BrowseRecommendationsFriends' => 'BxBaseServiceProfiles',
            'BrowseRecommendationsSubscriptions' => 'BxBaseServiceProfiles',
            'BrowseFriends' => 'BxBaseServiceProfiles',
            'BrowseFriendRequests' => 'BxBaseServiceProfiles',
            'BrowseFriendRequested' => 'BxBaseServiceProfiles',
            'BrowseSubscribedMe' => 'BxBaseServiceProfiles',
            'BrowseSubscriptions' => 'BxBaseServiceProfiles',
            'BrowseMembers' => 'BxBaseServiceProfiles',
            'UpdateSettings' => 'BxBaseServiceProfiles',
            'Befriend' => 'BxBaseServiceProfiles',
            
            'GetChartGrowth' => 'BxBaseChartServices',
            'GetChartStats' => 'BxBaseChartServices',
            'GetDataByInterval' => 'BxBaseChartServices',
            
            'GetCartItemsCount' => 'BxBasePaymentsServices',
            'GetOrdersCount' => 'BxBasePaymentsServices',

            'Do' => 'BxBaseReportServices',
            'GetPerformedBy' => 'BxBaseReportServices',

            'Do' => 'BxBaseVoteServices',
            'GetPerformedBy' => 'BxBaseVoteServices',

            'Perform' => 'BxBaseServiceConnections',
            'Perform' => 'BxBaseServiceRecommendations',
            'GetDataApi' => 'BxBaseCmtsServices',

            'GetDataApi' => 'BxBaseUploaderServices',
            
            'GetStatBlock' => 'BxBaseDashboardServices',
            
            'PerfomActionApi' => 'BxBaseServiceGrid',

            'GetLabels' => 'BxBaseLabelsServices',
            'GetForm' => 'BxBaseSearchExtendedServices',
            'GetResults' => 'BxBaseSearchExtendedServices'
        );
    }

    public function serviceGetPreloaderContent($sName) {
        $sResult = '';

        switch($sName) {
            case 'icons':
                $sResult = getParam('sys_css_icons_default');
                break;

            case 'tailwind':
                $sFile = getParam('sys_css_tailwind_default');
                if(empty($sFile))
                    $sFile = 'tailwind.min.css';

                $sResult = '{dir_plugins_public}tailwind/css/|' . $sFile;
                break;
        }

        return $sResult;
    }
            
    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_check_for_update module_check_for_update
     * 
     * @code bx_srv('system', 'module_check_for_update', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_check_for_update', ["bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:module_check_for_update:TemplServices["bx_posts"]~}} @endcode
     * 
     * Checks whether an update is available for requested module or not.
     * @param $mixedModule - module ID/name to check the updates for.
     * @return array with information about the update, false if nothing was found
     * 
     * @see BxBaseServices::serviceModuleCheckForUpdate
     */
    /** 
     * @ref bx_system_general-module_check_for_update "module_check_for_update"
     */
    public function serviceModuleCheckForUpdate($mixedModule)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        if(empty($aModule) || !is_array($aModule))
            return false;

        $aUpdates = BxDolStudioInstallerUtils::getInstance()->checkUpdatesByModule($aModule['name']);
        if(empty($aUpdates) || !is_array($aUpdates)) 
            return false;

        $aUpdate = array_shift($aUpdates);
        return array(
            'version_from' => $aUpdate['file_version'],
            'version_to' => $aUpdate['file_version_to'],
            'file_id' => $aUpdate['file_id']
        );
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_update module_update
     * 
     * @code bx_srv('system', 'module_update', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_update', ["bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:module_update:TemplServices["bx_posts"]~}} @endcode
     * 
     * Download and install an update for requested module. The operation may be performed 
     * immediately or by Transient Cron task. The second way is more common.
     * @param $mixedModule - module ID/name to check the updates for.
     * @return integer value determining the result of the operation. It can be one of the following values:
     * BX_DOL_STUDIO_IU_RC_SUCCESS, BX_DOL_STUDIO_IU_RC_FAILED or BX_DOL_STUDIO_IU_RC_SCHEDULED.
     * 
     * @see BxBaseServices::serviceModuleUpdate
     */
    /** 
     * @ref bx_system_general-module_update "module_update"
     */
    public function serviceModuleUpdate($mixedModule)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        if(empty($aModule) || !is_array($aModule))
            return BX_DOL_STUDIO_IU_RC_FAILED;

        $aResult = BxDolStudioInstallerUtils::getInstance()->downloadUpdatePublic($aModule['name'], true);
        return $aResult['code'];
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_delete module_delete
     * 
     * @code bx_srv('system', 'module_delete', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_delete', ["bx_posts", true], 'TemplServices'); @endcode
     * @code {{~system:module_delete:TemplServices["bx_posts"]~}} @endcode
     * 
     * Delete the requested module. By default only uninstalled module can be deleted.
     * The operation may be performed immediately or by Transient Cron task. The second way is more common.
     * @param $sModule - module name to delete.
     * @param $bForceUninstall - if 'true' is passed the module will be uninstalled before deletion.
     * @return integer value determining the result of the operation. It can be one of the following values:
     * BX_DOL_STUDIO_IU_RC_SUCCESS, BX_DOL_STUDIO_IU_RC_FAILED or BX_DOL_STUDIO_IU_RC_SCHEDULED.
     * 
     * @see BxBaseServices::serviceModuleDelete
     */
    /** 
     * @ref bx_system_general-module_delete "module_delete"
     */
    public function serviceModuleDelete($sModule, $bForceUninstall = false)
    {
        $sModulePath = '';
        $oInstallerUtils = BxDolStudioInstallerUtils::getInstance();

        $aModule = BxDolModuleQuery::getInstance()->getModuleByName($sModule, false);
        if(!empty($aModule) && is_array($aModule)) {
            if(!$bForceUninstall)
                return BX_DOL_STUDIO_IU_RC_FAILED;

            $sModulePath = $aModule['path'];

            $aResult = $oInstallerUtils->perform($sModulePath, 'uninstall', array('auto_disable' => $bForceUninstall));
            if($aResult['code'] != BX_DOL_STUDIO_IU_RC_SUCCESS)
                return BX_DOL_STUDIO_IU_RC_FAILED;
        }
        else {
            $aModules = $oInstallerUtils->getModules(false);
            if(!isset($aModules[$sModule]))
                return BX_DOL_STUDIO_IU_RC_FAILED;
            
            $sModulePath = $aModules[$sModule]['dir'];
        }

        $aResult = $oInstallerUtils->perform($sModulePath, 'delete');
        return $aResult['code'];
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_install module_install
     * 
     * @code bx_srv('system', 'module_install', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_install', ["bx_posts", true], 'TemplServices'); @endcode
     * @code {{~system:module_install:TemplServices["bx_posts"]~}} @endcode
     * 
     * Install the requested module. By default the module will be installed only.
     * @param $sModule - module name to install.
     * @param $bForceEnable - if 'true' is passed the module will be automatically enabled after installation.
     * @return boolean value determining the result of the operation.
     * 
     * @see BxBaseServices::serviceModuleInstall
     */
    /** 
     * @ref bx_system_general-module_install "module_install"
     */
    public function serviceModuleInstall($sModule, $bForceEnable = false)
    {
        $oInstallerUtils = BxDolStudioInstallerUtils::getInstance();

        $aModules = $oInstallerUtils->getModules(false);
        if(!isset($aModules[$sModule]))
            return false;

        $aResult = $oInstallerUtils->perform($aModules[$sModule]['dir'], 'install', array('auto_enable' => $bForceEnable));
        if($aResult['code'] != BX_DOL_STUDIO_IU_RC_SUCCESS)
            return false;

        return true;
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_uninstall module_uninstall
     * 
     * @code bx_srv('system', 'module_uninstall', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_uninstall', ["bx_posts", true], 'TemplServices'); @endcode
     * @code {{~system:module_uninstall:TemplServices["bx_posts"]~}} @endcode
     * 
     * Uninstall the requested module. By default only disabled module can be uninstalled.
     * @param $mixedModule - module ID/name to uninstall.
     * @param $bForceDisable - if 'true' is passed the module will be automatically disabled before uninstallation.
     * @return boolean value determining the result of the operation.
     * 
     * @see BxBaseServices::serviceModuleUninstall
     */
    /** 
     * @ref bx_system_general-module_uninstall "module_uninstall"
     */
    public function serviceModuleUninstall($mixedModule, $bForceDisable = false)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        if(empty($aModule) || !is_array($aModule))
            return false;

        if((int)$aModule['enabled'] != 0 && !$bForceDisable)
            return false;

        $aResult = BxDolStudioInstallerUtils::getInstance()->perform($aModule['path'], 'uninstall', array('auto_disable' => $bForceDisable));
        if($aResult['code'] != BX_DOL_STUDIO_IU_RC_SUCCESS)
            return false;

        return true;
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_enable module_enable
     * 
     * @code bx_srv('system', 'module_enable', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_enable', ["bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:module_enable:TemplServices["bx_posts"]~}} @endcode
     * 
     * Enable the requested module.
     * @param $mixedModule - module ID/name to enable.
     * @return boolean value determining the result of the operation.
     * 
     * @see BxBaseServices::serviceModuleEnable
     */
    /** 
     * @ref bx_system_general-module_enable "module_enable"
     */
    public function serviceModuleEnable($mixedModule)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        if(empty($aModule) || !is_array($aModule))
            return false;

        if((int)$aModule['enabled'] != 0)
            return false;

        $aResult = BxDolStudioInstallerUtils::getInstance()->perform($aModule['path'], 'enable');
        if($aResult['code'] != BX_DOL_STUDIO_IU_RC_SUCCESS)
            return false;

        return true;
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-module_disable module_disable
     * 
     * @code bx_srv('system', 'module_disable', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'module_disable', ["bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:module_disable:TemplServices["bx_posts"]~}} @endcode
     * 
     * Disable the requested module.
     * @param $mixedModule - module ID/name to disable.
     * @return boolean value determining the result of the operation.
     * 
     * @see BxBaseServices::serviceModuleDisable
     */
    /** 
     * @ref bx_system_general-module_disable "module_disable"
     */
    public function serviceModuleDisable($mixedModule)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        if(empty($aModule) || !is_array($aModule))
            return false;

        if((int)$aModule['enabled'] == 0)
            return false;

        $aResult = BxDolStudioInstallerUtils::getInstance()->perform($aModule['path'], 'disable');
        if($aResult['code'] != BX_DOL_STUDIO_IU_RC_SUCCESS)
            return false;

        return true;
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-is_module_installed is_module_installed
     * 
     * @code bx_srv('system', 'is_module_installed', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'is_module_installed', ["bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:is_module_installed:TemplServices["bx_posts"]~}} @endcode
     * 
     * Checks whether the requested module is installed or not.
     * @param $mixedModule - module ID/name to check.
     * @return boolean value determining the result of the operation.
     * 
     * @see BxBaseServices::serviceIsModuleInstalled
     */
    /** 
     * @ref bx_system_general-is_module_installed "is_module_installed"
     */
    public function serviceIsModuleInstalled($mixedModule)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        return !empty($aModule) && is_array($aModule);
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-is_module_enabled is_module_enabled
     * 
     * @code bx_srv('system', 'is_module_enabled', ["bx_posts"]); @endcode
     * @code bx_srv('system', 'is_module_enabled', ["bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:is_module_enabled:TemplServices["bx_posts"]~}} @endcode
     * 
     * Checks whether the requested module is enabled or not.
     * @param $mixedModule - module ID/name to check.
     * @return boolean value determining the result of the operation.
     * 
     * @see BxBaseServices::serviceIsModuleEnabled
     */
    /** 
     * @ref bx_system_general-is_module_enabled "is_module_enabled"
     */
    public function serviceIsModuleEnabled($mixedModule)
    {
        $aModule = BxDolModuleQuery::getInstance()->{'getModuleBy' . (is_numeric($mixedModule) ? 'Id' : 'Name')}($mixedModule, false);
        return !empty($aModule) && is_array($aModule) && (int)$aModule['enabled'] != 0;
    }

    /**
     * Checks whether a module is a 'Content' module or not.
     * 
     * @param string $mixedModule - module name or an instance of Module class.
     * @return boolean result of operation.
     */
    public function serviceIsModuleContent($mixedModule)
    {
        if($mixedModule instanceof BxDolModule)
            $mixedModule = $mixedModule->getName();

        return BxDolModuleQuery::getInstance()->isModuleContent($mixedModule);
    }

    /**
     * Checks whether a module is a 'Context' module or not.
     * 
     * @param string $mixedModule - module name or an instance of Module class.
     * @return boolean result of operation.
     */
    public function serviceIsModuleContext($mixedModule)
    {
        if($mixedModule instanceof BxDolModule)
            $mixedModule = $mixedModule->getName();

        return BxDolModuleQuery::getInstance()->isModuleContext($mixedModule);
    }

    /**
     * Checks whether a module is a 'Profile' module or not.
     * 
     * @param string $mixedModule - module name or an instance of Module class.
     * @return boolean result of operation.
     */
    public function serviceIsModuleProfile($mixedModule)
    {
        if($mixedModule instanceof BxDolModule)
            $mixedModule = $mixedModule->getName();

        return BxDolModuleQuery::getInstance()->isModuleProfile($mixedModule);
    }

    /**
     * Get modules by type. Available types are 'content', 'context', 'profile'.
     * 
     * @param type $sType - string with type.
     * @return array of modules.
     */
    public function serviceGetModulesByType($sType, $aParams = [])
    {
        $oModuleQuery = BxDolModuleQuery::getInstance();

        if(!$oModuleQuery->checkModulesSubtypes()) {
            $oInstallerUtils = new BxDolInstallerUtils();
            $aModules = $oModuleQuery->getModulesBy(['type' => 'modules']);
            foreach($aModules as $aModule)
                if(($iModuleSubtypes = $oInstallerUtils->getSubtypes($aModule['name'])) != 0)
                    $oModuleQuery->updateModule(['subtypes' => $iModuleSubtypes], ['id' => $aModule['id']]);
        }

        $iSubtypes = 0;
        switch($sType) {
            case 'content';
                $iSubtypes = BX_DOL_MODULE_SUBTYPE_TEXT;
                break;

            case 'context';
                $iSubtypes = BX_DOL_MODULE_SUBTYPE_CONTEXT;
                break;

            case 'profile';
                $iSubtypes = BX_DOL_MODULE_SUBTYPE_PROFILE;
                break;
        }

        $aResults = $oModuleQuery->getModulesBy(array_merge([
            'type' => 'modules_subtypes', 
            'value' => $iSubtypes, 
            'active' => 1
        ], array_intersect_key($aParams, array_flip(['id_as_key', 'name_as_key']))));

        return $aResults;
    }

    public function serviceProfileUnit ($iContentId, $aParams = array())
    {
        return $this->_serviceProfileFunc('getUnit', $iContentId, $aParams);
    }

    public function serviceHasImage ($iContentId)
    {
        return false;
    }

    public function serviceProfilePicture ($iContentId)
    {
        return BxDolTemplate::getInstance()->getImageUrl('account.svg');
    }

    public function serviceProfileAvatar ($iContentId)
    {
        return BxDolTemplate::getInstance()->getImageUrl('account.svg');
    }

    public function serviceProfileCover ($iContentId)
    {
        return '';
    }

    public function serviceProfileEditUrl ($iContentId)
    {
        return bx_absolute_url(BxDolPermalinks::getInstance()->permalink('page.php?i=account-settings-info'));
    }

    public function serviceProfileThumb ($iContentId)
    {
        return $this->_serviceProfileFunc('getThumb', $iContentId);
    }

    public function serviceProfileIcon ($iContentId)
    {
        return $this->_serviceProfileFunc('getIcon', $iContentId);
    }

    public function serviceProfileName ($iContentId)
    {
        return $this->_serviceProfileFunc('getDisplayName', $iContentId);
    }

    public function serviceProfileUrl ($iContentId)
    {
        return $this->_serviceProfileFunc('getUrl', $iContentId);
    }

    /**
     * @see iBxDolProfileService::serviceCheckAllowedProfileView
     */ 
    public function serviceCheckAllowedProfileView($iContentId)
    {
        return _t('_Access denied');
    }

    /**
     * @see iBxDolProfileService::serviceCheckAllowedProfileContact
     */ 
    public function serviceCheckAllowedProfileContact($iContentId)
    {
        return _t('_Access denied');
    }

    /**
     * @see iBxDolProfileService::serviceCheckAllowedPostInProfile
     */ 
    public function serviceCheckAllowedPostInProfile($iContentId, $sPostModule = '')
    {
        return _t('_Access denied');
    }

    /**
     * @see iBxDolProfileService::serviceCheckAllowedModuleActionInProfile
     */
    public function serviceCheckAllowedModuleActionInProfile($iContentId, $sPostModule, $sActionName)
    {
        return _t('_Access denied');
    }

    /**
     * @see iBxDolProfileService::serviceGetSpaceTitle
     */ 
    public function serviceGetSpaceTitle()
    {
        return '';
    }

    /**
     * @see iBxDolProfileService::serviceGetParticipatingProfiles
     */ 
    public function serviceGetParticipatingProfiles($iProfileId, $aConnectionObject = false)
    {
        return array();
    }
    
    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-get_create_post_form get_create_post_form
     * 
     * @code bx_srv('system', 'get_create_post_form', [false, "bx_posts"], 'TemplServices'); @endcode
     * @code {{~system:get_create_post_form:TemplServices[false,"bx_posts"]~}} @endcode
     * 
     * Get United Create Post form.
     * @param $mixedContextId - context which the post will be created in:
     *      - false = 'Public' post form;
     *      - 0 = 'Profile' post form, which allows to post in your own profile and connections;
     *      - n = 'Context' post form, which allows to post in context (3d party profile, group, event, etc).
     * @param $sDefault - tab selected by default.
     * @param $aCustom - an array with custom paramaters.
     * @return string with form content
     * 
     * @see BxBaseServices::serviceGetCreatePostForm
     */
    /** 
     * @ref bx_system_general-get_create_post_form "get_create_post_form"
     */
    public function serviceGetCreatePostForm($mixedContextId = false, $sDefault = '', $aCustom = array())
    {
    	if(!isLogged() || ($mixedContextId !== false && !is_numeric($mixedContextId)))
            return '';

        $oProfile = BxDolProfile::getInstance();
        if(!$oProfile)
            return '';

        if($mixedContextId !== false)
            $mixedContextId = (int)(!empty($mixedContextId) ? -$mixedContextId : $oProfile->id());

    	$oMenu = BxDolMenu::getObjectInstance('sys_create_post');
        if(!$oMenu)
            return '';

        $oMenu->setContextId($mixedContextId);
    	$aMenuItems = $oMenu->getMenuItems();
    	if(empty($aMenuItems) || !is_array($aMenuItems))
            return '';

    	if(empty($sDefault)) {
            $aDefault = array_shift($aMenuItems);
            $sDefault = !empty($aDefault['module']) ? $aDefault['module'] : $aDefault['name'];
    	}
    	$oMenu->setSelected($sDefault, $sDefault);

        $bContext = $mixedContextId !== false;
        if($bContext && ($aContextInfo = BxDolProfileQuery::getInstance()->getInfoById(abs($mixedContextId))))
            if(bx_srv($aContextInfo['type'], 'check_allowed_post_in_profile', [$aContextInfo['content_id'], $sDefault]) !== CHECK_ACTION_RESULT_ALLOWED)
                return '';

        $sTitle = _t('_sys_page_block_title_create_post' . (!$bContext ? '_public' : ($mixedContextId < 0 ? '_context' : '')));
        $sPlaceholder = _t('_sys_txt_create_post_placeholder', $oProfile->getDisplayName());

        $oDbModules = BxDolModuleQuery::getInstance();
    	$oTemplate = BxDolTemplate::getInstance();
    	$oTemplate->addJs(['BxDolCreatePost.js']);

        $aPreloadingList = [];
        $aModules = explode(',', getParam('sys_create_post_form_preloading_list'));
        if(!empty($aModules))
            foreach($aModules as $sModule) {
                $aModule = $oDbModules->getModuleByName($sModule);
                if(empty($aModule) || !is_array($aModule))
                    continue;

                $aPreloadingList[$sModule] = $aModule['uri'];
            }

        if(bx_is_api()) {
            return [
                'title' => $sTitle,
                'content' => '',
                'menu' => $oMenu->getCodeAPI()
            ];
        }

    	$sJsObject = 'oBxDolCreatePost';
        $sJsContent = $oTemplate->_wrapInTagJsCode("var " . $sJsObject . " = new BxDolCreatePost(" . json_encode([
            'sObjName' => $sJsObject,
            'sRootUrl' => BX_DOL_URL_ROOT,
            'sDefault' => $sDefault,
            'iContextId' => $bContext ? $mixedContextId : 0,
            'oPreloadingList' => $aPreloadingList,
            'oCustom' => $aCustom
        ]) . ");");

    	return [
            'title' => $sTitle,
            'content' => $oTemplate->parseHtmlByName('create_post_form.html', [
                'default' => $sDefault,
                'placeholder' => $sPlaceholder,
                'user_thumb' => $oProfile->getUnit(0, ['template' => 'unit_wo_info_links']),
                'form' => bx_srv($sDefault, 'get_create_post_form', [[
                    'context_id' => $mixedContextId, 
                    'ajax_mode' => true, 
                    'absolute_action_url' => true, 
                    'custom' => $aCustom
                ]]),
                'js_object' => $sJsObject,
                'js_content' => $sJsContent
            ]),
            'menu' => $oMenu
        ];
    }

    public function serviceGetOptionsCreatePostFormPreloadingList()
    {
        $oMenu = BxDolMenu::getObjectInstance('sys_create_post');
        if(!$oMenu)
            return [];

        $aMenuItems = $oMenu->getMenuItems();

        $aResult = [];
        foreach($aMenuItems as $aMenuItem) {
            $sModule = !empty($aMenuItem['module']) ? $aMenuItem['module'] : $aMenuItem['name'];
            if(!bx_srv('system', 'is_module_enabled', [$sModule]))
                continue;

            $aResult[] = [
                'key' => $sModule, 
                'value' => _t('_' . $sModule)
            ];
        }

        return $aResult;
    }

    public function serviceGetBlockAuthor($sModule, $iContentId = 0)
    {
        if(!$iContentId && bx_get('id') !== false)
            $iContentId = (int)bx_get('id');

        $sMethodGetAuthor = 'get_author';
        if(!$sModule || !$iContentId || !BxDolRequest::serviceExists($sModule, $sMethodGetAuthor))
            return '';

        $iAuthor = bx_srv($sModule, $sMethodGetAuthor, [$iContentId]);
        if(!$iAuthor)
            return '';

        $oAuthor = BxDolProfile::getInstance($iAuthor);
        if(!$oAuthor)
            return '';

        $sModuleAuthor = $oAuthor->getModule();
        $sMethodGetCover = 'entity_cover';
        if(!BxDolRequest::serviceExists($sModuleAuthor, $sMethodGetCover))
            return '';

        return bx_srv($sModuleAuthor, $sMethodGetCover, [$oAuthor->getContentId(), [
            'use_as_author' => true,
            'show_text' => false
        ]]);
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-get_site_submenu get_site_submenu
     * 
     * @code bx_srv('system', 'get_site_submenu', [], 'TemplServices'); @endcode
     * @code {{~system:get_site_submenu:TemplServices[]~}} @endcode
     * 
     * Get Site Submenu code.
     * @see BxBaseServices::serviceGetSiteSubmenu
     */
    /** 
     * @ref bx_system_general-get_site_submenu "get_site_submenu"
     */
    public function serviceGetSiteSubmenu()
    {
        $oMenu = BxDolMenu::getObjectInstance('sys_site_submenu');
        return $oMenu ? $oMenu->getCode() : '';
    }
    
    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-get_products_names get_products_names
     * 
     * @code bx_srv('system', 'get_products_names', [], 'TemplServices'); 
     *
     * @code http://example.com/m/oauth2/com/get_products_names?module=system&class=BaseServices @endcode
     * @code http://hihi.una.io/modules/?r=oauth2/com/get_products_names&module=system&class=BaseServices @endcode
     * 
     * Get an array of products names from all modules with payments functionality.
     * @param $iVendorId filter products by vendor ID.
     * @param $iLimit limit number of records from one module.
     * @return array of products where key id product name and value is module name
     * 
     * @see BxBaseServices::serviceGetProductsNames
     */
    /** 
     * @ref bx_system_general-get_products_names "get_products_names"
     */
    public function serviceGetProductsNames($iVendorId = 0, $iLimit = 1000)
    {
        $o = BxDolPayments::getInstance();
        if (!$o)
            return array();
        return $o->getProductsNames($iVendorId, $iLimit);
    }

    /**
     * @see iBxDolProfileService::serviceCheckSpacePrivacy
     */ 
    public function serviceCheckSpacePrivacy($iContentId)
    {
        return _t('_Access denied');
    }
    
    public function serviceFormsHelper ()
    {
        return new BxTemplAccountForms();
    }

    public function serviceActAsProfile ()
    {
        return false;
    }

    public function servicePrepareFields ($aFieldsProfile)
    {
        return $aFieldsProfile;
    }

    public function serviceProfilesSearch ($sTerm, $iLimit)
    {
        $oDb = BxDolAccountQuery::getInstance();
        $aRet = array();
        $a = $oDb->searchByTerm($sTerm, $iLimit);
        foreach ($a as $r)
            $aRet[] = array ('label' => $this->serviceProfileName($r['content_id']), 'value' => $r['profile_id']);
        return $aRet;
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-keyword_search keyword_search
     * 
     * @code bx_srv('system', 'keyword_search', ["bx_posts", ["keyword" => "test"}], 'TemplServices'); @endcode
     * 
     * @code {{~system:keyword_search:TemplServices["bx_posts", {"keyword":"test"}]~}} @endcode
     * @code {{~system:keyword_search:TemplServices["bx_albums", {"meta_type": "location_country", "keyword": "AU"}, "unit.html"]~}} @endcode
     * @code {{~system:keyword_search:TemplServices["bx_albums", {"meta_type": "location_country_state", "state":"NSW", "keyword": "AU"}, "unit.html"]~}} @endcode
     * @code {{~system:keyword_search:TemplServices["bx_albums", {"meta_type": "location_country_city", "state":"NSW", "city":"Manly", "keyword": "AU"}, "unit.html"]~}} @endcode
     * @code {{~system:keyword_search:TemplServices["bx_posts", {"meta_type": "mention", "keyword": 2}, "unit_gallery.html"]~}} @endcode
     * @code {{~system:keyword_search:TemplServices["bx_posts", {"cat": "bx_posts_cats", "keyword": 3}, "unit_gallery.html"]~}} @endcode
     * 
     * Search by keyword
     * @param $sSection - search object to search in, usually module name, for example: bx_posts
     * @param $aCondition - condition for search, supported conditions: 
     *          - search by keyword: ["keyword" => "test"]
     *          - search by country: ["meta_type" => "location_country", "keyword" => "AU"]
     *          - search by country and state: ["meta_type": "location_country_state", "state":"NSW", "keyword": "AU"]
     *          - search by country, state and city: ["meta_type": "location_country_city", "state":"NSW", "city":"Manly", "keyword": "AU"]
     *          - search for mentions: ["meta_type" => "mention", "keyword" => 2]
     *          - search in category: ["cat": "bx_posts_cats", "keyword": 3]
     * @param $sTemplate - template for displaying search results, for example: unit.html
     * @param $iStart - paginate, display records starting from this number
     * @param $iPerPage - paginate, display this number of records per page
     * @param $bLiveSearch - search results like in live search
     * 
     * @see BxBaseServices::serviceKeywordSearch
     */
    /** 
     * @ref bx_system_general-keyword_search "keyword_search"
     */
    public function serviceKeywordSearch ($sSection, $aCondition, $sTemplate = '', $iStart = 0, $iPerPage = 0, $bLiveSearch = 0, $bPaginate = false)
    {
        if (!$sSection || !isset($aCondition['keyword']))
            return '';

        $sClass = 'BxTemplSearch';

        $sElsName = 'bx_elasticsearch';
        $sElsMethod = 'is_configured';
        if(BxDolRequest::serviceExists($sElsName, $sElsMethod) && BxDolService::call($sElsName, $sElsMethod)) {
             $oModule = BxDolModule::getInstance($sElsName);

             bx_import('Search', $oModule->_aModule);
             $sClass = 'BxElsSearch';
        }

        $oSearch = new $sClass(array($sSection));
        $oSearch->setLiveSearch($bLiveSearch);
        $oSearch->setMetaType(isset($aCondition['meta_type']) ? $aCondition['meta_type'] : '');
        $oSearch->setCategoryObject(isset($aCondition['cat']) ? $aCondition['cat'] : '');
        $oSearch->setCustomSearchCondition($aCondition);
        if (!$bPaginate)
            $oSearch->setRawProcessing(true);
        $oSearch->setCustomCurrentCondition(array(
            'paginate' => array (
                'start' => $iStart,
                'perPage' => $iPerPage ? $iPerPage : BX_DOL_SEARCH_RESULTS_PER_PAGE_DEFAULT,
            )));
        if ($sTemplate)
            $oSearch->setUnitTemplate($sTemplate);
        
        return $oSearch->response();
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-keyword_search keyword_search
     * 
     * @code bx_srv('system', 'search_keyword_form', 'TemplServices'); @endcode
     * 
     * Block with Search by Keywords Form
     *  
     * @see BxBaseServices::serviceSearchKeywordForm
     */
    /** 
     * @ref bx_system_general-keyword_search "keyword_search"
     */
    public function serviceSearchKeywordForm ()
    {
        return $this->_getSearchObject()->getForm(BX_DB_PADDING_DEF, false, true);
    }
    
    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-keyword_search keyword_search
     * 
     * @code bx_srv('system', 'search_keyword_result', 'TemplServices'); @endcode
     * 
     * Block with Search by Keywords Results
     *  
     * @see BxBaseServices::serviceSearchKeywordResult
     */
    /** 
     * @ref bx_system_general-keyword_search "keyword_search"
     */
    public function serviceSearchKeywordResult ()
    {
        $sKeyword = bx_process_input(bx_get('keyword'));
        $bKeyword = $sKeyword !== false;

        if(bx_is_api())
            return $this->serviceGetDataSearchApi(['params' => 
                [
                    'keyword' => $sKeyword,
                    'section' => bx_process_input(bx_get('section')),
                    'cat' => bx_process_input(bx_get('cat'))
                ]
            ]);

        $sCode = '';
        if($bKeyword) {
            $oSearch = $this->_getSearchObject();

            $sCode = $oSearch->response();
            if(!$sCode)
                $sCode = $oSearch->getEmptyResult();
        }

        return $sCode;
    }

    /** 
     * @ref bx_system_general-get_date_search_api "get_date_search_api"
     * @api @ref bx_system_general-get_date_search_api "get_date_search_api"
     */
    public function serviceGetDataSearchApi ($aParams)
    {
        if(!bx_is_api())
            return false;

        if(is_string($aParams))
            $aParams = json_decode($aParams, true);

        $aSections = [];
        $aSectionsAll = BxDolDb::getInstance()->fromCache(
            'sys_global_search_pairs', 
            'getPairs', 
            'SELECT `ObjectName` AS `name`, `Title` AS `title` FROM `sys_objects_search` WHERE `GlobalSearch`=\'1\' ORDER BY `Order` ASC',
            'name', 'title'
        );

        $aSectionNames = explode(',', getParam('sys_api_search_sections'));
        foreach($aSectionNames as $sSectionName) {
            if(!isset($aSectionsAll[$sSectionName]))
                continue;

            $aSections[$sSectionName] = [
                'name' => $sSectionName,
                'title' => _t($aSectionsAll[$sSectionName])
            ];
        }

        $aParamsBrowse = array_merge([
            'keyword' => '',
            'section' => '',
            'sections' => array_values($aSections),
            'start' => 0,
            'per_page' => 12
        ], !empty($aParams['params']) && is_array($aParams['params']) ? $aParams['params'] : []);

        if(empty($aParamsBrowse['section']))
            $aParamsBrowse['section'] = array_keys($aSections);
        else if(is_string($aParamsBrowse['section']))
            $aParamsBrowse['section'] = explode(',', $aParamsBrowse['section']);

        $sClass = 'BxTemplSearch';
        $oSearch = new $sClass($aParamsBrowse['section']);
        $oSearch->setLiveSearch(true);
        $oSearch->setDataProcessing(true);
        $oSearch->setCustomSearchCondition(['keyword' => $aParamsBrowse['keyword']]);
        $oSearch->setCustomCurrentCondition([
            'paginate' => [
                'forceStart' => $aParamsBrowse['start'],
                'perPage' => $aParamsBrowse['per_page'],
            ]
        ]);

        $aData = $oSearch->response();
        if(count($aData) > $aParamsBrowse['per_page'])
            $aData = array_slice($aData, $aParamsBrowse['start'], $aParamsBrowse['per_page']);

        return [
            bx_api_get_block('browse', [
                'unit' => 'search-results',  
                'request_url' => '/api.php?r=system/get_data_search_api/TemplServices&params[]=',
                'params' => $aParamsBrowse,
                'data' => $aData
            ])
        ];
    }

    /**
     * @page service Service Calls
     * @section bx_system_general System Services 
     * @subsection bx_system_general-general General
     * @subsubsection bx_system_general-cmts cmts
     * 
     * @code bx_srv('system', 'cmts', ["sys_blocks", 1], 'TemplServices'); @endcode
     * 
     * @code {{~system:cmts:TemplServices["sys_blocks", 1]~}} @endcode
     * 
     * Comments block
     * @param $sObject - comments object name
     * @param $sId - content id assiciated tith the comments
     * 
     * @see BxBaseServices::serviceCmts
     */
    /** 
     * @ref bx_system_general-cmts "cmts"
     */
    public function serviceCmts ($sObject, $sId)
    {
        $o = BxDolCmts::getObjectInstance($sObject, $sId);
        if (!$o || !$o->isEnabled())
            return '';
        return $o->getCommentsBlock(array(), array('in_designbox' => false, 'show_empty' => true));
    }

    public function _serviceProfileFunc ($sFunc, $iContentId, $aParams = array())
    {
        if (!$iContentId)
            return false;
        if (!($oAccount = BxDolAccount::getInstance($iContentId)))
            return false;

        return $oAccount->$sFunc(false, $aParams);
    }

    public function serviceAlertResponseProcessInstalled()
    {
        BxDolTranscoderImage::registerHandlersSystem();
    }

    public function serviceAlertResponseProcessStorageChange ($oAlert)
    {
        if ('sys_storage_default' != $oAlert->aExtras['option'])
            return;

        $aStorages = BxDolStorageQuery::getStorageObjects();
        foreach ($aStorages as $r) {
            if (0 == $r['current_size'] && 0 == $r['current_number'] && ($oStorage = BxDolStorage::getObjectInstance($r['object'])))
                $oStorage->changeStorageEngine($oAlert->aExtras['value']);
        }

    }

    public function serviceGetOptionsProfileBot()
    {
        $aResult = array(
            array('key' => '', 'value' => _t('_Select_one'))
        );

        $aAccountsIds = BxDolAccountQuery::getInstance()->getOperators();
        foreach($aAccountsIds as $iAccountId) {
            $aProfilesIds = BxDolAccount::getInstance($iAccountId)->getProfilesIds(true, false);
            foreach($aProfilesIds as $iProfileId) {
                $oProfile = BxDolProfile::getInstance($iProfileId);
                $aResult[] = array(
                    'key' => $iProfileId,
                    'value' => _t('_sys_profile_with_type', $oProfile->getDisplayName(), $oProfile->getModule()),
                );
            }
        }

        return $aResult;
    }
    
    public function serviceGetOptionsModuleListForPrivacySelector()
    {
        $aResult = array();
        $BxDolModuleQuery = BxDolModuleQuery::getInstance();
        $aModules = $BxDolModuleQuery->getModulesBy(array('type' => 'modules', 'active' => 1));
        foreach($aModules as $aModule){
            $oModule = BxDolModule::getInstance($aModule['name']);
            if($oModule instanceof iBxDolContentInfoService){
                if (!BxDolRequest::serviceExists($aModule['name'], 'act_as_profile'))
                    continue;
                $aResult[$aModule['name']] = $aModule['title'];
            }
        }
        return $aResult;
    }
    

    public function serviceGetOptionsCaptchaDefault()
    {
        $aResults = [];
        $aObjects = BxDolCaptchaQuery::getObjects();
        foreach($aObjects as $aObject)
            $aResults[$aObject['object']] = $aObject['title'];

        return $aResults;
    }

    public function serviceGetOptionsEmbedDefault()
    {
        $aResults = array(
            '' => _t('_None')
        );

        $aObjects = BxDolEmbedQuery::getObjects();
        foreach($aObjects as $aObject)
            $aResults[$aObject['object']] = $aObject['title'];

        return $aResults;
    }

    public function serviceGetOptionsLocationFieldDefault()
    {
        $aResults = array();

        $aObjects = BxDolLocationFieldQuery::getObjects();
        foreach($aObjects as $aObject)
            $aResults[$aObject['object']] = _t($aObject['title']);

        return $aResults;
    }

    public function serviceGetOptionsLocationMapDefault()
    {
        $aResults = array();

        $aObjects = BxDolLocationMapQuery::getObjects();
        foreach($aObjects as $aObject)
            $aResults[$aObject['object']] = _t($aObject['title']);

        return $aResults;
    }

    public function serviceGetOptionsSocketsFieldDefault()
    {
        $aResults = [];
        $aObjects = ['sys_sockets_disabled', 'sys_sockets_soketi'];
        foreach($aObjects as $sObject)
            $aResults[$sObject] = _t('_adm_stg_cpt_option_sys_sockets_type_' . $sObject);
        return $aResults;
    }

    public function serviceGetOptionsLocationLeafletGetProviders()
    {
        return BxDolLocationMapLeaflet::getProviders();
    }

    public function serviceGetOptionsRelations()
    {
        $aModules = BxDolModuleQuery::getInstance()->getModulesBy(array('type' => 'modules', 'active' => 1));

        $aProfiles = array();
        foreach($aModules as $aModule) {
            $sMethod = 'act_as_profile';
            if(!BxDolRequest::serviceExists($aModule['name'], $sMethod) || !BxDolService::call($aModule['name'], $sMethod))
                continue;

            $aProfiles[$aModule['name']] = _t('_' . $aModule['name']);
        }

        $aResults = array();
        foreach($aProfiles as $sName1 => $sTitle1)
            foreach($aProfiles as $sName2 => $sTitle2)
                $aResults[$sName1 . '_' . $sName2] = $sTitle1 . ' - ' . $sTitle2;

        return $aResults;
    }

    public function serviceGetOptionsCfProhibited()
    {
        return BxDolFormQuery::getDataItems('sys_content_filter');
    }

    public function serviceGetOptionsCfUnauthenticated()
    {
        return BxDolFormQuery::getDataItems('sys_content_filter');
    }

    public function serviceGetOptionsTaiwindDefault()
    {
        $sPath = BX_DIRECTORY_PATH_PLUGINS_PUBLIC . 'tailwind/css';
        $aExcludes = ['.', '..', 'tailwind.css'];

        $aResults = [];
        if(($oHandle = opendir($sPath)) !== false) {
            while(($sFile = readdir($oHandle)) !== false) {
                if(in_array($sFile, $aExcludes))
                    continue;

                if(($oHandlerFile = fopen($sPath . '/' . $sFile, 'r')) === false)
                    continue;

                if(($sContent = fread($oHandlerFile, 1024)) !== false && strpos($sContent, '@tailwind base') === false)
                    $aResults[$sFile] = $sFile;

                fclose($oHandlerFile);
            }

            closedir($oHandle);
        }

        return $aResults;
    }

    public function serviceGetOptionsApiMenuTop()
    {
        $aMenus = BxDolMenuQuery::getMenuObjects();

        $aResults = ['' => _t('_sys_please_select')];
        foreach($aMenus as $aMenu)
            if($aMenu['module'] == 'system')
                $aResults[$aMenu['object']] = _t($aMenu['title']);

        return $aResults;
    }

    public function serviceGetOptionsAgentsProfile($bSelectOne = true)
    {
        $aResult = [];
        if($bSelectOne)
            $aResult[] = ['key' => '', 'value' => _t('_Select_one')];

        $aAccountsIds = BxDolAccountQuery::getInstance()->getOperators();
        foreach($aAccountsIds as $iAccountId) {
            $aProfilesIds = BxDolAccount::getInstance($iAccountId)->getProfilesIds(true, false);
            foreach($aProfilesIds as $iProfileId)
                if(($oProfile = BxDolProfile::getInstance($iProfileId)) !== false && ($sProfileModule = $oProfile->getModule()) != 'system')
                    $aResult[] = [
                        'key' => $iProfileId,
                        'value' => _t('_sys_profile_with_type', $oProfile->getDisplayName(), _t('_' . $sProfileModule))
                    ];
        }

        return $aResult;
    }

    public function serviceRedirect($sUrl = false)
    {
        if (!getParam('sys_confirmation_before_redirect')) {
            BxDolTemplate::getInstance()->displayPageNotFound();
            exit;
        }

        if (false === $sUrl)
            $sUrl = bx_get('url');

        if (!$sUrl || !preg_match('@^https?://@', $sUrl)) {
            http_response_code(500);
            return MsgBox(_t('_error occured'));
        }
        else {

            $bSpam = null;
            bx_alert('system', 'check_spam_url', 0, getLoggedId(), array('is_spam' => &$bSpam, 'content' => &$sUrl, 'where' => 'redirect'));

            $sLangKey = $bSpam ? '_sys_redirect_confirmation_harmful' : '_sys_redirect_confirmation';
            return BxDolTemplate::getInstance()->parseHtmlByName('redirect.html', [
                'text' => _t($sLangKey, bx_process_output($sUrl), getParam('site_title')),
                'url' => bx_js_string($sUrl, BX_ESCAPE_STR_APOS),
            ]);
        }
    }

    public function serviceGetBadge($aBadge, $bIsCompact = false)
    {
        $sClass = '';
		$sStyleFont = '';
		$sStyleBg = '';
		if ($aBadge['color'] == '')
			$aBadge['color'] = 'purple-600';
		
        if ($bIsCompact && $aBadge['icon'] != ''){
            $aBadge['is_icon_only'] = 1;
        }
        if ($aBadge['is_icon_only'] == 1){
            $sClass .= ' bx-badge-compact';
            if (substr_count($aBadge['color'], 'rgb('))
                $sStyleFont = 'color: ' . $aBadge['color'];
            else
                $sClass .= ' text-' . $aBadge['color'];
        }
		else{
            if (substr_count($aBadge['color'], 'rgb('))
                $sStyleBg = 'background-color: ' . $aBadge['color'];
            else
                $sClass .= ' bg-' . $aBadge['color'];
		}
        
        return BxDolTemplate::getInstance()->parseHtmlByName('badge.html', array(
            'bx_if:content' => array(
                'condition' => $aBadge['is_icon_only'] != '1',
                'content' => array('content' => _t($aBadge['text'])),
            ),
            'bx_if:icon' => array(
                'condition' => $aBadge['icon'] != '',
                'content' => array('content' => BxDolTemplate::getInstance()->getIcon($aBadge['icon'], array('class' => 'bx-badge-icon sys-colored', 'style' => $sStyleFont))),
            ),
            'title' => $aBadge['text'],
			'style_font' => $sStyleFont,
			'style_bg' => $sStyleBg,
            'class' => $sClass,
            )
    	);
    }
    
    public function serviceGetFooter()
    {
        return BxDolTemplate::getInstance()->parseHtmlByName('footer.html', []);
    }

    private function _getSearchObject()
    {
        $sClass = 'BxTemplSearch';
        $sElsName = 'bx_elasticsearch';
        $sElsMethod = 'is_configured';
        if(BxDolRequest::serviceExists($sElsName, $sElsMethod) && BxDolService::call($sElsName, $sElsMethod) && !bx_get('cat') && !bx_get('type')) {
            $oModule = BxDolModule::getInstance($sElsName);
            bx_import('Search', $oModule->_aModule);
            $sClass = 'BxElsSearch';
        }
        bx_alert('system', 'search_keyword', 0, 0, array('class' => &$sClass));

        $oSearch = new $sClass(bx_get('section'));
        $oSearch->setLiveSearch(bx_get('live_search') ? 1 : 0);
        $oSearch->setMetaType(bx_process_input(bx_get('type')));
        $oSearch->setCategoryObject(bx_process_input(bx_get('cat')));

        return $oSearch;
    }
}

/** @} */
