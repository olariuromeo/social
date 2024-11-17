<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaCore UNA Core
 * @{
 */

class BxDolAIQuery extends BxDolDb
{
    public function __construct()
    {
        parent::__construct();
    }

    static public function getModelObject($iId)
    {
        $oDb = BxDolDb::getInstance();

        $aModel = $oDb->getRow("SELECT * FROM `sys_agents_models` WHERE `id` = :id", ['id' => $iId]);
        if(!$aModel || !is_array($aModel))
            return false;

        return $aModel;
    }

    static public function getProviderObject($iId)
    {
        $oDb = BxDolDb::getInstance();

        $aProvider = $oDb->getRow("SELECT * FROM `sys_agents_providers` WHERE `id` = :id", ['id' => $iId]);
        if(!$aProvider || !is_array($aProvider))
            return false;

        // get type
        $aProviderType = $oDb->getRow("SELECT * FROM `sys_agents_provider_types` WHERE `id` = :id", ['id' => $aProvider['type_id']]);
        if(!$aProviderType || !is_array($aProviderType))
            return false;

        $aProvider['type'] = $aProviderType;

        // get options
        $sQuery = "SELECT
               `tpo`.`name` AS `name`,
               `tpv`.`value` AS `value`
            FROM `sys_agents_provider_options` AS `tpo`
            LEFT JOIN `sys_agents_providers_values` AS `tpv` ON `tpo`.`id` = `tpv`.`option_id` AND `tpv`.`provider_id` = :provider_id
            WHERE 1 ORDER BY `tpo`.`order`";

        $aProvider['options'] = $oDb->getAllWithKey($sQuery, 'name', [
            'provider_id' => $iId
        ]);

        return $aProvider;
    }

    static public function getProviderIdByName($sName)
    {
        return (int)BxDolDb::getInstance()->getOne("SELECT `id` FROM `sys_agents_providers` WHERE `name`=:name LIMIT 1", [
            'name' => $sName
        ]);
    }
    
    static public function getAssistantObject($iId)
    {
        $oDb = BxDolDb::getInstance();

        $aAssistant = $oDb->getRow("SELECT * FROM `sys_agents_assistants` WHERE `id` = :id", ['id' => $iId]);
        if(!$aAssistant || !is_array($aAssistant))
            return false;

        return $aAssistant;
    }

    public function getModelsBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
    	$sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `id`=:id";
                break;

            case 'all_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'title';
                $aMethod['params'][3] = [];

                if(isset($aParams['for_asst'])) {
                    $aMethod['params'][3]['for_asst'] = $aParams['for_asst'];

                    $sWhereClause .= " AND `for_asst`=:for_asst";
                }

                if(isset($aParams['active'])) {
                    $aMethod['params'][3]['active'] = $aParams['active'];

                    $sWhereClause .= " AND `active`=:active";
                }

                if(isset($aParams['hidden'])) {
                    $aMethod['params'][3]['hidden'] = $aParams['hidden'];

                    $sWhereClause .= " AND `hidden`=:hidden";
                }
                break;
        }

        $aMethod['params'][0] = "SELECT * 
            FROM `sys_agents_models`
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }

    public function getAutomatorsBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
        $sSelectClause = "`taa`.*";
    	$sJoinClause = $sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `taa`.`id`=:id";
                break;

            case 'id_full':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sSelectClause .= ", `tam`.`name` AS `model_name`, `tam`.`title` AS `model_title`, `tam`.`key` AS `model_key`, `tam`.`params` AS `model_params`";
                $sJoinClause .= " LEFT JOIN `sys_agents_models` AS `tam` ON `taa`.`model_id`=`tam`.`id`";
                $sWhereClause .= " AND `taa`.`id`=:id";
                break;
            
            case 'events':
                $aMethod['params'][1] = [
                    'type' => BX_DOL_AI_AUTOMATOR_EVENT,
                    'alert_unit' => $aParams['alert_unit'],
                    'alert_action' => $aParams['alert_action']
                ];

                $sWhereClause .= " AND `taa`.`type`=:type AND `taa`.`alert_unit`=:alert_unit AND `taa`.`alert_action`=:alert_action";

                if(isset($aParams['active'])) {
                    $aMethod['params'][1]['active'] = (int)$aParams['active'];

                    $sWhereClause .= " AND `taa`.`active`=:active";
                }
                break;
                
            case 'schedulers':
                $aMethod['params'][1] = [
                    'type' => BX_DOL_AI_AUTOMATOR_SCHEDULER,
                ];

                $sWhereClause .= " AND `taa`.`type`=:type";

                if(isset($aParams['active'])) {
                    $aMethod['params'][1]['active'] = (int)$aParams['active'];

                    $sWhereClause .= " AND `taa`.`active`=:active";
                }
                break;

            case 'webhooks':
                $aMethod['params'][1] = [
                    'type' => BX_DOL_AI_AUTOMATOR_WEBHOOK,
                ];

                $sWhereClause .= " AND `taa`.`type`=:type";

                if(isset($aParams['provider_id'])) {
                    $aMethod['params'][1]['provider_id'] = (int)$aParams['provider_id'];

                    $sJoinClause = "INNER JOIN `sys_agents_automators_providers` AS `tap` ON `taa`.`id`=`tap`.`automator_id`";
                    $sWhereClause .= " AND `tap`.`provider_id`=:provider_id";
                }

                if(isset($aParams['active'])) {
                    $aMethod['params'][1]['active'] = (int)$aParams['active'];

                    $sWhereClause .= " AND `taa`.`active`=:active";
                }
                break;

            case 'providers_by_id_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'provider_id';
                $aMethod['params'][3] = [
                    'id' => $aParams['id']
                ];

                $sSelectClause = "`taap`.`id`, `taap`.`provider_id`";
                $sJoinClause = "INNER JOIN `sys_agents_automators_providers` AS `taap` ON `taa`.`id`=`taap`.`automator_id`";
                $sWhereClause = " AND `taa`.`id`=:id";
                break;
            
            case 'helpers_by_id_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'helper_id';
                $aMethod['params'][3] = [
                    'id' => $aParams['id']
                ];

                $sSelectClause = "`taah`.`id`, `taah`.`helper_id`";
                $sJoinClause = "INNER JOIN `sys_agents_automators_helpers` AS `taah` ON `taa`.`id`=`taah`.`automator_id`";
                $sWhereClause = " AND `taa`.`id`=:id";
                break;
            
            case 'assistants_by_id_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'assistant_id';
                $aMethod['params'][3] = [
                    'id' => $aParams['id']
                ];

                $sSelectClause = "`taaa`.`id`, `taaa`.`assistant_id`";
                $sJoinClause = "INNER JOIN `sys_agents_automators_assistants` AS `taaa` ON `taa`.`id`=`taaa`.`automator_id`";
                $sWhereClause = " AND `taa`.`id`=:id";
                break;
        }

        $aMethod['params'][0] = "SELECT " . $sSelectClause . "
            FROM `sys_agents_automators` AS `taa` " . $sJoinClause . "
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }

    public function updateAutomators($aSetClause, $aWhereClause)
    {
        if(empty($aSetClause) || empty($aWhereClause))
            return false;

        return (int)$this->query("UPDATE `sys_agents_automators` SET " . $this->arrayToSQL($aSetClause) . " WHERE " . $this->arrayToSQL($aWhereClause)) > 0;
    }

    public function insertAutomatorProvider($aParamsSet)
    {
        if(empty($aParamsSet))
            return false;

        return (int)$this->query("INSERT INTO `sys_agents_automators_providers` SET " . $this->arrayToSQL($aParamsSet)) > 0 ? (int)$this->lastId() : false;
    }

    public function updateAutomatorProvider($aParamsSet, $aParamsWhere)
    {
        if(empty($aParamsSet) || empty($aParamsWhere))
            return false;

        return $this->query("UPDATE `sys_agents_automators_providers` SET " . $this->arrayToSQL($aParamsSet) . " WHERE " . $this->arrayToSQL($aParamsWhere, " AND "));
    }

    public function deleteAutomatorProviders($aParamsWhere)
    {
        if(empty($aParamsWhere))
            return false;

        return (int)$this->query("DELETE FROM `sys_agents_automators_providers` WHERE " . $this->arrayToSQL($aParamsWhere)) > 0;
    }
    
    public function deleteAutomatorProvidersById($mixedId)
    {
        if(!is_array($mixedId))
            $mixedId = [$mixedId];

        return (int)$this->query("DELETE FROM `sys_agents_automators_providers` WHERE `id` IN (" . $this->implode_escape($mixedId) . ")") > 0;
    }

    public function insertAutomatorHelper($aParamsSet)
    {
        if(empty($aParamsSet))
            return false;

        return (int)$this->query("INSERT INTO `sys_agents_automators_helpers` SET " . $this->arrayToSQL($aParamsSet)) > 0 ? (int)$this->lastId() : false;
    }

    public function updateAutomatorHelper($aParamsSet, $aParamsWhere)
    {
        if(empty($aParamsSet) || empty($aParamsWhere))
            return false;

        return $this->query("UPDATE `sys_agents_automators_helpers` SET " . $this->arrayToSQL($aParamsSet) . " WHERE " . $this->arrayToSQL($aParamsWhere, " AND "));
    }

    public function deleteAutomatorHelpers($aParamsWhere)
    {
        if(empty($aParamsWhere))
            return false;

        return (int)$this->query("DELETE FROM `sys_agents_automators_helpers` WHERE " . $this->arrayToSQL($aParamsWhere)) > 0;
    }

    public function deleteAutomatorHelpersById($mixedId)
    {
        if(!is_array($mixedId))
            $mixedId = [$mixedId];

        return (int)$this->query("DELETE FROM `sys_agents_automators_helpers` WHERE `id` IN (" . $this->implode_escape($mixedId) . ")") > 0;
    }

    public function insertAutomatorAssistant($aParamsSet)
    {
        if(empty($aParamsSet))
            return false;

        return (int)$this->query("INSERT INTO `sys_agents_automators_assistants` SET " . $this->arrayToSQL($aParamsSet)) > 0 ? (int)$this->lastId() : false;
    }

    public function updateAutomatorAssistant($aParamsSet, $aParamsWhere)
    {
        if(empty($aParamsSet) || empty($aParamsWhere))
            return false;

        return $this->query("UPDATE `sys_agents_automators_assistants` SET " . $this->arrayToSQL($aParamsSet) . " WHERE " . $this->arrayToSQL($aParamsWhere, " AND "));
    }

    public function deleteAutomatorAssistants($aParamsWhere)
    {
        if(empty($aParamsWhere))
            return false;

        return (int)$this->query("DELETE FROM `sys_agents_automators_assistants` WHERE " . $this->arrayToSQL($aParamsWhere)) > 0;
    }
    
    public function deleteAutomatorAssistantsById($mixedId)
    {
        if(!is_array($mixedId))
            $mixedId = [$mixedId];

        return (int)$this->query("DELETE FROM `sys_agents_automators_assistants` WHERE `id` IN (" . $this->implode_escape($mixedId) . ")") > 0;
    }

    public function getProviderTypesBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
    	$sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `id`=:id";
                break;

            case 'name':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'name' => $aParams['name']
                ];

                $sWhereClause .= " AND `name`=:name";
                break;

            case 'all_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'title';

                if(isset($aParams['active'])) {
                    $aMethod['params'][3] = [
                        'active' => $aParams['active']
                    ];

                    $sWhereClause = " AND `active`=:active";
                }
                break;
        }

        $aMethod['params'][0] = "SELECT * 
            FROM `sys_agents_provider_types`
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }

    public function getProviderOptionsBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
    	$sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `id`=:id";
                break;

            case 'provider_type_id':
            	$aMethod['params'][1] = [
                    'provider_type_id' => $aParams['provider_type_id']
                ];

                $sWhereClause .= " AND `provider_type_id`=:provider_type_id";
                break;
        }

        $aMethod['params'][0] = "SELECT * 
            FROM `sys_agents_provider_options`
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }

    public function getProvidersBy($aParams = [])
    {
    	$aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];

    	$sSelectClause = "`tp`.*";
    	$sJoinClause = $sWhereClause = $sGroupClause = $sOrderClause = $sLimitClause = "";
        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause = " AND `tp`.`id`=:id";
                break;

            case 'ids':
                $sSelectClause .= ", `tpt`.`name` AS `type_name`";
                $sJoinClause = "INNER JOIN `sys_agents_provider_types` AS `tpt` ON `tp`.`type_id`=`tpt`.`id`";
                $sWhereClause = " AND `tp`.`id` IN (" . $this->implode_escape($aParams['ids']) . ")";
                break;
                 
            case 'options_by_id':
                $aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sSelectClause = "`tpo`.`id`, `tpo`.`name`, `tpo`.`type`, `tpo`.`title`, `tpo`.`description`, `tpv`.`value`";
                $sJoinClause = "LEFT JOIN `sys_agents_provider_options` AS `tpo` ON `tp`.`type_id`=`tpo`.`provider_type_id` LEFT JOIN `sys_agents_providers_values` AS `tpv` ON `tp`.`id`=`tpv`.`provider_id` AND `tpo`.`id`=`tpv`.`option_id`";
                $sWhereClause = " AND `tp`.`id`=:id";
                break;
            
            case 'all_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'name';

                if(isset($aParams['active'])) {
                    $aMethod['params'][3] = [
                        'active' => $aParams['active']
                    ];

                    $sWhereClause = " AND `tp`.`active`=:active";
                }
                break;
        }

        $sOrderClause = !empty($sOrderClause) ? "ORDER BY " . $sOrderClause : $sOrderClause;
        $sLimitClause = !empty($sLimitClause) ? "LIMIT " . $sLimitClause : $sLimitClause;

        $aMethod['params'][0] = "SELECT
                " . $sSelectClause . "
            FROM `sys_agents_providers` AS `tp` " . $sJoinClause . "
            WHERE 1" . $sWhereClause . " " . $sGroupClause . " " . $sOrderClause . " " . $sLimitClause;

        return call_user_func_array(array($this, $aMethod['name']), $aMethod['params']);
    }

    public function insertProviderValue($aParamsSet)
    {
        if(empty($aParamsSet) || !is_array($aParamsSet) || !isset($aParamsSet['value']))
            return false;

        return (int)$this->query("INSERT INTO `sys_agents_providers_values` SET " . $this->arrayToSQL($aParamsSet) . " ON DUPLICATE KEY UPDATE `value`=:value", [
            'value' => $aParamsSet['value']
        ]) > 0 ? (int)$this->lastId() : false;
    }

    public function updateProviderValue($aParamsSet, $aParamsWhere)
    {
        if(empty($aParamsSet))
            return false;

        $sWhereClause = "1";
        if(!empty($aParamsWhere) && is_array($aParamsWhere))
            $sWhereClause = $this->arrayToSQL($aParamsWhere, ' AND ');

        return $this->query("UPDATE `sys_agents_providers_values` SET " . $this->arrayToSQL($aParamsSet) . " WHERE " . $sWhereClause) !== false;
    }

    public function deleteProviderValues($aParamsWhere)
    {
        if(empty($aParamsWhere))
            return false;

        return (int)$this->query("DELETE FROM `sys_agents_providers_values` WHERE " . $this->arrayToSQL($aParamsWhere, ' AND ')) > 0;
    }
    
    public function getHelpersBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
        $sSelectClause = "`th`.*";
    	$sJoinClause = $sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `th`.`id`=:id";
                break;
            case 'name':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'name' => $aParams['name']
                ];

                $sWhereClause .= " AND `th`.`name`=:name";
                break;
            case 'ids':
                $sWhereClause = " AND `th`.`id` IN (" . $this->implode_escape($aParams['ids']) . ")";
                break;

            case 'all_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'name';

                if(isset($aParams['active'])) {
                    $aMethod['params'][3] = [
                        'active' => $aParams['active']
                    ];

                    $sWhereClause = " AND `th`.`active`=:active";
                }
                break;
        }

        $aMethod['params'][0] = "SELECT " . $sSelectClause . "
            FROM `sys_agents_helpers` AS `th` " . $sJoinClause . "
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }

    public function getAssistantsBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
        $sSelectClause = "`ta`.*";
    	$sJoinClause = $sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `ta`.`id`=:id";
                break;

            case 'name':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'name' => $aParams['name']
                ];

                $sWhereClause .= " AND `ta`.`name`=:name";
                break;

            case 'ids':
                $sWhereClause = " AND `ta`.`id` IN (" . $this->implode_escape($aParams['ids']) . ")";
                break;

            case 'all_pairs':
                $aMethod['name'] = 'getPairs';
                $aMethod['params'][1] = 'id';
                $aMethod['params'][2] = 'name';
                $aMethod['params'][3] = [];

                if(isset($aParams['active'])) {
                    $aMethod['params'][3]['active'] = $aParams['active'];

                    $sWhereClause .= " AND `ta`.`active`=:active";
                }

                if(isset($aParams['hidden'])) {
                    $aMethod['params'][3]['hidden'] = $aParams['hidden'];

                    $sWhereClause .= " AND `ta`.`hidden`=:hidden";
                }
                break;
        }

        $aMethod['params'][0] = "SELECT " . $sSelectClause . "
            FROM `sys_agents_assistants` AS `ta` " . $sJoinClause . "
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }
    
    public function updateAssistants($aSetClause, $aWhereClause)
    {
        if(empty($aSetClause) || empty($aWhereClause))
            return false;

        return (int)$this->query("UPDATE `sys_agents_assistants` SET " . $this->arrayToSQL($aSetClause) . " WHERE " . $this->arrayToSQL($aWhereClause)) > 0;
    }

    public function getChatsBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
        $sSelectClause = "`tac`.*";
    	$sJoinClause = $sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `tac`.`id`=:id";
                break;

            case 'name':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'name' => $aParams['name']
                ];

                $sWhereClause .= " AND `tac`.`name`=:name";
                break;

            case 'assistant_id':
                $aMethod['params'][1] = [
                    'assistant_id' => $aParams['assistant_id']
                ];

                $sWhereClause .= " AND `tac`.`assistant_id`=:assistant_id";
                break;

            case 'type':
                $aMethod['params'][1] = [
                    'type' => $aParams['type']
                ];

                $sWhereClause .= " AND `tac`.`type`=:type";
                
                if(isset($aParams['lifetime']) && (int)$aParams['lifetime'] > 0) {
                    $aMethod['params'][1]['lifetime'] = (int)$aParams['lifetime'];

                    $sWhereClause .= " AND (UNIX_TIMESTAMP() - `tac`.`added`) >= :lifetime";
                }
                break;
        }

        $aMethod['params'][0] = "SELECT " . $sSelectClause . "
            FROM `sys_agents_assistants_chats` AS `tac` " . $sJoinClause . "
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }
    
    public function insertChat($aParamsSet)
    {
        if(empty($aParamsSet) || !is_array($aParamsSet))
            return false;

        return (int)$this->query("INSERT INTO `sys_agents_assistants_chats` SET " . $this->arrayToSQL($aParamsSet)) > 0 ? (int)$this->lastId() : false;
    }

    public function updateChats($aSetClause, $aWhereClause)
    {
        if(empty($aSetClause) || empty($aWhereClause))
            return false;

        return (int)$this->query("UPDATE `sys_agents_assistants_chats` SET " . $this->arrayToSQL($aSetClause) . " WHERE " . $this->arrayToSQL($aWhereClause)) > 0;
    }
    
    public function deleteChats($aParamsWhere)
    {
        if(empty($aParamsWhere))
            return false;

        return (int)$this->query("DELETE FROM `sys_agents_assistants_chats` WHERE " . $this->arrayToSQL($aParamsWhere, ' AND ')) > 0;
    }
    
    public function getFilesBy($aParams = [])
    {
        $aMethod = ['name' => 'getAll', 'params' => [0 => 'query']];
        $sSelectClause = "`taf`.*";
    	$sJoinClause = $sWhereClause = "";

        switch($aParams['sample']) {
            case 'id':
            	$aMethod['name'] = 'getRow';
            	$aMethod['params'][1] = [
                    'id' => $aParams['id']
                ];

                $sWhereClause .= " AND `taf`.`id`=:id";
                break;

            case 'assistant_id':
                $aMethod['params'][1] = [
                    'assistant_id' => $aParams['assistant_id']
                ];

                $sWhereClause .= " AND `taf`.`assistant_id`=:assistant_id";

                if(!empty($aParams['name'])) {
                    $aMethod['name'] = 'getRow';
                    $aMethod['params'][1]['name'] = $aParams['name'];

                    $sWhereClause .= " AND `taf`.`name`=:name";
                }
                break;                
        }

        $aMethod['params'][0] = "SELECT " . $sSelectClause . "
            FROM `sys_agents_assistants_files` AS `taf` " . $sJoinClause . "
            WHERE 1" . $sWhereClause;

        return call_user_func_array([$this, $aMethod['name']], $aMethod['params']);
    }

    public function insertFile($aParamsSet)
    {
        if(empty($aParamsSet) || !is_array($aParamsSet))
            return false;

        return (int)$this->query("INSERT INTO `sys_agents_assistants_files` SET " . $this->arrayToSQL($aParamsSet)) > 0 ? (int)$this->lastId() : false;
    }

    public function updateFiles($aSetClause, $aWhereClause)
    {
        if(empty($aSetClause) || empty($aWhereClause))
            return false;

        return (int)$this->query("UPDATE `sys_agents_assistants_files` SET " . $this->arrayToSQL($aSetClause) . " WHERE " . $this->arrayToSQL($aWhereClause)) > 0;
    }

    public function deleteFiles($aParamsWhere)
    {
        if(empty($aParamsWhere))
            return false;

        return (int)$this->query("DELETE FROM `sys_agents_assistants_files` WHERE " . $this->arrayToSQL($aParamsWhere, ' AND ')) > 0;
    }
}

/** @} */
