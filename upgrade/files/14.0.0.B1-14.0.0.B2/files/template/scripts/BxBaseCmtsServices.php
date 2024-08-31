<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

/**
 * System services related to Comments.
 */
class BxBaseCmtsServices extends BxDol
{
    public function __construct()
    {
        parent::__construct();
    }

    public function serviceGetAuthor($iCmtUniqId)
    {
        $oCmts = BxDolCmts::getObjectInstanceByUniqId($iCmtUniqId);
        if(!$oCmts)
            return [];

        $aCmt = BxDolCmtsQuery::getCommentExtendedByUniqId($iCmtUniqId);
        if(empty($aCmt) || !is_array($aCmt))
            return [];

        return $oCmts->serviceGetAuthor((int)$aCmt['cmt_id']);
    }
    
    public function serviceGetBlockAuthor($sSystem = '', $iObjectId = 0, $iCommentId = 0)
    {
        if(empty($sSystem) && ($sSystem = bx_get('sys')) !== false)
            $sSystem = bx_process_input($sSystem);

        if(empty($iObjectId) && ($iObjectId = bx_get('id')) !== false)
            $iObjectId = bx_process_input($iObjectId, BX_DATA_INT);

        if(empty($iCommentId) && ($iCommentId = bx_get('cmt_id')) !== false)
            $iCommentId = bx_process_input($iCommentId, BX_DATA_INT);

        if(bx_is_api()) {
            $oCmts = BxDolCmts::getObjectInstance($sSystem, $iObjectId, true);
            $aCmt = $oCmts->getCommentRow($iCommentId);

            return [bx_api_get_block('entity_author', [
                'author_data' => BxDolProfile::getData($aCmt['cmt_author_id']),
                'entry_date' => $aCmt['cmt_time'],
                'menu_manage' => [],
                'text' => bx_process_output(strip_tags($oCmts->getObjectTitle($iObjectId))),
                'url' => bx_api_get_relative_url($oCmts->getBaseUrl())
            ])];
        }
        
        return '';
    }

    public function serviceGetInfo($iCmtUniqId, $bSearchableFieldsOnly = true)
    {
        $oCmts = BxDolCmts::getObjectInstanceByUniqId($iCmtUniqId);
        if(!$oCmts)
            return [];

        $aCmt = BxDolCmtsQuery::getCommentExtendedByUniqId($iCmtUniqId);
        if(empty($aCmt) || !is_array($aCmt))
            return [];

        return $oCmts->serviceGetInfo((int)$aCmt['cmt_id'], $bSearchableFieldsOnly);
    }

    public function serviceGetBlockView($sSystem = '', $iObjectId = 0, $iCommentId = 0)
    {
        if(empty($sSystem) && ($sSystem = bx_get('sys')) !== false)
            $sSystem = bx_process_input($sSystem);

        if(empty($iObjectId) && ($iObjectId = bx_get('id')) !== false)
            $iObjectId = bx_process_input($iObjectId, BX_DATA_INT);

        if(empty($iCommentId) && ($iCommentId = bx_get('cmt_id')) !== false)
            $iCommentId = bx_process_input($iCommentId, BX_DATA_INT);

        if(bx_is_api()) {
            $aRv = $this->serviceGetDataApi([
                'module' => $sSystem,
                'object_id' => $iObjectId,
                'comment_id' => $iCommentId
            ]);

            if(isset($aRv['browse']) && is_array($aRv['browse']))
                $aRv['browse']['data']['total_count'] = 0;

            return [$aRv];
        }

        $oCmts = BxDolCmts::getObjectInstance($sSystem, $iObjectId, true);
        if(!$oCmts)
            return '';

        return $oCmts->getCommentBlock($iCommentId);
    }

    public function serviceAlertResponseSysCmtsImagesFileDeleted($oAlert)
    {
        $iUniqId = (int)$oAlert->aExtras['ghost']['content_id'];

        $aCmt = BxDolCmts::getGlobalInfo($iUniqId);
        if(empty($aCmt) || !is_array($aCmt))
            return;

        $oCmts = BxDolCmts::getObjectInstance($aCmt['system_name'], 0, false);
        if(!$oCmts)
            return;

        $oCmts->getQueryObject()->deleteImages($aCmt['system_id'], false, $oAlert->iObject);
    }

    public function serviceGetMenuItemAddonVote($sSystem, $iId, $iCmtId)
    {
        $oCmts = BxDolCmts::getObjectInstance($sSystem, $iId);

        $oVote = $oCmts->getVoteObject($oCmts->getCommentUniqId($iCmtId));
        if($oVote !== false)
            return $oVote->getCounter();

        return '';
    }

    public function serviceGetLiveUpdate($sSystem, $iContentId, $iProfileId, $iCount = 0)
    {
        $oCmts = BxDolCmts::getObjectInstance($sSystem, $iContentId);
        if(!$oCmts || !$oCmts->isEnabled())
            return false;

        $sKey = $oCmts->getNotificationId();

        bx_import('BxDolSession');
        if((int)BxDolSession::getInstance()->getValue($sKey) == 1)
            return false;

        $iCountNew = $oCmts->getCommentsCount($iContentId, -1, BX_CMT_FILTER_OTHERS);
        if($iCountNew == $iCount)
            return false;

        return array(
            'count' => $iCountNew, // required
            'method' => $oCmts->getJsObjectName() . '.showLiveUpdate(oData)', // required
            'data' => array(
                'code' => $oCmts->getLiveUpdate($iCount, $iCountNew)
            ),  // optional, may have some additional data to be passed in JS method provided using 'method' param above.
        );
    }

    public function serviceGetLiveUpdates($sSystem, $iContentId, $iProfileId, $iCount = 0)
    {
        $oCmts = BxDolCmts::getObjectInstance($sSystem, $iContentId);
        if(!$oCmts || !$oCmts->isEnabled())
            return false;

        $sKey = $oCmts->getNotificationId();

        bx_import('BxDolSession');
        if((int)BxDolSession::getInstance()->getValue($sKey) == 1)
            return false;

        $iCountNew = $oCmts->getCommentsCount($iContentId, -1, BX_CMT_FILTER_OTHERS);
        if($iCountNew == $iCount)
            return false;

        return array(
            'count' => $iCountNew, // required
            'method' => $oCmts->getJsObjectName() . '.showLiveUpdates(oData)', // required
            'data' => array(
                'code' => $oCmts->getLiveUpdates($iCount, $iCountNew)
            ),  // optional, may have some additional data to be passed in JS method provided using 'method' param above.
        );
    }
    
    public function serviceManageTools($sType = 'common')
    {
        $oGrid = BxDolGrid::getObjectInstance('sys_cmts_administration');
        if(!$oGrid)
            return '';

        $oTemplate = BxDolTemplate::getInstance();
        $oTemplate->addJs(array('BxDolCmtsManageTools.js'));
        $oTemplate->addCss(array('cmts_manage_tools.css'));
        $oTemplate->addJsTranslation(array('_sys_grid_search'));
        return array(
            'content' =>  $oGrid->getCode()
        );
    }

    public function serviceGetMenuAddonManageTools()
    {
        $iNumTotal = BxDolCmts::getGlobalNumByParams();

        $iNum1 = BxDolCmts::getGlobalNumByParams([[
            'key' => 'status_admin', 
            'value' => 'pending', 
            'operator' => '='
        ]]);

        $iNum2 = BxDolCmts::getGlobalNumByParams([[
            'key' => 'reports',
            'value' => '0', 
            'operator' => '>'
        ]]);

        return [
            'counter1_value' => $iNum1, 
            'counter1_caption' => _t('_sys_menu_dashboard_manage_tools_addon_counter1_caption_profile_default'), 
            'counter2_value' => $iNum2, 
            'counter3_value' => $iNumTotal
        ];
    }

    /**
     * Comment Added for Timeline module
     */
    public function serviceGetTimelinePost($aEvent, $aBrowseParams = array())
    {
        $bCache = true;

        $iCmtUniqId = $aEvent['object_id'];
        $oCmts = BxDolCmts::getObjectInstanceByUniqId($iCmtUniqId);
        if(!$oCmts)
            return false;

        $aCmt = BxDolCmtsQuery::getCommentExtendedByUniqId($iCmtUniqId);
        if(empty($aCmt) || !is_array($aCmt))
            return false;

        $iCmtId = (int)$aCmt['cmt_id'];

        $iUserId = bx_get_logged_profile_id();
        $iAuthorId = (int)$aCmt['cmt_author_id'];
        $iAuthorIdAbs = abs($iAuthorId);
        if($iAuthorId < 0 && ((is_numeric($aEvent['owner_id']) && $iAuthorIdAbs == (int)$aEvent['owner_id']) || (is_array($aEvent['owner_id']) && in_array($iAuthorIdAbs, $aEvent['owner_id']))) && $iAuthorIdAbs != $iUserId)
            return false;

        $sCmtUrl = $oCmts->getViewUrl($iCmtId);

        $sSample = '_cmt_txt_sample_comment_single_with_article';
        $sSampleWoArticle = '_cmt_txt_sample_comment_single';
        $sAction = '';
        $sActionCustom = '_cmt_txt_added_sample_custom';
        $aActionCustomMarkers = array(
            'sample' => $sSample,
            'sample_url' => $sCmtUrl,
            'psample' => strip_tags($oCmts->getObjectTitle()),
            'psample_url' => $oCmts->getBaseUrl(),
        );

        $iOwnerId = $iAuthorIdAbs;
        if(isset($aEvent['object_privacy_view']) && (int)$aEvent['object_privacy_view'] < 0)
            $iOwnerId = abs($aEvent['object_privacy_view']);

        //--- Votes
        $aVotes = array();
        if(($oVotes = $oCmts->getVoteObject($iCmtUniqId)) !== false)
            $aVotes = array(
                'system' => $oVotes->getSystemName(),
                'object_id' => $iCmtUniqId,
                'count' => $aCmt['votes']
            );
        
        //--- Reactions
        $aReactions = array();
        if(($oReactions = $oCmts->getReactionObject($iCmtUniqId)) !== false)
            $aReactions = array(
                'system' => $oReactions->getSystemName(),
                'object_id' => $iCmtUniqId,
                'count' => $aCmt['rvotes']
            );

        //--- Scores
        $aScores = array();
        if(($oScores = $oCmts->getScoreObject($iCmtUniqId)) !== false)
            $aScores = array(
                'system' => $oScores->getSystemName(),
                'object_id' => $iCmtUniqId,
                'score' => $aCmt['score']
            );

        //--- Reports
        $aReports = array();
        if(($oReports = $oCmts->getReportObject($iCmtUniqId)) !== false)
            $aReports = array(
                'system' => $oReports->getSystemName(),
                'object_id' => $iCmtUniqId,
                'count' => $aCmt['reports']
            );

        return array(
            '_cache' => $bCache,
            'owner_id' => $iOwnerId,
            'object_owner_id' => $iAuthorId,
            'icon' => 'comments',
            'sample' => $sSample,
            'sample_wo_article' => $sSampleWoArticle,
            'sample_action' => $sAction,
            'sample_action_custom' => array(
                'content' => $sActionCustom,
                'markers' => $aActionCustomMarkers
            ),
            'url' => $sCmtUrl,
            'content' => array(
                'sample' => $sSample,
                'sample_wo_article' => $sSampleWoArticle,
                'sample_action' => $sAction,
                'url' => $sCmtUrl,
                'title' => '',
                'text' => $oCmts->getViewText($aCmt),
                'images' => array(),
                'images_attach' => array(),
                'videos' => array(),
                'videos_attach' => array(),
                'files' => array(),
                'files_attach' => $oCmts->getAttachments($iCmtId)
            ), //a string to display or array to parse default template before displaying.
            'date' => $aCmt['cmt_time'],
            'views' => array(),
            'votes' => $aVotes,
            'reactions' => $aReactions,
            'scores' => $aScores,
            'reports' => $aReports,
            'comments' => array(),
            'title' => !empty($aCmt['cmt_text']) ? $aCmt['cmt_text'] : '', //may be empty.
            'description' => '' //may be empty.
        );
    }

    /**
     * Comment added for Notifications module
     */
    public function serviceGetNotificationsCommentAdded($aEvent)
    {
        $aCommentGi = BxDolCmts::getGlobalInfo($aEvent['subobject_id']);
        if(empty($aCommentGi) || !is_array($aCommentGi))
            return [];

        $iContentId = (int)$aEvent['object_id'];

        $oComment = BxDolCmts::getObjectInstance($aCommentGi['system_name'], $iContentId);
        if(!$oComment)
            return [];

        $aCommentSystem = $oComment->getSystemInfo();
        
        $oContentModule = BxDolModule::getInstance($aCommentSystem['module']);
        if(!$oContentModule)
            return [];

        $aContentInfo = $oContentModule->_oDb->getContentInfoById($iContentId);
        if(empty($aContentInfo) || !is_array($aContentInfo))
            return [];

        $CNF = &$oContentModule->_oConfig->CNF;
        if(empty($CNF['FIELD_ID']) || empty($CNF['FIELD_AUTHOR']) || !(isset($CNF['FIELD_TITLE']) || isset($CNF['FIELD_TEXT'])) || empty($CNF['URI_VIEW_ENTRY']))
            return [];

        $sEntryUrl = '{bx_url_root}' . BxDolPermalinks::getInstance()->permalink('page.php?i=' . $CNF['URI_VIEW_ENTRY'] . '&id=' . $aContentInfo[$CNF['FIELD_ID']]);
        $sEntryCaption = isset($aContentInfo[$CNF['FIELD_TITLE']]) ? $aContentInfo[$CNF['FIELD_TITLE']] : strmaxtextlen($aContentInfo[$CNF['FIELD_TEXT']], 20, '...');

        return array(
            'entry_sample' => $CNF['T']['txt_sample_single'],
            'entry_url' => $sEntryUrl,
            'entry_caption' => $sEntryCaption,
            'subentry_sample' => $CNF['T']['txt_sample_comment_single'],
            'subentry_url' => '{bx_url_root}' . $oComment->getViewUrl((int)$aCommentGi['cmt_id'], false),
            'lang_key' => '', //may be empty or not specified. In this case the default one from Notification module will be used.
        );
    }

    /**
     * Comment vote for Notifications module
     */
    public function serviceGetNotificationsVote($aEvent)
    {
        $iCmtIdUnique = (int)$aEvent['object_id'];

        $aCmtInfo = BxDolCmts::getGlobalInfo($iCmtIdUnique);
        if(empty($aCmtInfo) || !is_array($aCmtInfo))
            return array();

        $oCmts = BxDolCmts::getObjectInstance($aCmtInfo['system_name'], 0, false);
        if(!$oCmts || !$oCmts->isEnabled())
            return array();

        $oVote = $oCmts->getVoteObject($iCmtIdUnique);
        if(!$oVote)
            return array();

        $iCmtId = (int)$aCmtInfo['cmt_id'];
        $sCmtUrl = str_replace(BX_DOL_URL_ROOT, '{bx_url_root}', $oCmts->serviceGetLink($iCmtId));
        $sCmtCaption = strmaxtextlen($oCmts->serviceGetText($iCmtId), 20, '...');

        return array(
            'entry_sample' => $oCmts->getLanguageKey('txt_sample_single'),
            'entry_url' => $sCmtUrl,
            'entry_caption' => $sCmtCaption,
            'entry_author' => $aCmtInfo['author_id'],
            'subentry_sample' => $oCmts->getLanguageKey('txt_sample_vote_single'),
            'lang_key' => '', //may be empty or not specified. In this case the default one from Notification module will be used.
        );
    }

    /**
     * Comment reaction for Notifications module
     */
    public function serviceGetNotificationsReaction($aEvent)
    {
    	$iCmtIdUnique = (int)$aEvent['object_id'];

        $aCmtInfo = BxDolCmts::getGlobalInfo($iCmtIdUnique);
        if(empty($aCmtInfo) || !is_array($aCmtInfo))
            return array();

        $oCmts = BxDolCmts::getObjectInstance($aCmtInfo['system_name'], 0, false);
        if(!$oCmts || !$oCmts->isEnabled())
            return array();

        $oReaction = $oCmts->getReactionObject($iCmtIdUnique);
        if(!$oReaction)
            return array();
        
        $aSubentry = $oReaction->getTrackBy(array('type' => 'id', 'id' => (int)$aEvent['subobject_id']));
        if(empty($aSubentry) || !is_array($aSubentry))
            return array();

        $aSubentrySampleParams = array();
        $aReaction = $oReaction->getReaction($aSubentry['reaction']);
        if(!empty($aReaction['title']))
            $aSubentrySampleParams[] = $aReaction['title'];
        else
            $aSubentrySampleParams[] = '_undefined';

        $iCmtId = (int)$aCmtInfo['cmt_id'];
        $sCmtUrl = str_replace(BX_DOL_URL_ROOT, '{bx_url_root}', $oCmts->serviceGetLink($iCmtId));
        $sCmtCaption = strmaxtextlen($oCmts->serviceGetText($iCmtId), 20, '...');

        return array(
            'entry_sample' => $oCmts->getLanguageKey('txt_sample_single'),
            'entry_url' => $sCmtUrl,
            'entry_caption' => $sCmtCaption,
            'entry_author' => $aCmtInfo['author_id'],
            'subentry_sample' => $oCmts->getLanguageKey('txt_sample_reaction_single'),
            'subentry_sample_params' => $aSubentrySampleParams,
            'lang_key' => '', //may be empty or not specified. In this case the default one from Notification module will be used.
        );
    }

    /**
     * Comment score -> vote up for Notifications module
     */
    public function serviceGetNotificationsScoreUp($aEvent)
    {
    	return $this->_serviceGetNotificationsScore('up', $aEvent);
    }

    /**
     * Comment score -> vote down for Notifications module
     */
    public function serviceGetNotificationsScoreDown($aEvent)
    {
    	return $this->_serviceGetNotificationsScore('down', $aEvent);
    }

    protected function _serviceGetNotificationsScore($sType, $aEvent)
    {
        $iCmtIdUnique = (int)$aEvent['object_id'];

        $aCmtInfo = BxDolCmts::getGlobalInfo($iCmtIdUnique);
        if(empty($aCmtInfo) || !is_array($aCmtInfo))
            return array();

        $oCmts = BxDolCmts::getObjectInstance($aCmtInfo['system_name'], 0, false);
        if(!$oCmts || !$oCmts->isEnabled())
            return array();

        $oScore = $oCmts->getScoreObject($iCmtIdUnique);
        if(!$oScore)
            return array();

        $iCmtId = (int)$aCmtInfo['cmt_id'];
        $sCmtUrl = str_replace(BX_DOL_URL_ROOT, '{bx_url_root}', $oCmts->serviceGetLink($iCmtId));
        $sCmtCaption = strmaxtextlen($oCmts->serviceGetText($iCmtId), 20, '...');

        return array(
            'entry_sample' => $oCmts->getLanguageKey('txt_sample_single'),
            'entry_url' => $sCmtUrl,
            'entry_caption' => $sCmtCaption,
            'entry_author' => $aCmtInfo['author_id'],
            'subentry_sample' => $oCmts->getLanguageKey('txt_sample_score_' . $sType . '_single'),
            'lang_key' => '', //may be empty or not specified. In this case the default one from Notification module will be used.
        );
    }
    
    /**
     * @page service Service Calls
     * @section bx_system_cmts System Services 
     * @subsection bx_system_cmts-general General
     * @subsubsection bx_system_cmts-get_data_api get_data_api
     * 
     * @code bx_srv('system', 'get_data_api', [], 'TemplCmtsServices'); @endcode
     * 
     * Get comments data
     * @param $aParams array with paramenters :
     *         "module":"bx_posts","object_id":3,"start_from":5,"order_way":"desc","is_form":false
     * 
     * @see TemplCmtsServices::serviceGetDataApi
     */
    /** 
     * @ref bx_system_cmts-get_data_api "get_data_api"
     * @api @ref bx_system_cmts-get_data_api "get_data_api"
     */
    
    public function serviceGetDataApi($aParams)
    {
        if(is_string($aParams))
            $aParams = json_decode($aParams, true);
        
        if (isset($aParams['action']) && isset($aParams['id']) && $aParams['action'] == 'remove'){
            $iCmtId = $aParams['id'];
            $oCmts = BxDolCmts::getObjectInstance($aParams['module'], $aParams['object_id']);
            $oCmts->remove($iCmtId);
            return 'Ok';
        }
        
        if (isset($aParams['action']) && isset($aParams['id']) && $aParams['action'] == 'edit'){
            $iCmtId = $aParams['id'];
            $oCmts = BxDolCmts::getObjectInstance($aParams['module'], $aParams['object_id']);
            $oForm = $oCmts->getFormEdit($iCmtId);
            if($oForm['form']->isSubmittedAndValid()){
                $aParams['comment_id'] = $oForm['res'];  
                $aRv['browse'] = $this->serviceGetCommentsApi($oCmts, $aParams);
                return $aRv;
            }
            $aForm = $oForm['form']->getCodeAPI();
            $aForm['inputs']['cmt_text']['numLines'] = 1;
            $aForm['inputs']['cmt_text']['autoheight'] = true;
            $aForm['inputs']['cmt_submit'] = $aForm['inputs']['cmt_controls'][0];
            unset($aForm['inputs']['cmt_controls']);
            
            $aForm['inputs']['cmt_submit']['icon'] = 'contact';
            $aForm['inputs']['cmt_submit']['variant'] = 'text';
            $aForm['inputs']['cmt_submit']['icon_only'] = true;
            $aRv['form'] = ['id' => 'cmt_form', 'type' => 'form', 'name' => 'comment', 'data' => $aForm, 'request' => ['immutable' => true]];
            return $aRv;
        }
        
        
        $aParams['parent_id'] = !isset($aParams['parent_id']) ? 0 : $aParams['parent_id'];
        $aParams['start_from'] = !isset($aParams['start_from']) ? 0 : $aParams['start_from'];
        $aParams['order_way'] = !isset($aParams['order_way']) ? 'asc' : $aParams['order_way'];
        $aParams['is_form'] = !isset($aParams['is_form']) ? true : $aParams['is_form'];
        $aParams['insert'] = 'before';
        
        $oCmts = BxDolCmts::getObjectInstance($aParams['module'], $aParams['object_id']);
        
        if (!$oCmts || !$oCmts->isEnabled())
            return false;
        
        $aRv = [
            'id' => '1',
            'url' => '/api.php?r=system/get_data_api/TemplCmtsServices/&params[]=',
            'type' => 'comments'
        ];
        
        $oForm = $oCmts->getFormPost($aParams['parent_id']);
        $bIsList =  false;
        if (isset($oForm['form']) && $aParams['is_form']){
            $aForm = $oForm['form']->getCodeAPI();
            $aForm['inputs']['cmt_text']['numLines'] = 1;
            $aForm['inputs']['cmt_text']['autoheight'] = true;
            $aForm['inputs']['cmt_submit']['icon'] = 'contact';
            $aForm['inputs']['cmt_submit']['variant'] = 'text';
            $aForm['inputs']['cmt_submit']['icon_only'] = true;
            
            // add view (form + new comment)
            if($oForm['form']->isSubmittedAndValid()){
                $sInsert = $aParams['order_way'] == 'desc' ? 'after' : 'before';
                if (bx_get('cmt_parent_id') > 0)
                     $sInsert = 'after';
                $aParams['insert'] = $sInsert;
                $aParams['comment_id'] = $oForm['res'];
                $bIsList = true;
                $aForm['inputs']['cmt_parent_id']['value'] = 0;
            }
            
            $aRv['form'] = ['id' => 'cmt_form', 'type' => 'form', 'name' => 'comment', 'data' => $aForm, 'request' => ['immutable' => true]];

            // default view (form + list)
            if (!$oForm['form']->isSubmitted()){
                $bIsList = true;
            }
        }
        else{
            // list only view
            $bIsList = true;                  
            $aParams['insert'] = 'before';
        }
        if ($bIsList)
            $aRv['browse'] = $this->serviceGetCommentsApi($oCmts, $aParams);

        return $aRv;
    }
    
    public function serviceGetCommentsApi($oCmts, $aParams)
    {
        $mixedResult = $oCmts->isViewAllowed();
        if($mixedResult !== CHECK_ACTION_RESULT_ALLOWED)
            return $mixedResult; // TODO: error checking

        if (isset($aParams['comment_id']))
            $aParams['comment_id'] = explode(',', $aParams['comment_id']);
       
        $aBp = !isset($aParams['aBp']) ? [] : $aParams['aBp'];
        $aDp = ['in_designbox' => false, 'show_empty' => false];
        
        $aDp['type'] = !getParam('sys_api_comments_flat') ? 'threaded' : 'flat';
        $aBp['type'] = 'head';
        $oCmts->getParams($aBp, $aDp);
        $oCmts->prepareParams($aBp, $aDp);
        
        $aBp['order']['way'] = $aParams['order_way'];
        $aBp['order_way'] = $aParams['order_way'];
        $aBp['start'] = $aParams['start_from'] ; 
        $aPp = $aBp['per_view'];
        $aBp['per_view'] =  $aBp['per_view'] + 1; 
        if (isset($aParams['per_view'])){
             $aBp['per_view'] = $aParams['per_view'];
        }

        $aCmts = isset($aParams['comment_id']) ? array_map(function($value) {return ['cmt_id' => $value];}, $aParams['comment_id']) : $oCmts->getCommentsArray($aBp['vparent_id'], $aBp['filter'], $aBp['order'], $aBp['start'], $aBp[($aBp['init_view'] != -1 ? 'init' : 'per') . '_view']);

        
        $aParams['start_from'] = 0;
        if (count($aCmts) == $aBp['per_view']){
            $aBp['per_view'] = $aPp;
            $aCmts = array_slice($aCmts, 0, $aBp['per_view']); 
            $aParams['start_from'] = $aBp['start'] + $aBp['per_view'];
        }
        $aCmtsRv = [];
        foreach ($aCmts as $aCmt) {
            $oCmt = $oCmts->getCommentStructure($aCmt['cmt_id'], $aBp, $aDp);
            if($oCmt === false)
                continue;

            if(($sKey = array_shift(array_keys($oCmt))) && $oCmt[$sKey]['data']['cmt_parent_id'] > 0){
                $aParent = $oCmts->getCommentSimple((int)$oCmt[$sKey]['data']['cmt_parent_id']);
                $oCmt[$sKey]['data']['cmt_parent'] = [
                    'data' => $aParent,
                    'author_data' => BxDolProfile::getData($aParent['cmt_author_id'])
                ];
            }

            $aCmtsRv[] = $oCmt;
        }
        
        $aData = [
            'unit' => 'comments',
            'start' => $aParams['start_from'],
            'count' => count($aCmts),
            'per_view' => $aBp['per_view'],
            'total_count' => $oCmts->getCommentsCount(),
            'order' => $aParams['order_way'],
            'view' => $aDp['type'],
            'module' => $oCmts->getSystemName(), 
            'object_id' => $oCmts->getId(),
            'max_level' => $oCmts->getSystemInfo()['number_of_levels'],
            'data' => $aCmtsRv,
        ];
        if (isset($aParams['mode']) &&  $aParams['mode'] == 'feed')
            return $aData;
        
        $aRv = [
            'id' => 'cmt_list', 
            'type' => 'browse', 
            'insert' => $aParams['insert'], 
            'data' => $aData
        ];
        
        if (isset($aParams['comment_id'])){
            $aRv['new'] = $aParams['comment_id'];
        }
       
        return $aRv;
    }
}

/** @} */
