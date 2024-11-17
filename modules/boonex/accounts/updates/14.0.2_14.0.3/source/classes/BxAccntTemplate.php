<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Accounts Accounts
 * @ingroup     UnaModules
 *
 * @{
 */

class BxAccntTemplate extends BxBaseModGeneralTemplate
{
    function __construct(&$oConfig, &$oDb)
    {
    	$this->MODULE = 'bx_accounts';
        parent::__construct($oConfig, $oDb);
    }

    public function getProfilesByAccount($aContentInfo, $iMaxVisible = 2, $iMaxInPopup = 20)
    {
        $CNF = &$this->_oConfig->CNF;
        $iMax = $iMaxVisible + $iMaxInPopup;

        $aProfiles = BxDolAccount::getInstance($aContentInfo['id'])->getProfiles();
        $iProfiles = count($aProfiles);

        $aTmplVars = [
            'class_cnt' => '',
            'bx_repeat:profiles' => [],
            'bx_if:profiles_more' => [
                'condition' => $iProfiles > $iMaxVisible,
                'content' => [
                    'html_id' => $this->_oConfig->getHtmlIds('profile_more_popup') . $aContentInfo['id'],
                    'more' => _t('_bx_accnt_txt_more', $iProfiles - $iMaxVisible),
                    'more_attr' => bx_html_attribute(_t('_bx_accnt_txt_see_more')),
                    'popup' => '',
                ],
            ],
        ];

        $aTmplVarsPopup = [
            'bx_repeat:profiles' => [],
        ];

        if($iProfiles > $iMax)
            $aProfiles = array_slice($aProfiles, 0, $iMax);

        $i = 0;
        foreach ($aProfiles as $aProfile) {
            $oProfile = BxDolProfile::getInstance((int)$aProfile['id']);
            if(!$oProfile)
                continue;

            $iId = $oProfile->id();
            $sName = $oProfile->getDisplayName();
            $aTmplVarsProfile = [
                'html_id' => $this->_oConfig->getHtmlIds('profile') . $iId,
                'id' => $iId,
                'url' => $oProfile->getUrl(),
                'name' => strmaxtextlen($sName, $CNF['PARAM_PROFILE_NAME_LENGTH_MAX'], '...'),
                'name_attr' => bx_html_attribute($sName)
            ];

            if($i < $iMaxVisible)
                $aTmplVars['bx_repeat:profiles'][] = $aTmplVarsProfile;
            if($i >= $iMaxVisible)
                $aTmplVarsPopup['bx_repeat:profiles'][] = $aTmplVarsProfile;

            ++$i;
        }

        if($aTmplVarsPopup['bx_repeat:profiles'])
            $aTmplVars['bx_if:profiles_more']['content']['popup'] = BxTemplFunctions::getInstance()->transBox('', $this->parseHtmlByName('profiles_more.html', $aTmplVarsPopup));

        return $this->parseHtmlByName('profiles.html', $aTmplVars);
    }

    public function getPopupSetRole($aRoles, $iAccountId, $iAccountRole)
    {
        $sJsObject = $this->_oConfig->getJsObject('manage_tools');
        $sHtmlIdPrefix = str_replace('_', '-', $this->_oConfig->getName()) . '-role';
        $sMaskOnClick = '%s.onClickSetOperatorRoleSubmit(this, %d, %d)';

        $aRoles = array_merge(array(array('id' => 0, 'title' => '_bx_accnt_txt_not_operator')), $aRoles);

        $aTmplVarsRoles = array();
        foreach($aRoles as $aRole) {
            $iRole = (int)$aRole['id'];
            $bRole = $iRole != 0;

            if($iRole == BX_DOL_STUDIO_ROLE_MASTER)
                continue;

            $aTmplVarsRoles[] = array(
                'id' => $sHtmlIdPrefix . '-' . $iRole, 
                'type' => !$bRole ? 'radio' : 'checkbox',
                'value' => $iRole,
                'onclick' => sprintf($sMaskOnClick, $sJsObject, $iAccountId, $iRole),
                'title' => _t($aRole['title']), 
                'bx_if:show_checked' => array(
                    'condition' => ($bRole && $iAccountRole & (1 << ($iRole - 1))) || (!$bRole && $iAccountRole == 0),
                    'content' => array()
                ),
                'bx_if:show_divider' => array(
                    'condition' => !$bRole,
                    'content' => array()
                )
            );
        }

        return $this->parseHtmlByName('set_role_popup.html', array(
            'bx_repeat:roles' => $aTmplVarsRoles
        ));
    }
}

/** @} */
