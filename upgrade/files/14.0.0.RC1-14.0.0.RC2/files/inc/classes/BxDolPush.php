<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaCore UNA Core
 * @{
 */

class BxDolPush extends BxDolFactory implements iBxDolSingleton
{
    protected $_sAppId;
    protected $_sRestApi;

    protected function __construct()
    {
        if (isset($GLOBALS['bxDolClasses'][get_class($this)]))
            trigger_error ('Multiple instances are not allowed for the class: ' . get_class($this), E_USER_ERROR);

        parent::__construct();

        $this->_sAppId = getParam('sys_push_app_id');
        $this->_sRestApi = getParam('sys_push_rest_api');
    }

    /**
     * Prevent cloning the instance
     */
    public function __clone()
    {
        if (isset($GLOBALS['bxDolClasses'][get_class($this)]))
            trigger_error('Clone is not allowed for the class: ' . get_class($this), E_USER_ERROR);
    }

    /**
     * Get singleton instance of the class
     */
    public static function getInstance()
    {
        if(!isset($GLOBALS['bxDolClasses'][__CLASS__]))
            $GLOBALS['bxDolClasses'][__CLASS__] = new BxDolPush();

        return $GLOBALS['bxDolClasses'][__CLASS__];
    }
    
    /**
     * Get tags to send to PUSH server
     * @param $iProfileId - profile ID
     * @return array of tags
     */
    public static function getTags($iProfileId = false)
    {
        if (false === $iProfileId)
            $iProfileId = bx_get_logged_profile_id();

        $oProfile = BxDolProfile::getInstance($iProfileId);
        $oAccount = $oProfile ? $oProfile->getAccountObject() : null;
        if (!$oProfile || !$oAccount)
            return false;

        $sEmail = $oAccount->getEmail();
        $a = array (
            'user_hash' => encryptUserId($iProfileId),
            'real_name' => $oProfile->getDisplayName(),
            'email' => $sEmail,
            'email_hash' => $sEmail ? hash_hmac('sha256', $sEmail, getParam('sys_push_app_id')) : '',
        );

         /**
         * @hooks
         * @hookdef hook-system-is_confirmed 'system', 'push_tags' - hook on get tags to send to PUSH server
         * - $unit_name - equals `system`
         * - $action - equals `push_tags` 
         * - $object_id - profile_id from current user
         * - $sender_id - profile_id from current user
         * - $extra_params - array of additional params with the following array keys:
         *      - `tags` - [array] by ref, array of tags, can be overridden in hook processing
         * @hook @ref hook-system-push_tags
         */
        bx_alert('system', 'push_tags', $iProfileId, $iProfileId, array('tags' => &$a));

        return $a;
    }

    /**
     * @param $a - array to fill with notification counter per module
     * @return total number of notifications
     */
    public static function getNotificationsCount($iProfileId = 0, &$aBubbles = null)
    {    
        if ('' != trim(getParam('sys_api_url_root_push'))) {
             return bx_srv('bx_notifications', 'get_unread_notifications_num', [$iProfileId]);
        }   
        
        $iMemberIdCookie = null;
        $bLoggedMemberGlobals = null;
        if ($iProfileId && $iProfileId != bx_get_logged_profile_id()) {
            if (getLoggedId())
                $iMemberIdCookie = getLoggedId();
            if (!empty($GLOBALS['logged']['member']))
                $bLoggedMemberGlobals = $GLOBALS['logged']['member'];
            $oProfile = BxDolProfile::getInstance($iProfileId);
            $_COOKIE['memberID'] = $oProfile ? $oProfile->getAccountId() : 0;
            $GLOBALS['logged']['member'] = $oProfile ? true : false;
        }
    
        $aMenusObjects = array('sys_account_notifications', 'sys_toolbar_member');
        foreach ($aMenusObjects as $sMenuObject) {
            if ($iProfileId && $iProfileId != bx_get_logged_profile_id())
                unset($GLOBALS['bxDolClasses']['BxDolMenu!sys_account_notifications']);
            $oMenu = BxDolMenu::getObjectInstance($sMenuObject);
            if ($iProfileId && $iProfileId != bx_get_logged_profile_id())
                unset($GLOBALS['bxDolClasses']['BxDolMenu!sys_account_notifications']);

            $bSave = $oMenu->setDisplayAddons(true);
            $a = $oMenu->getMenuItems();
            $iBubbles = 0;
            foreach ($a as $r) {
                if (!$r['bx_if:addon']['condition'])
                    continue;
                if (null !== $aBubbles)
                    $aBubbles[$r['name']] = $r['bx_if:addon']['content']['addon'];
                $iBubbles += $r['bx_if:addon']['content']['addon'];
            }
        }

        if ($iProfileId && $iProfileId != bx_get_logged_profile_id()) {
            if (null === $iMemberIdCookie)
                unset($_COOKIE['memberID']);
            else
                $_COOKIE['memberID'] = $iMemberIdCookie;

            if (null === $bLoggedMemberGlobals)
                unset($GLOBALS['logged']['member']);
            else
                $GLOBALS['logged']['member'] = $bLoggedMemberGlobals;
        }

        return $iBubbles;
    }

    public function send($iProfileId, $aMessage, $bAddToQueue = false)
    {
        if(empty($this->_sAppId) || empty($this->_sRestApi))
            return false;

        if($bAddToQueue && BxDolQueuePush::getInstance()->add($iProfileId, $aMessage))
            return true;

        $sUrlWeb = $sUrlApp = !empty($aMessage['url']) ? $aMessage['url'] : '';

        if(($sRootUrl = getParam('sys_api_url_root_email')) !== '') {
            if(substr(BX_DOL_URL_ROOT, -1) == '/' && substr($sRootUrl, -1) != '/')
                $sRootUrl .= '/';

            if($sUrlWeb)
                $sUrlWeb = str_replace(BX_DOL_URL_ROOT, $sRootUrl, $sUrlWeb);

            if(empty($aMessage['contents']) && is_array($aMessage['contents']))
                foreach($aMessage['contents'] as $sKey => $sValue)
                    $aMessage['contents'][$sKey] = str_replace(BX_DOL_URL_ROOT, $sRootUrl, $sValue);
        }

        if(($sRootUrl = getParam('sys_api_url_root_push')) !== '') {
            if(substr(BX_DOL_URL_ROOT, -1) == '/' && substr($sRootUrl, -1) != '/')
                $sRootUrl .= '/';

            if($sUrlApp)
                $sUrlApp = str_replace(BX_DOL_URL_ROOT, $sRootUrl, $sUrlApp);
        }
        else
            $sUrlApp = $sUrlWeb;

        $aFields = [
            'app_id' => $this->_sAppId,
            'filters' => [
                ['field' => 'tag', 'key' => 'user_hash', 'relation' => '=', 'value' => encryptUserId($iProfileId)]
            ],
            'contents' => !empty($aMessage['contents']) && is_array($aMessage['contents']) ? $aMessage['contents'] : [],
            'headings' => !empty($aMessage['headings']) && is_array($aMessage['headings']) ? $aMessage['headings'] : [],
            'web_url' => $sUrlWeb,
            'app_url' => $sUrlApp,
            'data' => [
                'url' => $sUrlWeb
            ],
        ];
        
        if (empty($aMessage['icon'])){
            $aMessage['icon'] = BxTemplFunctions::getInstance()->getMainLogoUrl();
        }
        if (!empty($aMessage['icon'])){
            $aFields['chrome_web_icon'] = $aMessage['icon'];
            $aFields['large_icon'] = $aMessage['icon'];
            $aFields['ios_attachments'] = ['id'=> $aMessage['icon']];
        }
        
        if ('on' == getParam('bx_nexus_option_push_notifications_count')) {
            $iBadgeCount = $this->getNotificationsCount($iProfileId);
            $aFields['ios_badgeType'] = 'SetTo';
            $aFields['ios_badgeCount'] = $iBadgeCount;
        }

        $oChannel = curl_init();
        curl_setopt($oChannel, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($oChannel, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $this->_sRestApi
        ]);
        curl_setopt($oChannel, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oChannel, CURLOPT_HEADER, false);
        curl_setopt($oChannel, CURLOPT_POST, true);
        curl_setopt($oChannel, CURLOPT_POSTFIELDS, json_encode($aFields));
        if (getParam('sys_curl_ssl_allow_untrusted') == 'on')
            curl_setopt($oChannel, CURLOPT_SSL_VERIFYPEER, false);

        $sResult = curl_exec($oChannel);
        curl_close($oChannel);

        $oResult = @json_decode($sResult, true);
        if(isset($oResult['errors']))
            foreach($oResult['errors'] as $sError) {  
                bx_log('sys_push', $sError . " Message:" . json_encode($aMessage));
            }

        return $sResult;
    }
}

/** @} */
