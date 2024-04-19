<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');

/**
 * Page representation.
 * @see BxDolPage
 */
class BxBasePage extends BxDolPage
{
    protected $_oTemplate;

    protected $_sStorage; //--- Storage object for page's images like custom cover, HTML block attachments, etc.
    protected $_oPageCacheObject = null;
    protected $_oBlockCacheObject = null;
    
    protected $_sJsClassName = '';
    protected $_sJsObjectName = '';
    protected $_aHtmlIds = [];

    protected $_bStickyColumns = false;

    protected $_bSubPage = false;

    public function __construct ($aObject, $oTemplate)
    {
        parent::__construct ($aObject);

        if ($oTemplate)
            $this->_oTemplate = $oTemplate;
        else
            $this->_oTemplate = BxDolTemplate::getInstance();

        $this->_sStorage = 'sys_images';
        
        $this->_sJsClassName = 'BxDolPage';
        $this->_sJsObjectName = 'oBxDolPage';
        
        $this->_bStickyColumns = isset($this->_aObject['sticky_columns']) && $this->_aObject['sticky_columns'] == 1;

        $sHtmlId = str_replace(array('_' , ' '), array('-', '-'), $this->_sObject);
        $this->_aHtmlIds = array(
            'help_popup' => $sHtmlId . '-help-popup-',
        );
        $this->addMarkers(array('site_title' => getParam('site_title')));
    }

    public function performActionGetHelp ()
    {
        $iBlockId = (int)bx_get('block_id');
        if(empty($iBlockId))
            return;

        $aBlock = $this->_oQuery->getPageBlock($iBlockId);
        if(empty($aBlock) || !is_array($aBlock) || empty($aBlock['help']))
            return;

        echo $this->_oTemplate->parseHtmlByName('designbox_help_popup.html', array(
            'content' => _t($aBlock['help'])
        ));
    }
    
    public function performActionGetShare ()
    {
        $aEmbedData = BxDolPage::getEmbedData(bx_get('url'));
        if(!isset($aEmbedData['url'])) 
            return; 

        $sMenu = '';
        if(getParam('sys_a2a_enable') != 'on') {
            if(($oMenu = BxDolMenu::getObjectInstance('sys_social_sharing')) !== false) {
                $oMenu->addMarkers([
                    'url' => $aEmbedData['url'],
                    'img_url_encoded' => '',
                    'title_encoded' => ''
                ]);
                $sMenu = $oMenu->getCode();
            }
        }
        else
            $sMenu = getParam('sys_a2a_code');

        echo PopupBox('sys_share_popup', _t('_sys_txt_share_popup_header'), $this->_oTemplate->parseHtmlByName('designbox_share_popup.html', [
            'url' => bx_process_output($aEmbedData['url']),
            'menu' => $sMenu,
            'code' => htmlspecialchars($aEmbedData['html']),
        ]), true);
    }
    
    public function performActionEmbed ()
    {
        $aCover = $this->getPageCoverImage();
        $sTitle = $this->_getPageTitle();
        $this->_oTemplate->getEmbed($this->_oTemplate->parseHtmlByName('embed_card.html', [
            'title' => $sTitle,
            'url' => BxDolPermalinks::getInstance()->permalink($this->_aObject['url']),
            'bx_if:thumb' => [
                'condition' => $this->isPageCover() && count($aCover) > 0,
                'content' => [
                    'title' => $sTitle,
                    'url' => BxDolPermalinks::getInstance()->permalink($this->_aObject['url']),
                    'background' => BxDolCover::getInstance(BxDolTemplate::getInstance())->getCoverImageUrl($aCover)
                ],
            ],
            'bx_if:no_thumb' => [
                'condition' => count($aCover) == 0 || !$this->isPageCover(),
                'content' => [
                    'title' => $sTitle,
                    'url' => BxDolPermalinks::getInstance()->permalink($this->_aObject['url']),
                ]
            ]
        ]
        ));
    }

    public function performActionCreativeSave()
    {
        if(!$this->isAllowedCreativeManage())
            return echoJson(['code' => 1]);

        $iId = 0;
        $sContent = '';
        if(($iId = bx_get('b')) === false || ($sContent = bx_get('c')) === false)
            return echoJson(['code' => 2]);

        $iId = bx_process_input($iId, BX_DATA_INT);
        $sContent = bx_process_input($sContent, BX_DATA_HTML);
        if(!$this->_oQuery->setPageBlockContent($iId, $sContent))
            return echoJson(['code' => 3]);

        return echoJson(['code' => 0]);
    }

    public function isAllowedCreativeManage()
    {
        return isAdmin();
    }

    /**
     * Very similar to BxBasePage::getCode
     * but adds css and js files which are needed for the corect page display
     */ 
    public function getCodeDynamic ()
    {
        // TODO: remake to use use collect* template methods

        $oTemplate = BxDolTemplate::getInstance();

        // get js&css before the page code is generated
        $aCssBefore = $oTemplate->getCss();
        $aJsBefore = $oTemplate->getJs();

        // generate page code
        $sContent = $this->getCode();

        // get js&css after the page code is generated
        $aCssAfter = $oTemplate->getCss();
        $aJsAfter = $oTemplate->getJs();

        // compare files which were added before and after page code is generated
        $f = function ($a1, $a2) {
            return strcasecmp($a1['url'], $a2['url']);
        };
        $aCssNew = array_udiff($aCssAfter, $aCssBefore, $f);
        $aJsNew = array_udiff($aJsAfter, $aJsBefore, $f);

        // add newly added js&css files in static mode
        $sCss = $sJs = '';
        foreach ($aCssNew as $a)
            $sCss .= $oTemplate->addCss($a['url'], true);
        foreach ($aJsNew as $a)
            $sJs .= $oTemplate->addJs($a['url'], true);

        return $sJs . $sCss . $sContent;
    }

    public function getIncludes ()
    {
        $oTemplate = BxDolTemplate::getInstance();

        // generate page code
        $this->getCode();

        // get js&css after the page code is generated
        $aCss = $oTemplate->getCss();
        $aJs = $oTemplate->getJs();

        // add js&css files in static mode
        $sResult = '';
        foreach ($aCss as $a)
            $sResult .= $oTemplate->addCss($a['url'], true);
        foreach ($aJs as $a)
            $sResult .= $oTemplate->addJs($a['url'], true);

        return $sResult;
    }
    
    /**
     * Get page code with automatic caching, adding necessary css/js files and system template vars.
     * @return string.
     */
    public function getCode ()
    {
        if (bx_get('dynamic') && ($iBlockId = (int)bx_get('pageBlock'))) {

            if (self::isLockedFromUnauthenticated($this->_aObject['uri']) || !$this->_isVisiblePage($this->_aObject)) {
                header('HTTP/1.0 403 Forbidden');
                exit;
            }

            bx_alert('system', 'page_output_block', 0, false, array(
                'page_name' => $this->_sObject,
                'page_object' => $this,
                'page_query' => $this->_oQuery,
                'block_id' => (int)$iBlockId,
            ));

            header( 'Content-type:text/html;charset=utf-8' );

            BxDolTemplate::getInstance()->collectingStart();

            $s = $this->_getBlockOnlyCode($iBlockId);

            $sCss = bx_get('includedCss');
            $sJs = bx_get('includedJs');
            echo BxDolTemplate::getInstance()->collectingEndGetCode($sCss ? @json_decode($sCss) : [], $sJs ? @json_decode($sJs) : []);

            echo $s;
            exit;
        }

        if (($mixedAvailable = $this->_isAvailablePage($this->_aObject)) !== true) 
            return $this->_getPageNotFoundMsg($mixedAvailable);

        if (($mixedVisible = $this->_isVisiblePage($this->_aObject)) !== true)
            return $this->_getPageAccessDeniedMsg($mixedVisible);

        $this->_addJsCss();

        if (!$this->_bSubPage) {
            $this->_addSysTemplateVars();

            $this->_selectMenu();

            $this->_setSubmenu(array());
        }

        if (bx_is_dbg() || !getParam('sys_page_cache_enable') || !$this->_aObject['cache_lifetime'] || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')) {
            $sPageCode = $this->_getPageCode();
        }
        else {
            $oCache = $this->_getPageCacheObject();
            $sKey = $this->_getPageCacheKey();
            $sKeyCssJs = 'cssjs_' . $sKey;

            $mixedRet = $oCache->getData($sKey, $this->_aObject['cache_lifetime']);
            $aRetCssJs = $oCache->getData($sKeyCssJs, $this->_aObject['cache_lifetime']);

            if ($mixedRet !== null && $aRetCssJs !== null) {
                BxDolTemplate::getInstance()->collectingStart();                
                BxDolTemplate::getInstance()->collectingInject($aRetCssJs['css'], $aRetCssJs['js']);
                $sPageCode = BxDolTemplate::getInstance()->collectingEndGetCode();
                $sPageCode .= $mixedRet;
            } else {

                BxDolTemplate::getInstance()->collectingStart();

                $sPageCode = $this->_getPageCode();

                $aPageCssJs = BxDolTemplate::getInstance()->collectingEndGetCode(array(), array(), 'array');

                $oCache->setData($sKey, $sPageCode, $this->_aObject['cache_lifetime']);
                $oCache->setData($sKeyCssJs, $aPageCssJs, $this->_aObject['cache_lifetime']);
            }
        }
        
        bx_alert('system', 'page_output', 0, false, array(
            'page_name' => $this->_sObject,
            'page_object' => $this,
            'page_query' => $this->_oQuery,
            'page_code' => &$sPageCode,
            'sub_page' => $this->_bSubPage,
        ));

        if (!$this->_bSubPage)
            $sPageCode .= $this->getJsScript();
        
        return $sPageCode;
    }
    
    public function getJsClassName()
    {
        return $this->_sJsClassName;
    }

    public function getJsObjectName()
    {
        return $this->_sJsObjectName;
    }
    
    function _wrapInTagJsCode($sCode)
    {
        return "<script language=\"javascript\">\n<!--\n" . $sCode . "\n-->\n</script>";
    }

    public function getJsScript()
    {
        $sJsObjName = $this->getJsObjectName();
        $sJsObjClass = $this->getJsClassName();
        $sCode = "if(window['" . $sJsObjName . "'] == undefined) var " . $sJsObjName . " = new " . $sJsObjClass . "(" . json_encode(array(
            'sObjName' => $sJsObjName,
            'sObject' => $this->_sObject,
            'sRootUrl' => BX_DOL_URL_ROOT,
            'aHtmlIds' => $this->_aHtmlIds,
            'isStickyColumns' => $this->_bStickyColumns
        )) . ");";

        return $this->_wrapInTagJsCode($sCode);
    }

    /**
     * Is page cover enabled.
     * @return string
     */
    public function isPageCover()
    {
        $bResult = false;
        switch((int)$this->_aObject['cover']) {
            case 1: //--- Enabled for all
                $bResult = true;
                break;

            case 2: //--- Enabled for visitors only
                $bResult = !isLogged();
                break;

            case 3: //--- Enabled for members only
                $bResult = isLogged();
                break;
        }

    	return $bResult;
    }

    public function setSubPage($b = true)
    {
        $this->_bSubPage = $b;
    }

    public function setPageCover($bCover = true)
    {
        $this->_aObject['cover'] = (bool)$bCover;
    }

	public function getPageCoverImage($bTranscoder = true)
    {
    	$iId = (int)$this->_aObject['cover_image'];
    	if(empty($iId)) {
    		$iId = (int)getParam('sys_site_cover_common');
    		if(empty($iId))
    			return array();
    	}

    	$aResult = array(
    		'id' => $iId
    	);
    	if($bTranscoder)
    		$aResult['transcoder'] = BX_DOL_TRANSCODER_OBJ_COVER;
    	else 
    		$aResult['object'] = $this->_sStorage;

    	return $aResult;
    }

    public function getPageCoverParams()
    {
        $aParams = [
            'title' => $this->_getPageTitle(),
            'actions' => '',
            'bx_if:image' => [
                'condition' => false,
                'content' => []
            ],
            'bx_if:icon' => [
                'condition' => false,
                'content' => []
            ],
        ];

        if(($oMenuSubmenu = BxDolMenu::getObjectInstance('sys_site_submenu')) !== false) {
            $aCoverParams = $oMenuSubmenu->getPageCoverParams();
            if(!empty($aCoverParams) && is_array($aCoverParams))
                $aParams = $aCoverParams;
        }

        if(!empty($this->_aObject['cover_title'])) {
            $sCoverTitle = _t($this->_aObject['cover_title']);
            if($sCoverTitle && strcmp($sCoverTitle, $this->_aObject['cover_title']) != 0)
                $aParams['title'] = $sCoverTitle;
        }

    	return $aParams;
    }

    /**
     * Get block title.
     * @return string
     */
    public function getBlockTitle ($aBlock)
    {
        return $this->_replaceMarkers(_t($aBlock['title']), array('block_id' => $aBlock['id']));
    }

    /**
     * Get help control if help is available for the block.
     * @return string
     */
    public function getBlockHelp ($aBlock)
    {
        if(empty($aBlock['help']) || empty(strip_tags(_t($aBlock['help']))))
            return '';

        return $this->_oTemplate->parseHtmlByName('designbox_help.html', array(
            'js_object' => $this->_sJsObjectName,
            'block_id' => $aBlock['id']
        ));
    }

    /**
     * Get code to load block asyncroniously
     * @param $aBlock block code
     * @param $iAsync if greater than 0 the it defines loading indicator for the block
     * @return HTML code string
     */
    public function getBlockAsyncCode($aBlock, $iAsync)
    {
        $aContentPlaceholder = $this->_oQuery->getPageBlockContentPlaceholder($iAsync);
        if (!$aContentPlaceholder)
            return _t('_sys_txt_error_occured');
        $oTemplate = $this->_oTemplate;
        if ('system' != $aContentPlaceholder['module']) {
            $oModule = BxDolModule::getInstance($aContentPlaceholder['module']);
            if (!$oModule)
                return _t('_sys_txt_error_occured');
            $oTemplate = $oModule->_oTemplate;
        }
        return $oTemplate->parseHtmlByName($aContentPlaceholder['template'], $aBlock);
    }

    /**
     * Get page array with all cells and blocks
     */
    public function getPageAPI ($aBlocks = [])
    {
        define('BX_API_PAGE', true);
        $query_string  = '';

        if (isset(bx_get('params')[2]) && bx_get('params')[2] != ''){
            $array = json_decode(bx_get('params')[2], true);
            $query_string = http_build_query($array);
            parse_str($query_string, $a);
            $_GET = array_merge($_GET, $a);
        }

        $bIsAvailable = $this->_isAvailablePage($this->_aObject);
        $bIsVisible = $this->_isVisiblePage($this->_aObject);

        $sMetaTitle = $this->_getPageMetaTitle();
        $sName = $this->_getPageTitle();
        $a = [
            'id' => $this->_aObject['id'],
            'title' => $sMetaTitle ? $sMetaTitle : $sName,
            'name' => $sName,
            'uri' => $this->_aObject['uri'],
            'url' =>  ((bx_get('params')[0] ? bx_get('params')[0] : $this->_aObject['uri'] ) . ($query_string != '' ? '?' . $query_string : '')),
            'author' => $this->_aObject['author'],
            'added' => $this->_aObject['added'],
            'module' => $this->getModule(),
            'type' => $this->getType (),
            'layout' => str_replace('.html', '', $this->_aObject['template']),
            'cover_block' => '',
            'menu_top' => '',
            'menu' => '',
            'menu_bottom' => '',
            'menu_add' => '',
            'elements' => $this->getPageBlocksAPI($aBlocks),
        ];

        $sMenuTop = getParam('sys_api_menu_top');
        if (!empty($sMenuTop) && ($oMenuTop = BxDolMenu::getObjectInstance($sMenuTop)) !== false)
            $a['menu_top'] = $oMenuTop->getCodeAPI();

        if (($oMenuSubmenu = BxDolMenu::getObjectInstance('sys_site_submenu')) !== false) {
            if(($sSubmenu = $this->getSubMenu()) !== '')
                $oMenuSubmenu->setObjectSubmenu($sSubmenu, []);

            $a['menu'] = $oMenuSubmenu->getCodeAPI();
        }

        /**
         * Profile/Context view page.
         */
        if(isset($this->_aProfileInfo)) {
            $oProfileModule = BxDolModule::getInstance($this->getModule());
            if($oProfileModule !== null)
                $aProfileContentInfo = $oProfileModule->_oDb->getContentInfoById($this->_aProfileInfo['content_id']);

            if(!empty($aProfileContentInfo) && is_array($aProfileContentInfo)) {
                $CNF = &$oProfileModule->_oConfig->CNF;

                /**
                 * Process page cover with related menus and lists.
                 */
                if(BxDolCover::getInstance($this)->isCover()) {
                    $a['cover_block'] = [
                        'profile' => BxDolProfile::getData($this->_aProfileInfo['id'], [
                            'get_avatar' => 'getAvatarBig',
                            'with_info' => true
                        ]),
                        'actions_menu' => '',
                        'meta_menu' => '',
                        'badges' => BxDolProfile::getInstance($this->_aProfileInfo['id'])->getBadges(),
                        'cover' => $oProfileModule->serviceGetCover($this->_aProfileInfo['content_id']),
                        'allow_edit' =>  $oProfileModule->checkAllowedChangeCover($aProfileContentInfo) === CHECK_ACTION_RESULT_ALLOWED
                    ];

                    if(!empty($CNF['OBJECT_MENU_VIEW_ENTRY_META']) && ($oMetaMenu = BxTemplMenu::getObjectInstance($CNF['OBJECT_MENU_VIEW_ENTRY_META'])) !== false)
                        $a['cover_block']['meta_menu'] =  $oMetaMenu->getCodeAPI();

                    if(!empty($CNF['OBJECT_CONNECTIONS']) && ($oConnection = BxDolConnection::getObjectInstance($CNF['OBJECT_CONNECTIONS'])) !== false)
                        $a['cover_block']['members_list'] = $oConnection->getConnectedListAPI($this->_aProfileInfo['id'], false, BX_CONNECTIONS_CONTENT_TYPE_INITIATORS, 10);

                    if(!empty($CNF['OBJECT_MENU_ACTIONS_VIEW_ENTRY']) && ($oActionMenu = BxTemplMenu::getObjectInstance($CNF['OBJECT_MENU_ACTIONS_VIEW_ENTRY_ALL'])) !== false)
                        $a['cover_block']['actions_menu'] = $oActionMenu->getCodeAPI();
                }
                
                /**
                 * Process page mange menu.
                 */
                if(($oMenuManage = BxDolMenu::getObjectInstance('sys_site_manage')) !== false) {
                    if(!empty($CNF['FIELD_ID']))
                        $oMenuManage->setContentId($aProfileContentInfo[$CNF['FIELD_ID']]);

                    $aObjectManage = [];
                    if(!empty($CNF['OBJECT_MENU_MANAGE_VIEW_ENTRY']))
                        $aObjectManage = [$CNF['OBJECT_MENU_MANAGE_VIEW_ENTRY']];
                    else if(!empty($CNF['OBJECT_MENU_ACTIONS_VIEW_ENTRY_MORE']))
                        $aObjectManage = [$CNF['OBJECT_MENU_ACTIONS_VIEW_ENTRY_MORE'], true];

                    call_user_func_array([$oMenuManage, 'setObjectManage'], $aObjectManage);

                    $aMenuManage = $oMenuManage->getCodeAPI();
                    if(!empty($aMenuManage) && is_array($aMenuManage)) {
                        if(!empty($a['menu']) && is_array($a['menu']))
                            $a['menu']['items'] = array_merge($a['menu']['items'], $aMenuManage['items']);
                        else
                            $a['menu'] = $aMenuManage;
                    }
                }
            }
        }

        if (isLogged()) {
            /* temporatery commented
                if(($oMenuAddContent = BxDolMenu::getObjectInstance('sys_add_content')) !== false)
                $a['menu_add'] = $oMenuAddContent->getCodeAPI();
            */

            $o = BxDolProfile::getInstance();
            $a['user'] = BxDolProfile::getDataForPage($o);

            $oInformer = BxDolInformer::getInstance(BxDolTemplate::getInstance());
            $sRet = $oInformer ? $oInformer->display() : '';
            if ($sRet){
                $a['user']['informer'] = $sRet;
            }
            
            $sModuleNotifications = 'bx_notifications';
            if(BxDolRequest::serviceExists($sModuleNotifications, 'get_unread_notifications_num'))
                $a['user']['notifications'] = bx_srv($sModuleNotifications, 'get_unread_notifications_num', [$o->id()]);

            $oPayments = BxDolPayments::getInstance();
            if($oPayments->isActive())
                $a['user']['cart'] = $oPayments->getCartItemsCount();
        }
        
        $aExtras = [
            'page' => $this,
            'blocks' => $aBlocks,
            'data' => &$a,
        ];

        if(!$bIsAvailable)
            $a['page_status'] = 404;
        else if(!$bIsVisible)
            $a['page_status'] = 403;

        bx_alert('system', 'get_page_api', 0, 0, $aExtras);

        return $a;
    }

    public function getPageBlocksAPI($aBlocks = [])
    {
        $bBlocks = !empty($aBlocks) && is_array($aBlocks);
        $aFieldsUnset = ['object', 'cell_id', 'title_system', 'class', 'submenu', 'tabs', 'async', 'visible_for_levels', 'hidden_on', 'type', 'text', 'text_updated', 'help', 'cache_lifetime', 'active', 'active_api', 'copyable', 'deletable', 'order'];

        $aCells = $this->_oQuery->getPageBlocks(true);
        foreach($aCells as $sKey => &$aCell) {
            foreach($aCell as $i => $aBlock) {     
                if(!$this->_isVisibleBlock($aBlock)) {
                    unset($aCells[$sKey][$i]);
                    continue;
                }

                $this->processPageBlock($aCells[$sKey][$i], true);
                $aBlock = $aCells[$sKey][$i];

                $sSource = '';
                if($aBlock['type'] == 'service') {
                    $aContent = @unserialize($aBlock['content']);
                    if(isset($aContent['module'], $aContent['method']))
                        $sSource = $aContent['module'] . ':' . $aContent['method'];
                }
                else{
                    $sSource = 'system:block_' . $aBlock['id'];
                }

                if($bBlocks && !in_array($sSource, $aBlocks)) {
                    unset($aCells[$sKey][$i]);
                    continue;
                }

                $sFunc = '_getBlock' . ucfirst($aBlock['type']);
                $mBlock = method_exists($this, $sFunc) ? $this->$sFunc($aBlock) : $aBlock['content'];

                $aCells[$sKey][$i] = array_merge($aCells[$sKey][$i], [
                    'title' => isset($mBlock['title']) ? $mBlock['title'] : $this->getBlockTitle($aBlock),
                    'content' => isset($mBlock['content']) ? $mBlock['content'] : $mBlock,
                    'menu' => isset($mBlock['menu']) ? $mBlock['menu'] : '',
                    'source' => $sSource
                ]);
                $aCells[$sKey][$i] = array_diff_key($aCells[$sKey][$i], array_flip($aFieldsUnset));
            }
        }

        return array_map('array_values', $aCells);
    }

    /**
     * Get page code vars
     * @return string
     */
    protected function _getPageCodeVars ()
    {
    	$aHiddenOn = array(
            pow(2, BX_DB_HIDDEN_PHONE - 1) => 'bx-def-media-phone-hide',
            pow(2, BX_DB_HIDDEN_TABLET - 1) => 'bx-def-media-tablet-hide',
            pow(2, BX_DB_HIDDEN_DESKTOP - 1) => 'bx-def-media-desktop-hide',
            pow(2, BX_DB_HIDDEN_MOBILE - 1) => 'bx-def-mobile-app-hide'
        );

        $aVars = array (
            'page_id' => 'bx-page-' . $this->_aObject['uri'],
            'bx_if:show_layout_row_dump' => array(
                'condition' => false,
                'content' => array()
            )
        );
        $aBlocks = $this->_oQuery->getPageBlocks();
        foreach ($aBlocks as $sKey => $aCell) {
            $sCell = '';
            foreach ($aCell as $aBlock) {
                $this->processPageBlock($aBlock, false);

                $sContentWithBox = $this->_getBlockCodeWithCache($aBlock, $aBlock['async']);

            	$sClassAdd = '';
                if(!empty($aBlock['class']))
                    $sClassAdd .= ' ' . $aBlock['class'];

                if(!empty($aBlock['hidden_on']))
                    foreach($aHiddenOn as $iHiddenOn => $sHiddenOnClass)
                        if((int)$aBlock['hidden_on'] & $iHiddenOn)
                            $sClassAdd .= ' ' . $sHiddenOnClass;

                if ($sContentWithBox)
                    $sCell .= $this->_oTemplate->parseHtmlByName('designbox_container.html', array(
                        'class_add' => $sClassAdd,
                        'bx_if:show_html_id' => array(
                            'condition' => true,
                            'content' => array(
                                'html_id' => 'bx-page-block-' . $aBlock['id']
                            ),
                        ),
                        'content' => $sContentWithBox
                    ));
            }
            $aVars[$sKey] = $sCell;
        }

        return $aVars;
    }

    /**
     * Process block values, especially if someting need to be overrided 
     */
    protected function processPageBlock(&$aBlock, $bApi = false) 
    {

    }

    /**
     * Get page code only.
     * @return string
     */
    protected function _getPageCode ()
    {
        $aVars = $this->_getPageCodeVars ();
        return $this->_oTemplate->parseHtmlByName($this->_aObject['template'], $aVars);
    }

    /**
     * Get one block code only.
     * @return string
     */
    protected function _getBlockOnlyCode ($iBlockId)
    {
        if (!($aBlock = $this->_oQuery->getPageBlock((int)$iBlockId)))
            return '';
        return $this->_getBlockCodeWithCache($aBlock, 0);
    }


    protected function _getBlockCodeWithCache(&$aBlock, $iAsync = 0)
    {
        if (bx_is_dbg() || !getParam('sys_pb_cache_enable') || !$aBlock['cache_lifetime'] || $iAsync || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')) {
            $sBlockCode = $this->_getBlockCode($aBlock, $iAsync);
        }
        else {
            $oCache = $this->_getBlockCacheObject();
            $sKey = $this->_getBlockCacheKey(false, $aBlock);
            $sKeyCssJs = 'cssjs_' . $sKey;

            $mixedRet = $oCache->getData($sKey, $aBlock['cache_lifetime']);
            $aRetCssJs = $oCache->getData($sKeyCssJs, $aBlock['cache_lifetime']);

            if ($mixedRet !== null && $aRetCssJs !== null) {
                BxDolTemplate::getInstance()->collectingStart();
                BxDolTemplate::getInstance()->collectingInject($aRetCssJs['css'], $aRetCssJs['js']);
                $sBlockCode = BxDolTemplate::getInstance()->collectingEndGetCode();
                $sBlockCode .= $mixedRet;
            } else {
                BxDolTemplate::getInstance()->collectingStart();

                $sBlockCode = $this->_getBlockCode($aBlock, $iAsync);

                $aBlockCssJs = BxDolTemplate::getInstance()->collectingEndGetCode(array(), array(), 'array');

                $oCache->setData($sKey, $sBlockCode, $aBlock['cache_lifetime']);
                $oCache->setData($sKeyCssJs, $aBlockCssJs, $aBlock['cache_lifetime']);
            }
        }

        return $sBlockCode;
    }

    /**
     * Get block code.
     * @return string
     */
    protected function _getBlockCode(&$aBlock, $iAsync = 0)
    {
        $sContentWithBox = '';
        $oFunctions = $this->_oTemplate->getTemplateFunctions();

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginPageBlock(_t($aBlock['title']), $aBlock['id']);

        $aDbNoTitle = [BX_DB_CONTENT_ONLY, BX_DB_PADDING_CONTENT_ONLY, BX_DB_NO_CAPTION, BX_DB_PADDING_NO_CAPTION];

        $sTitle = $this->getBlockTitle($aBlock);
        $sHelp = $this->getBlockHelp($aBlock);

        $sFunc = '_getBlock' . ucfirst($aBlock['type']);
        $bBlockVisible = $this->_isVisibleBlock($aBlock);
        if ($iAsync && $bBlockVisible) {
            $iDesignboxId = $aBlock['designbox_id'];

            $sHelpTitle = $sHelpContent = '';
            if(!in_array($iDesignboxId, $aDbNoTitle))
                $sHelpTitle = $sHelp;
            else
                $sHelpContent = $sHelp;

            $sContent = $this->getBlockAsyncCode($aBlock, $iAsync);
            $aParams = array(
                $sTitle . $sHelpTitle,
                $sContent . $sHelpContent,
                $iDesignboxId
            );
            $sContentWithBox = call_user_func_array(array($oFunctions, 'designBoxContent'), $aParams);
        } 
        elseif ($bBlockVisible && method_exists($this, $sFunc)) {
            $mixedContent = $this->$sFunc($aBlock);

            if(!empty($aBlock['content_empty'])) {
                $sEmpty = _t($aBlock['content_empty']);
                $sRegExp = '/(<[A-Za-z0-9]+\b.[^<]*[\pPi\pPf]bx\-msg\-box\b.*[\pPi\pPf][^>]*>)(' . _t('_Empty') . ')(<\/[A-Za-z0-9]+>)/su';
                if(is_string($mixedContent))
                    $mixedContent = preg_replace($sRegExp, "\${1}" . $sEmpty . "\${3}", $mixedContent);
                else if(is_array($mixedContent) && !empty($mixedContent['content']))
                   $mixedContent['content'] = preg_replace($sRegExp, "\${1}" . $sEmpty . "\${3}", $mixedContent['content']);
            }

            $this->_oQuery->setReadOnlyMode(true);

            if(is_array($mixedContent) && !empty($mixedContent['content'])) {
                $iDesignboxId = isset($mixedContent['designbox_id']) ? $mixedContent['designbox_id'] : $aBlock['designbox_id'];

                $sHelpTitle = $sHelpContent = '';
                if(!in_array($iDesignboxId, $aDbNoTitle))
                    $sHelpTitle = $sHelp;
                else
                    $sHelpContent = $sHelp;

                $aParams = array(
                    (isset($mixedContent['title']) ? $mixedContent['title'] : $sTitle) . $sHelpTitle,
                    $mixedContent['content'] . $sHelpContent,
                    $iDesignboxId
                );

                $mixedMenu = false;
                if(isset($mixedContent['menu']))
                    $mixedMenu = $mixedContent['menu'];
                else if(!empty($aBlock['submenu']))
                    $mixedMenu = $aBlock['submenu'];
                $aParams[] = $mixedMenu;

                if(isset($mixedContent['buttons']))
                    $aParams[] = $mixedContent['buttons'];
                else if($mixedMenu)
                    $aParams[] = (int)$aBlock['tabs'] == 1 ? true : array();

                if(isset($mixedContent['class']))
                    $aBlock['class'] = $mixedContent['class'];
            }
            else if(is_string($mixedContent) && !empty($mixedContent)) {
                $iDesignboxId = $aBlock['designbox_id'];

                $sHelpTitle = $sHelpContent = '';
                if(!in_array($iDesignboxId, $aDbNoTitle))
                    $sHelpTitle = $sHelp;
                else
                    $sHelpContent = $sHelp;

                $aParams = array(
                    $sTitle . $sHelpTitle,
                    $mixedContent . $sHelpContent,
                    $iDesignboxId
                );

                $mixedMenu = !empty($aBlock['submenu']) ? $aBlock['submenu'] : false;
                $aParams[] = $mixedMenu;

                if($mixedMenu)
                    $aParams[] = (int)$aBlock['tabs'] == 1 ? true : array();
            }

            if(!empty($aParams))
                $sContentWithBox = call_user_func_array(array($oFunctions, 'designBoxContent'), $aParams);
        }

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endPageBlock($aBlock['id'], $sContentWithBox ? false : true, false );

        return $sContentWithBox;
    }

    /**
     * Add necessary js and css files.
     */
    protected function _addJsCss()
    {
        $this->_oTemplate->addJs(array('BxDolPage.js', 'theia-sticky-sidebar/theia-sticky-sidebar.min.js', 'theia-sticky-sidebar/ResizeSensor.min.js'));
        $this->_oTemplate->addCss('page_layouts.css');
    }

    /**
     * Set system template variables, like page title, meta description, meta keywords and meta robots.
     */
    protected function _addSysTemplateVars ()
    {
        $oTemplate = BxDolTemplate::getInstance();

        $sPageTitle = $this->_getPageTitle();
        if ($sPageTitle)
            $oTemplate->setPageHeader ($sPageTitle);

        $sMetaTitle = $this->_getPageMetaTitle();
        if ($sMetaTitle)
            $oTemplate->setPageMetaTitle ($sMetaTitle);
        
        $sMetaDesc = $this->_getPageMetaDesc();
        if ($sMetaDesc)
            $oTemplate->setPageDescription ($sMetaDesc);

        $sMetaRobots = $this->_getPageMetaRobots();
        if ($sMetaRobots)
            $oTemplate->setPageMetaRobots ($sMetaRobots);

        $sMetaImage = $this->_getPageMetaImage();
        if ($sMetaImage)
            $oTemplate->addPageMetaImage($sMetaImage);

        $sMetaKeywords = $this->_getPageMetaKeywords();
        if ($sMetaKeywords)
            $oTemplate->addPageKeywords ($sMetaKeywords);
    }

    /**
     * Select menu from page properties.
     */
    protected function _selectMenu ()
    {
        BxDolMenu::setSelectedGlobal ($this->_aObject['module'], $this->_aObject['uri']);
    }

    /**
     * Set page submenu if it's specified
     */
    protected function _setSubmenu ($aParams)
    {
        if(empty($this->_aObject['submenu']) || $this->_aObject['submenu'] == 'disabled')
            return;

        $oMenuSubmenu = BxDolMenu::getObjectInstance('sys_site_submenu');
        if(!$oMenuSubmenu)
            return;

        $oMenuSubmenu->setObjectSubmenu($this->_aObject['submenu'], $aParams);
    }

    /**
     * Get content for 'raw' block type.
     * @return string
     */
    protected function _getBlockRaw ($aBlock)
    {
        if (bx_is_api()){
            return [bx_api_get_block('raw', ['title' => _t($aBlock['title']), 'content' => BxDolTemplate::getInstance()->parseHtmlByContent($aBlock['content'], array())])];
        }
        
        $s = '<div class="bx-page-raw-container bx-def-vanilla-html max-w-none">' . BxDolTemplate::getInstance()->parseHtmlByContent($aBlock['content'], array()) . '</div>';
        $s = $this->_replaceMarkers($s, array('block_id' => $aBlock['id']));
        $s = bx_process_macros($s);
        return $s;
    }

    /**
     * Get content for 'custom' block type.
     * @return string
     */
    protected function _getBlockCustom ($aBlock)
    {
        if (bx_is_api()){
            return [bx_api_get_block('custom', ['title' => _t($aBlock['title']), 'content' => BxDolTemplate::getInstance()->parseHtmlByContent($aBlock['content'], array())])];
        }
        
        $s = '<div class="bx-page-custom-container">' . BxDolTemplate::getInstance()->parseHtmlByContent($aBlock['content'], array()) . '</div>';
        $s = $this->_replaceMarkers($s, array('block_id' => $aBlock['id']));
        $s = bx_process_macros($s);
        return $s;
    }

    /**
     * Get content for 'html' block type.
     * @return string
     */
    protected function _getBlockHtml ($aBlock)
    {
        if (bx_is_api()){
            return [bx_api_get_block('html', ['title' => _t($aBlock['title']), 'content' => $aBlock['content']])];
        }
        
        $s = '<div class="bx-page-html-container bx-def-vanilla-html max-w-none">' . $aBlock['content'] . '</div>';
        $s = $this->_replaceMarkers($s, array('block_id' => $aBlock['id']));
        $s = bx_process_macros($s);
        return $s;
    }
    
    /**
     * Get content for 'creative' block type.
     * @return string
     */
    protected function _getBlockCreative ($aBlock)
    {
        if(bx_is_api())
            return [bx_api_get_block('creative', ['title' => _t($aBlock['title']), 'content' => $aBlock['content']])];

        $s = $this->_replaceMarkers($aBlock['content'], ['block_id' => $aBlock['id']]);
        $s = bx_process_macros($s);

        $bCanManage = $this->isAllowedCreativeManage();

        if($bCanManage) {
            $this->_oTemplate->addCss([
                BX_DIRECTORY_PATH_PLUGINS_PUBLIC . 'grapesjs/|grapes.min.css',
            ]);

            $this->_oTemplate->addJs([
                'grapesjs/grapes.min.js',
                'grapesjs/grapesjs-blocks-basic.js',
                'grapesjs/grapesjs-style-bg.js',
                'grapesjs/grapesjs-preset-webpage.min.js',
            ]);
        }
        
        return $this->_oTemplate->parseHtmlByName('block_creative.html', [
            'block_id' => $aBlock['id'],
            'content' => $s,
            'bx_if:show_controls' => [
                'condition' => $bCanManage,
                'content' => [
                    'onclick_edit' => $this->_sJsObjectName . '.creativeEdit(this, ' . $aBlock['id'] . ')',
                    'onclick_save' => $this->_sJsObjectName . '.creativeSave(this, ' . $aBlock['id'] . ')'
                ]
            ]
        ]);
    }

    /**
     * Get content for 'wiki' block type.
     * @return string
     */
    protected function _getBlockWiki ($aBlock)
    {
        $oWiki = BxDolWiki::getObjectInstance($this->_aObject['module']);
        if (!$oWiki) {
            $sContent = _t('_sys_wiki_error_missing_wiki_object', $this->_aObject['module']);
        } 
        else {
            $sContent = $oWiki->getBlockContent($aBlock['id'], false, (int)bx_get($aBlock['id'].'rev') ? (int)bx_get($aBlock['id'].'rev') : false);
        }

        $s = '<div id="bx-page-wiki-container-' . $aBlock['id'] . '" class="bx-page-wiki-container markdown-body bx-def-vanilla-html">' . $sContent . '</div>';
        $s = $this->_replaceMarkers($s, array('block_id' => $aBlock['id']));
        $s = bx_process_macros($s);
        return $s;
    }

    /**
     * Get content for 'lang' block type.
     * @return string
     */
    protected function _getBlockLang ($aBlock)
    {
        if (bx_is_api()){
            return [bx_api_get_block('lang', ['title' => _t($aBlock['title']), 'content' => bx_process_macros(_t(trim($aBlock['content'])))])];
        }
    
        $s = '<div class="bx-page-lang-container bx-def-vanilla-html max-w-none">' . _t(trim($aBlock['content'])) . '</div>';
        $s = $this->_replaceMarkers($s, array('block_id' => $aBlock['id']));
        $s = bx_process_macros($s);
        return $s;
    }

    /**
     * Get content for 'image' block type.
     * @return string
     */
    protected function _getBlockImage ($aBlock)
    {
        if (empty($aBlock['content']))
            return false;

        list($iFileId, $sAlign ) = explode('#', $aBlock['content']);
        $iFileId = (int)$iFileId;
        if (!$iFileId)
            return false;

        $oStorage = BxDolStorage::getObjectInstance(BX_DOL_STORAGE_OBJ_IMAGES);
        if (!$oStorage)
            return false;

        $sUrl = $oStorage->getFileUrlById($iFileId);
        if (!$sUrl)
            return false;

        $sStyleAdd = '';
        $sClassAdd = 'bx-page-image-container';
        $aAlign = ['left' => 'start', 'center' => 'center', 'right' => 'end'];
        if(array_key_exists($sAlign, $aAlign)) {
            $sClassAdd .= ' flex justify-' . $aAlign[$sAlign];

            $sStyleAdd = 'style="text-align:' . $sAlign . '"';
        }

        return '<div class="' . $sClassAdd . '" ' . $sStyleAdd . '><img src="' . $sUrl . '" /></div>';
    }

    /**
     * Get content for 'rss' block type.
     * @return string
     */
    protected function _getBlockRss ($aBlock)
    {
        if (empty($aBlock['content']))
            return false;

        list( $sUrl, $iNum ) = explode('#', $aBlock['content']);
        $iNum = (int)$iNum;

        return BxDolRss::getObjectInstance('sys_page_block')->getHolder($aBlock['id'], $iNum);
    }

    /**
     * Get content for 'menu' block type.
     * @return string
     */
    protected function _getBlockMenu ($aBlock)
    {
        $oMenu = BxTemplMenu::getObjectInstance($aBlock['content']);
        return $oMenu ? $oMenu->getCode () : '';
    }

    /**
     * Get content for 'service' block type.
     * @return string
     */
    protected function _getBlockService ($aBlock)
    {
        $aMarkers = array_merge($this->_aMarkers, [
            'block_id' => $aBlock['id']
        ]);

        return BxDolService::callSerialized($aBlock['content'], $aMarkers);
    }

    /**
     * Get page title.
     * @return string
     */
    protected function _getPageTitle()
    {
        return $this->_replaceMarkers(_t($this->_aObject['title']));
    }

    /**
     * Get page meta description.
     * @return string
     */
    protected function _getPageMetaDesc()
    {
        return $this->_replaceMarkers(_t($this->_aObject['meta_description']));
    }

    /**
     * Get page meta title.
     * @return string
     */
    protected function _getPageMetaTitle()
    {
        return $this->_replaceMarkers(_t($this->_aObject['meta_title']));
    }
    
    /**
     * Get page meta image.
     * @return string
     */
    protected function _getPageMetaImage()
    {
        return '';
    }
    
    /**
     * Get page meta keywords.
     * @return string
     */
    protected function _getPageMetaKeywords()
    {
        return $this->_replaceMarkers(_t($this->_aObject['meta_keywords']));
    }

    /**
     * Get page meta robots.
     * @return string
     */
    protected function _getPageMetaRobots()
    {
        return _t($this->_aObject['meta_robots']);
    }

    /**
     * Get page not found message.
     * @return string
     */
    protected function _getPageNotFoundMsg ($mixedMsg = false)
    {
        $this->_oTemplate->displayPageNotFound();
        exit;
    }

    /**
     * Get access denied message.
     * @return string
     */
    protected function _getPageAccessDeniedMsg ($mixedMsg = false)
    {
        return MsgBox($mixedMsg !== false ? $mixedMsg : _t('_Access denied'));
    }

    /**
     * Get page cache object.
     * @return cache object instance
     */
    protected function _getPageCacheObject ()
    {
        return $this->_getCacheObject ();
    }

    /**
     * Get block cache object.
     * @return cache object instance
     */
    protected function _getBlockCacheObject ()
    {
        return $this->_getCacheObject ('Block', 'pb');
    }

    protected function _getCacheObject ($sSuffixObj = 'Page', $sSuffixParam = 'page')
    {
        $sObj = '_o' . $sSuffixObj . 'CacheObject';
        $sParam = 'sys_' . $sSuffixParam . '_cache_engine';

        if ($this->{$sObj} != null) {
            return $this->{$sObj};
        } else {
            $sEngine = getParam($sParam);
            $this->{$sObj} = bx_instance ('BxDolCache' . $sEngine);
            if (!$this->{$sObj}->isAvailable())
                $this->{$sObj} = bx_instance ('BxDolCacheFile');
            return $this->{$sObj};
        }
    }

    /**
     * Get page cache key.
     * @param $isPrefixOnly return key prefix only.
     * @return string
     */
    protected function _getPageCacheKey ($isPrefixOnly = false)
    {
        $s = 'page_' . $this->_aObject['object'] . '_';
        if ($isPrefixOnly)
            return $s;
        $s .= $this->_getPageCacheParams ();
        $s .= bx_site_hash() . '.php';
        return $s;
    }

    /**
     * Get block cache key.
     * @param $isPrefixOnly return key prefix only.
     * @return string
     */
    protected function _getBlockCacheKey ($isPrefixOnly = false, $aBlock = array())
    {
        $s = $this->_getPageCacheKey(true) . 'block_';
        if ($isPrefixOnly)
            return $s;
        $s .= ($aBlock ? $aBlock['id'] : 0) . '_' . $this->_getPageCacheParams ();
        $s .= bx_site_hash() . '.php';
        return $s;
    }

    /**
     * Additional cache key. In the case of dynamic page.
     * For example - profile page, where each profile has own page.
     * @return string
     */
    protected function _getPageCacheParams ()
    {
        return md5(serialize($_GET) . bx_lang_name());
    }

    /**
     * Clean page cache.
     * @param $isDelAllWithPagePrefix delete cache by prefix, it can be used for dynamic pages, like profile view, where for each profile separate cache is generated.
     * @return string
     */
    protected function cleanCache ($isDelAllWithPagePrefix = false)
    {
        $a = ['Page', 'Block'];
        $bRet = false;
        foreach ($a as $s) {
            $oCache = $this->{'_get' . $s . 'CacheObject'} ();
            $sKey = $this->{'_get'. $s . 'CacheKey'} ($isDelAllWithPagePrefix);

            if ($isDelAllWithPagePrefix)
                $bRet = $bRet && $oCache->removeAllByPrefix($sKey);
            else
                $bRet = $bRet && $oCache->delData($sKey);
        }
        return $bRet;
    }
}

/** @} */
