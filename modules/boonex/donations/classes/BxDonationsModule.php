<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Donations Donations
 * @ingroup     UnaModules
 *
 * @{
 */

define('BX_DONATIONS_BTYPE_SINGLE', 'single');
define('BX_DONATIONS_BTYPE_RECURRING', 'recurring');

class BxDonationsModule extends BxBaseModGeneralModule
{
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_oConfig->init($this->_oDb);
    }

    /**
     * ACTION METHODS
     */
    public function actionCheckName()
    {
        $CNF = &$this->_oConfig->CNF;

    	$sName = bx_process_input(bx_get('name'));
    	if(empty($sName))
            return echoJson(array());

        $sResult = '';

        $iId = (int)bx_get('id');
        if(!empty($iId)) {
            $aPrice = $this->_oDb->getTypes(array('type' => 'by_id', 'value' => $iId)); 
            if(strcmp($sName, $aPrice[$CNF['FIELD_NAME']]) == 0) 
                $sResult = $sName;
        }

    	echoJson(array(
            'name' => !empty($sResult) ? $sResult : $this->_oConfig->getTypeName($sName)
    	));
    }


    /**
     * SERVICE METHODS
     */
    public function serviceGetTypesBy($aParams)
    {
    	return $this->_oDb->getTypes($aParams);
    }

    public function serviceIncludeCssJs()
    {
        return $this->_oTemplate->getIncludeCssJs();
    }

    public function serviceGetBlockMake()
    {
        return $this->_oTemplate->getBlockMake();
    }

    public function serviceGetBlockList()
    {
        return $this->_getBlockList();
    }

    public function serviceGetBlockListAll()
    {
        return $this->_getBlockList('all');
    }

    public function serviceGetPaymentData()
    {
        $CNF = &$this->_oConfig->CNF;

        $oPermalink = BxDolPermalinks::getInstance();

        $aResult = $this->_aModule;
        $aResult['url_browse_order_common'] = BX_DOL_URL_ROOT . $oPermalink->permalink($CNF['URL_LIST'], array('filter' => '{order}'));
        $aResult['url_browse_order_administration'] = BX_DOL_URL_ROOT . $oPermalink->permalink($CNF['URL_LIST_ALL'], array('filter' => '{order}'));

        return $aResult;
    }

    public function serviceGetCartItem($mixedItemId)
    {
    	$CNF = &$this->_oConfig->CNF;

        if(!$mixedItemId)
            return array();

        $aItem = $this->_oDb->getTypes(array(
            'type' => 'by_' . (is_numeric($mixedItemId) ? 'id' : 'name'), 
            'value' => $mixedItemId
        ));

        if(empty($aItem) || !is_array($aItem))
            return array();

        return array (
            'id' => $aItem[$CNF['FIELD_ID']],
            'author_id' => $this->_oConfig->getOwner(),
            'name' => $aItem[$CNF['FIELD_NAME']],
            'title' => _t($this->_oConfig->isShowTitle() ? $aItem[$CNF['FIELD_TITLE']] : '_bx_donations_txt_cart_item_title'),
            'description' => '',
            'url' => BX_DOL_URL_ROOT . BxDolPermalinks::getInstance()->permalink($CNF['URL_MAKE']),
            'price_single' => $aItem[$CNF['FIELD_PRICE']],
            'price_recurring' => $aItem[$CNF['FIELD_PRICE']],
            'period_recurring' => $aItem[$CNF['FIELD_PERIOD']],
            'period_unit_recurring' => $aItem[$CNF['FIELD_PERIOD_UNIT']],
            'trial_recurring' => ''
        );
    }

    public function serviceGetCartItems($iSellerId)
    {
    	$CNF = &$this->_oConfig->CNF;

        $iSellerId = (int)$iSellerId;
        if(empty($iSellerId))
            return array();

        $aItems = $this->_oDb->getTypes(array('type' => 'all'));
        if(empty($aItems) || !is_array($aItems))
            return array();

        $bShowTitle = $this->_oConfig->isShowTitle();

        $aResult = array();
        foreach($aItems as $aItem)
            $aResult[] = array(
                'id' => $aItem[$CNF['FIELD_ID']],
                'author_id' => $this->_oConfig->getOwner(),
                'name' => $aItem[$CNF['FIELD_NAME']],
                'title' => _t($bShowTitle ? $aItem[$CNF['FIELD_TITLE']] : '_bx_donations_txt_cart_item_title'),
                'description' => '',
                'url' => BX_DOL_URL_ROOT . BxDolPermalinks::getInstance()->permalink($CNF['URL_MAKE']),
                'price_single' => $aItem[$CNF['FIELD_PRICE']],
                'price_recurring' => $aItem[$CNF['FIELD_PRICE']],
                'period_recurring' => $aItem[$CNF['FIELD_PERIOD']],
                'period_unit_recurring' => $aItem[$CNF['FIELD_PERIOD_UNIT']],
                'trial_recurring' => ''
            );

        return $aResult;
    }

    public function serviceRegisterCartItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense)
    {
        return $this->_serviceRegisterItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense, BX_DONATIONS_BTYPE_SINGLE);
    }

    public function serviceRegisterSubscriptionItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense)
    {
        return $this->_serviceRegisterItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense, BX_DONATIONS_BTYPE_RECURRING);
    }

    public function serviceReregisterCartItem($iClientId, $iSellerId, $iItemIdOld, $iItemIdNew, $sOrder)
    {
        return array();
    }

    public function serviceReregisterSubscriptionItem($iClientId, $iSellerId, $iItemIdOld, $iItemIdNew, $sOrder)
    {
        return array();
    }

    public function serviceUnregisterCartItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense)
    {
        return $this->_serviceUnregisterItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense, BX_DONATIONS_BTYPE_SINGLE);
    }

    public function serviceUnregisterSubscriptionItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense)
    {
        return $this->_serviceUnregisterItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense, BX_DONATIONS_BTYPE_RECURRING); 
    }

    public function serviceCancelSubscriptionItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder)
    {
        return true;
    }

    protected function _serviceRegisterItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense, $sType)
    {
        $CNF = &$this->_oConfig->CNF;

    	$aItem = $this->serviceGetCartItem($iItemId);
        if(empty($aItem) || !is_array($aItem))
            return array();

        $aType = $this->_oDb->getTypeById($iItemId);
        if(empty($aType) || !is_array($aType))
            return array();

        if(!$this->_oDb->registerEntry($iClientId, $iItemId, $iItemCount, $sOrder, $sLicense))
            return array();

        bx_alert($this->getName(), 'donation_register', 0, false, array(
            'donation_id' => $iItemId,
            'profile_id' => $iClientId,
            'order' => $sOrder,
            'type' => $sType,
            'count' => $iItemCount
        ));

        $oClient = BxDolProfile::getInstanceMagic($iClientId);
        sendMailTemplate($CNF['ETEMPLATE_DONATED'], 0, $iClientId, array(
            'client_name' => $oClient->getDisplayName(),
        ));

        return $aItem;
    }

    protected function _serviceUnregisterItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrder, $sLicense, $sType)
    {
    	if(!$this->_oDb->unregisterEntry($iClientId, $iItemId, $sOrder, $sLicense))
            return false;

        bx_alert($this->getName(), 'donation_unregister', 0, false, array(
            'donation_id' => $iItemId,
            'profile_id' => $iClientId,
            'order' => $sOrder,
            'type' => $sType,
            'count' => $iItemCount
        ));

    	return true;
    }

    /*
     * INTERNAL METHODS
     */
    protected function _getBlockList($sType = '') 
    {
        $CNF = &$this->_oConfig->CNF;

        $sGrid = $CNF['OBJECT_GRID_LIST' . (!empty($sType) ? '_' . strtoupper($sType) : '')];
        $oGrid = BxDolGrid::getObjectInstance($sGrid);
        if(!$oGrid)
            return '';

        return $oGrid->getCode();
    }
}

/** @} */