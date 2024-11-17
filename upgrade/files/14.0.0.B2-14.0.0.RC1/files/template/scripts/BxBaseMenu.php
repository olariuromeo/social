<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

/**
 * Menu representation.
 * @see BxDolMenu
 */
class BxBaseMenu extends BxDolMenu
{
    protected $_oTemplate;
    protected $_iPageType; 
    protected $_aOptionalParams = array('target' => '', 'onclick' => '');
    protected $_bDisplayAddons = false;

    protected $_aSelected; // Selected menu item.

    public function __construct ($aObject, $oTemplate)
    {
        parent::__construct ($aObject);

        if ($oTemplate)
            $this->_oTemplate = $oTemplate;
        else
            $this->_oTemplate = BxDolTemplate::getInstance();

        $this->_iPageType = false;
        $this->_aSelected = [];
    }

    public function getDisplayAddons()
    {
        return $this->_bDisplayAddons;
    }   

    public function setDisplayAddons($b)
    {
        $bRet = $this->_bDisplayAddons;
        $this->_bDisplayAddons = $b;
        return $bRet;
    }

    /**
     * Get menu code.
     * @return string
     */
    public function getCode ()
    {
        $sMenuTitle = isset($this->_aObject['title']) ? _t($this->_aObject['title']) : 'Menu-' . rand(0, PHP_INT_MAX);
        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginMenu($sMenuTitle);

        if(!$this->_iPageType)
            $this->_iPageType = BxDolTemplate::getInstance()->getPageType();

        $s = '';
        $aVars = $this->_getTemplateVars ();
        if (!empty($aVars['bx_repeat:menu_items'])) {
            $this->_addJsCss();
            $s = $this->_getCode($this->_aObject['template'], $aVars);
        }

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endMenu($sMenuTitle);

        return $s;
    }

    /**
     * Get menu code API.
     * @return array
     */
    public function getCodeAPI ()
    {
        $aItems = [];

        $aVars = $this->_getTemplateVars();
        if(!empty($aVars['bx_repeat:menu_items']))
            $aItems = $aVars['bx_repeat:menu_items'];

        return [
            'object' => $this->_sObject, 
            'persistent' => $this->_aObject['persistent'],
            'params' => $this->getContentParams(),
            'items' => $aItems
        ];
    }

    /**
     * Get menu short code API.
     * @return array
     */
    public function getShortCodeAPI ()
    {
        return [
            'object' => $this->_sObject, 
            'params' => $this->getContentParams()
        ];
    }

    protected function _getCode($sTmplName, $aTmplVars)
    {
        return $this->_oTemplate->parseHtmlByName($this->getTemplateName($sTmplName), $aTmplVars);
    }

    public function getCodeItem ($sName)
    {
        if(empty($sName))
            return '';

        $sCode = $this->_oTemplate->getHtml(str_replace('.html', '_item.html', $this->_aObject['template']));
        if(empty($sCode))
            return '';

        $mixedTmplVars = $this->getMenuItem($sName);
        if($mixedTmplVars === false)
            return '';

        return $this->_oTemplate->parseHtmlByContent($sCode, $mixedTmplVars);
    }

    /**
     * Get template variables array
     * @return array
     */
    protected function _getTemplateVars ()
    {
        $aMenuItems = $this->getMenuItems();
        $sMenuItemSelected = _t($aMenuItems && $this->_aSelected && !empty($this->_aSelected['title']) ? $this->_aSelected['title'] : '_Contents');

        return [
            'object' => $this->_sObject,
            'menu_item_selected' => $sMenuItemSelected,
            'bx_repeat:menu_items' => $aMenuItems,
        ];
    }

    /**
     * Get menu items array, which is ready to pass to menu template. 
     * @return array or false
     */
    public function getMenuItems ()
    {
        if (!isset($this->_aObject['menu_items']))
            $this->_aObject['menu_items'] = $this->getMenuItemsRaw ();

        $aItems = array();
        foreach ($this->_aObject['menu_items'] as $aItem) {
            $aItem = $this->_getMenuItem ($aItem);
            if($aItem !== false)
                $aItems[] = $aItem;
        }

        return $aItems;
    }
    
    /**
     * Get menu item array, which is ready to pass to whole menu or 
     * single menu item template. May return false if single menu item 
     * is requested but cannot be shown by circumstances.
     * @return array or false
     */
    public function getMenuItem ($sName)
    {
        if (!isset($this->_aObject['menu_items']))
            $this->_aObject['menu_items'] = $this->getMenuItemsRaw ();

        if(!empty($sName))
            return $this->_getMenuItem($this->_aObject['menu_items'][$sName]);
    }

    /**
     * Get menu items array, this is just a wrapper for DB function for make it easier to override.
     * It is used in @see BxBaseMenu::getMenuItems
     * @return array
     */
    protected function getMenuItemsRaw ()
    {
        if($this->_bMultilevel)
            return $this->_oQuery->getMenuItemsHierarchy();
        else 
            return $this->_oQuery->getMenuItems();
    }

    protected function _getMenuItem ($a)
    {
        if (!$this->_isActive($a) || !$this->_isVisible($a))
            return false;

        if ($this->_bIsApi) {
            list ($sIcon, $sIconUrl) = $this->_getMenuIcon($a);

            $aResult = [
                'id' => $a['id'],
                'name' => $a['name'],
                'title' => _t($a['title']),
                'link' => isset($a['link']) ? $a['link'] : '',
                'icon' => $sIcon ? $sIcon : '',
                'image' => $sIconUrl ? $sIconUrl : '',
                'submenu' => !empty($a['submenu_object']) ? $a['submenu_object'] : '',
                'addon' => $this->_bDisplayAddons ? $this->_getMenuAddon($a) : '',
                'primary' => $a['primary'],
            ];

            if(($aMarkers = $this->_getMenuMarkers($a)) && is_array($aMarkers))
                $this->addMarkers($aMarkers);
            $aResult = $this->_replaceMarkers($aResult);

            if(!empty($aResult['link']))
                $aResult['link'] = $this->_oPermalinks->permalink($aResult['link']);

            return $aResult;
        }

        $bIsSelected = $this->_isSelected($a);
        if($bIsSelected)
            $this->_aSelected = $a;

        $a['object'] = $this->_sObject;

        $a['title'] = _t($a['title']);
        $a['bx_if:title'] = array(
            'condition' => !empty($a['title']),
            'content' => array(
                'title' => $a['title']
            )
        );

        $this->removeMarker('addon');

        $a = $this->_replaceMarkers($a);

        if ($this->_bDisplayAddons) {
            $mixedAddon = $this->_getMenuAddon($a);
            if (!is_array($mixedAddon)) {
                $this->addMarkers(array('addon' => $mixedAddon));
                $a = $this->_replaceMarkers($a);
            }
        }

        $aMarkers = $this->_getMenuMarkers($a);
        if ($aMarkers && is_array($aMarkers)) {
            $this->addMarkers($aMarkers);
            $a = $this->_replaceMarkers($a);
        }

        list ($sIcon, $sIconUrl, $sIconA, $sIconHtml) = $this->_getMenuIcon($a);

        $a['class_add'] = $bIsSelected ? 'bx-menu-tab-active' : '';
        $a['class_add'] .= $this->_getVisibilityClass($a);
        $a['class_link'] = '';

        $a['link'] = isset($a['link']) ? $this->_oPermalinks->permalink($a['link']) : 'javascript:void(0);';
        $a['title_attr'] = bx_html_attribute(strip_tags($a['title']));

        $a['attrs'] = $this->_getMenuAttrs($a);
        $a['attrs_wrp'] = '';

        if($this->_bHx && !empty($a['link']) && strpos($a['link'], 'javascript:') === false) {
            $this->_aHx['get'] = $a['link'];
            $a['attrs'] .= bx_get_htmx_attrs($this->_aHx, $this->_mHxPreload);

            if(!bx_is_htmx_request() && !$bIsSelected)
                $a['attrs_wrp'] .= bx_get_htmx_attrs([
                    'get' => $a['link'],
                    'trigger' => 'load',
                    'target' => '#bx-content-preload',
                    'swap' => 'beforeend',
                ]);
        }

        $a['bx_if:image'] = array (
            'condition' => (bool)$sIconUrl,
            'content' => array('icon_url' => $sIconUrl),
        );
        $a['bx_if:image_inline'] = array (
            'condition' => false,
            'content' => array('image' => ''),
        );
        $a['bx_if:icon'] = array (
            'condition' => (bool)$sIcon,
            'content' => array('icon' => $sIcon),
        );
        $a['bx_if:icon-html'] = array (
            'condition' => (bool)$sIconHtml,
            'content' => array('icon' => $sIconHtml),
        );
        $a['bx_if:icon-a'] = array (
            'condition' => (bool)$sIconA,
            'content' => array('icon-a' => $sIconA),
        );
        $a['bx_if:title'] = array (
            'condition' => (bool)$a['title'],
            'content' => array(
                'title' => $a['title'],
                'title_attr' => $a['title_attr']
            ),
        );

        $bOnClick = !empty($a['onclick']);
        $aOnClick = $bOnClick ? [
            'onclick' => $a['onclick'],
        ] : [];

        $a['bx_if:onclick'] = [
            'condition' => $bOnClick,
            'content' => $aOnClick
        ];

        $aTmplVarsAddon = $this->_bDisplayAddons ? $this->_getTmplVarsAddon($mixedAddon, $a) : array('addon' => '', 'addonf' => '');
        $a['bx_if:addon'] = array (
            'condition' => $this->_bDisplayAddons && !empty($aTmplVarsAddon['addon']),
            'content' => $aTmplVarsAddon
        );

        $aTmplVarsSubitems = array('subitems' => '');
        $bTmplVarsSubitems = $this->_bMultilevel && !empty($a['subitems']);
        if($bTmplVarsSubitems) {
            $sClassCollpsed = 'bx-mi-collapsed';
            if(($iCollapsed = $this->getUserChoiceCollapsedSubmenu($a)) !== false)
                $a['class_add'] .= $iCollapsed ? ' ' . $sClassCollpsed : '';
            else if(isset($a['collapsed']) && $a['collapsed'])
                $a['class_add'] .= ' ' . $sClassCollpsed;

            $aSubitems = array();
            foreach($a['subitems'] as $aSubitem) {
                $aSubitem = $this->_getMenuItem($aSubitem);
                if($aSubitem !== false)
                    $aSubitems[] = $aSubitem;
            }

            if(!$bOnClick)
                $a['bx_if:onclick'] = [
                    'condition' => true,
                    'content' => [
                        'onclick' => "javascript:return bx_menu_toggle(this, '" . $this->_sObject . "', '" . $a['name'] . "')"
                    ]
                ];

            $aTmplVarsSubitems['subitems'] = $this->_oTemplate->parseHtmlByName(str_replace('.html', '_subitems.html', $this->getTemplateName()), array(
                'bx_repeat:menu_items' => $aSubitems,
            ));
        }

        $a['bx_if:show_toggler'] = [
            'condition' => $bTmplVarsSubitems,
            'content' => []
        ];
        $a['bx_if:show_subitems'] = array (
            'condition' => $bTmplVarsSubitems,
            'content' => $aTmplVarsSubitems
        );

        unset($a['subitems']);

        foreach ($this->_aOptionalParams as $sName => $sDefaultValue)
            if (!isset($a[$sName]))
                $a[$sName] = $sDefaultValue;

        return $a;
    }
    
    protected function _getMenuIcon ($a)
    {
        return BxTemplFunctions::getInstanceWithTemplate($this->_oTemplate)->getIcon(!empty($a['icon']) ? $a['icon'] : '');
    }
    
    public function getMenuIconHtml($sIcon)
    {
        list ($sIcon, $sIconUrl, $sIconA, $sIconHtml) = BxTemplFunctions::getInstanceWithTemplate($this->_oTemplate)->getIcon($sIcon);

        $a['bx_if:image'] = array (
            'condition' => (bool)$sIconUrl,
            'content' => array('icon_url' => $sIconUrl),
        );
        $a['bx_if:icon'] = array (
            'condition' => (bool)$sIcon,
            'content' => array('icon' => $sIcon),
        );
        $a['bx_if:icon-a'] = array (
            'condition' => (bool)$sIconA,
            'content' => array('icon-a' => $sIconA),
        );
        $a['bx_if:icon-html'] = array (
            'condition' => (bool)$sIconHtml,
            'content' => array('icon' => $sIconHtml),
        );

        return $this->_oTemplate->parseHtmlByName('menu_icon.html', $a);
    }

    protected function _getMenuAddon ($aMenuItem)
    {
        if (empty($aMenuItem['addon']))
            return '';

        if (isset($aMenuItem['addon_cache']) && $aMenuItem['addon_cache']) {
            $oCache = BxDolDb::getInstance()->getDbCacheObject();
            $sKey = 'menu_' . $this->_sObject . '_' . $aMenuItem['name'] . '_' . bx_get_logged_profile_id() . '_' . bx_site_hash() . '.php';
            $s = $oCache->getData($sKey);
            if ($s !== null) {
                return $s;
            }
            else {
                $s = BxDolService::callSerialized($aMenuItem['addon'], $this->_aMarkers);
                $oCache->setData($sKey, $s);
            }

            return $s;
        }
        else {
            return BxDolService::callSerialized($aMenuItem['addon'], $this->_aMarkers);
        }
    }

    protected function _getMenuMarkers ($aMenuItem)
    {
        if (empty($aMenuItem['markers']))
            return '';

        return BxDolService::callSerialized($aMenuItem['markers'], $this->_aMarkers);
    }

    protected function _getMenuAttrs ($aMenuItem)
    {
        $sAttrs = '';
        if(!empty($aMenuItem['target']))
            $sAttrs .= ' target="' . $aMenuItem['target'] . '"';

        if($this->_bAddNoFollow && !empty($aMenuItem['link']) && preg_match('@^https?://@', $aMenuItem['link']) && strncmp($aMenuItem['link'], BX_DOL_URL_ROOT, strlen(BX_DOL_URL_ROOT)) !== 0)
            $sAttrs .= ' rel="noreferrer"';

        return $sAttrs;
    }

    /**
     * Add css/js files which are needed for menu display and functionality.
     */
    protected function _addJsCss()
    {
        $this->_oTemplate->addCss('menu.css');
    }

    protected function _getTmplVarsAddon($mixedAddon, $aMenuItem) 
    {
        $sAddon = '';
        if(!is_array($mixedAddon))
            $sAddon = $mixedAddon;
        else if(!empty($mixedAddon['addon']))
            $sAddon = $mixedAddon['addon'];

        return array(
            'addon' => $sAddon,
            'addonf' => ''
        );
    }
}

/** @} */
