<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    BaseProfile Base classes for profile modules
 * @ingroup     UnaModules
 *
 * @{
 */

/*
 * Profile based module database queries
 */
class BxBaseModProfileDb extends BxBaseModGeneralDb
{
    public function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }

    public function getContentInfoById ($iContentId)
    {
        $aInfo = $this->getRow("SELECT `c`.*, `p`.`account_id`, `p`.`id` AS `profile_id`, `a`.`email` AS `profile_email`, `a`.`active` AS `profile_last_active`, `a`.`ip` AS `profile_ip`, `p`.`status` AS `profile_status` FROM `" . $this->_oConfig->CNF['TABLE_ENTRIES'] . "` AS `c` INNER JOIN `sys_profiles` AS `p` ON (`p`.`content_id` = `c`.`id` AND `p`.`type` = :type) INNER JOIN `sys_accounts` AS `a` ON (`p`.`account_id` = `a`.`id`) WHERE `c`.`id` = :id", [
            'type' => $this->_oConfig->getName(),
            'id' => $iContentId
        ]);
        
        /**
         * @hooks
         * @hookdef hook-profile-content_info_by_id 'profile', 'content_info_by_id' - hook to modify profile info retrieved by content id
         * - $unit_name - equals `profile`
         * - $action - equals `content_info_by_id`
         * - $object_id - content id
         * - $sender_id - not used
         * - $extra_params - array of additional params with the following array keys:
         *      - `module` - [string] profile module name
         *      - `info` - [array] by ref, profile info array as key&value pairs, can be overridden in hook processing
         * @hook @ref hook-profile-content_info_by_id
         */
        bx_alert('profile', 'content_info_by_id', $iContentId, 0, [
            'module' => $this->_oConfig->getName(), 
            'info' => &$aInfo
        ]);
        return $aInfo;
    }

    public function getContentInfoByProfileId ($iProfileId)
    {
        $aInfo = $this->getRow("SELECT `c`.*, `p`.`account_id`, `p`.`id` AS `profile_id`, `a`.`email` AS `profile_email`, `a`.`active` AS `profile_last_active`, `a`.`ip` AS `profile_ip`, `p`.`status` AS `profile_status` FROM `" . $this->_oConfig->CNF['TABLE_ENTRIES'] . "` AS `c` INNER JOIN `sys_profiles` AS `p` ON (`p`.`content_id` = `c`.`id` AND `p`.`type` = :type) INNER JOIN `sys_accounts` AS `a` ON (`p`.`account_id` = `a`.`id`) WHERE `p`.`id` = :profile_id", [
            'type' => $this->_oConfig->getName(),
            'profile_id' => $iProfileId
        ]);
        
        /**
         * @hooks
         * @hookdef hook-profile-content_info_by_profile_id 'profile', 'content_info_by_profile_id' - hook to modify profile info retrieved by profile id
         * - $unit_name - equals `profile`
         * - $action - equals `content_info_by_profile_id`
         * - $object_id - profile id
         * - $sender_id - not used
         * - $extra_params - array of additional params with the following array keys:
         *      - `module` - [string] profile module name
         *      - `info` - [array] by ref, profile info array as key&value pairs, can be overridden in hook processing
         * @hook @ref hook-profile-content_info_by_profile_id
         */
        bx_alert('profile', 'content_info_by_profile_id', $iProfileId, 0, [
            'module' => $this->_oConfig->getName(), 
            'info' => &$aInfo
        ]);
        return $aInfo;
    }

    public function resetContentPictureByFileId($iFileId, $sFieldPicture)
    {
        return $this->query("UPDATE `" . $this->_oConfig->CNF['TABLE_ENTRIES'] . "` SET `" . $sFieldPicture . "` = 0 WHERE `" . $sFieldPicture . "` = :file", [
    		'file' => $iFileId,
        ]);
    }

    public function updateContentPictureById($iContentId, $iProfileId, $iPictureId, $sFieldPicture)
    {
    	$aBindings = array(
    		'pic' => $iPictureId,
    		'id' => $iContentId
    	);

        $sWhere = '';
        if ($iProfileId) {
        	$aBindings['author'] = $iProfileId;

            $sWhere = " AND `author` = :author ";
        }

        $sQuery = "UPDATE `" . $this->_oConfig->CNF['TABLE_ENTRIES'] . "` SET `" . $sFieldPicture . "` = :pic WHERE `id` = :id" . $sWhere;
        return $this->query($sQuery, $aBindings);
    }

    public function searchByTerm($sTerm, $iLimit)
    {
        if (!$this->_oConfig->CNF['FIELDS_QUICK_SEARCH'])
            return array();

		$aBindings = array(
			'type' => $this->_oConfig->getName(),
			'status' => BX_PROFILE_STATUS_ACTIVE
		);
        $sSelect = "`c`.`id` AS `content_id`, `p`.`account_id`, `p`.`id` AS `profile_id`, `p`.`status` AS `profile_status` ";
        
        $sJoin = "INNER JOIN `sys_profiles` AS `p` ON (`p`.`content_id` = `c`.`id` AND `p`.`type` = :type) INNER JOIN `sys_accounts` AS `a` ON (`a`.`id` =  `p`.`account_id`)";
        
        $sWhere = '';
        foreach ($this->_oConfig->CNF['FIELDS_QUICK_SEARCH'] as $sField) {
        	$aBindings[$sField] = '%' . $sTerm . '%';

            $sWhere .= " OR `c`.`$sField` LIKE :" . $sField;
        }
        $sWhere = "`p`.`status` = :status AND (0 $sWhere) ";

        $sOrderBy = $this->prepareAsString(" ORDER BY `a`.`logged` DESC LIMIT ?", (int)$iLimit);

        /**
         * @hooks
         * @hookdef hook-profile-search_by_term 'profile', 'search_by_term' - hook to modify a list of profiles found by term
         * - $unit_name - equals `profile`
         * - $action - equals `search_by_term`
         * - $object_id - not used
         * - $sender_id - not used
         * - $extra_params - array of additional params with the following array keys:
         *      - `module` - [string] profile module name
         *      - `table` - [string] db table name
         *      - `select` - [string] by ref, 'select' part of SQL query, can be overridden in hook processing
         *      - `join` - [string] by ref, 'join' part of SQL query, can be overridden in hook processing
         *      - `where` - [string] by ref, 'where' part of SQL query, can be overridden in hook processing
         *      - `order_by` - [string] by ref, 'order' part of SQL query, can be overridden in hook processing
         * @hook @ref hook-profile-search_by_term
         */
        bx_alert('profile', 'search_by_term', 0, 0, [
            'module' => $this->_oConfig->getName(), 
            'table' => $this->_oConfig->CNF['TABLE_ENTRIES'], 
            'select' => &$sSelect,  
            'join' => &$sJoin, 
            'where' => &$sWhere, 
            'order_by' => &$sOrderBy
        ]);
        
        return $this->getAll("SELECT " . $sSelect . " FROM `" . $this->_oConfig->CNF['TABLE_ENTRIES'] . "` AS `c` " . $sJoin . " WHERE " . $sWhere . $sOrderBy, $aBindings);
    }

    protected function _getEntriesBySearchIds($aParams, &$aMethod, &$sSelectClause, &$sJoinClause, &$sWhereClause, &$sOrderClause, &$sLimitClause)
    {
        $CNF = &$this->_oConfig->CNF;

       
        $aMethod['params'][1] = array_merge($aMethod['params'][1], array(
            'profile_type' => $this->_oConfig->getName()
        ));  
        
        $sJoinClause .= " LEFT JOIN `sys_profiles` AS `tp` ON `" . $CNF['TABLE_ENTRIES'] . "`.`" . $CNF['FIELD_ID'] . "`=`tp`.`content_id` AND `tp`.`type`=:profile_type ";
        
        $sWhereClause .= " AND `tp`.`status`='active'";
        
        if(isset($aParams['search_params']['online'])) {
            $aMethod['params'][1] = array_merge($aMethod['params'][1], array(
                'online_time' => (int)getParam('sys_account_online_time')
            ));

            $sJoinClause .= "
            	INNER JOIN `sys_accounts` AS `ta` ON `tp`.`account_id`=`ta`.`id` 
            	INNER JOIN `sys_sessions` AS `ts` ON `tp`.`account_id`=`ts`.`user_id` 
                ";

            $sWhereClause .= " AND `ta`.`profile_id`=`tp`.`id` AND `ts`.`date` > (UNIX_TIMESTAMP() - 60 * :online_time)";

            unset($aParams['search_params']['online']);
        }

        parent::_getEntriesBySearchIds($aParams, $aMethod, $sSelectClause, $sJoinClause, $sWhereClause, $sOrderClause, $sLimitClause);        
    }
    
    public function getEntriesNumByParams ($aParams = [])
    {
        $CNF = &$this->_oConfig->CNF;
        
        $sSql = "SELECT COUNT(*) FROM `" . $this->_oConfig->CNF['TABLE_ENTRIES'] . "` 
            INNER JOIN `sys_profiles` ON `" . $CNF['TABLE_ENTRIES'] . "`.`" . $CNF['FIELD_ID'] . "`=`sys_profiles`.`content_id` AND `sys_profiles`.`type`=?
            WHERE 1";
        
        foreach($aParams as $aValue){
            $sSql .= ' AND ' . (isset($aValue['table'])? '`' . $aValue['table'] .'`.' : '') . '`' . $aValue['key'] ."` " . $aValue['operator'] . " '" . $aValue['value'] . "'";
        }
        
        $sQuery = $this->prepare($sSql, $this->_oConfig->getName());
        return $this->getOne($sQuery);
    }
}

/** @} */
