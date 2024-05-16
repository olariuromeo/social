<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaView UNA Studio Representation classes
 * @ingroup     UnaStudio
 * @{
 */

class BxBaseStudioAPI extends BxDolStudioAPI
{
    protected $sSubpageUrl;
    protected $aMenuItems;
    protected $aGridObjects;

    public function __construct($sPage = '')
    {
        parent::__construct($sPage);

        $this->aPageCss = array_merge($this->aPageCss, ['forms.css', 'paginate.css']);
        
        $this->sSubpageUrl = BX_DOL_URL_STUDIO . 'api.php?page=';

        $this->aMenuItems = array(
            BX_DOL_STUDIO_API_TYPE_SETTINGS => array('icon' => 'cogs'),
            BX_DOL_STUDIO_API_TYPE_CONFIG => array('icon' => 'cogs'),
            BX_DOL_STUDIO_API_TYPE_ORIGINS => array('icon' => 'globe'),
            BX_DOL_STUDIO_API_TYPE_KEYS => array('icon' => 'key'),
        );

        $this->aGridObjects = array(
            'keys' => 'sys_studio_api_keys',
            'origins' => 'sys_studio_api_origins',
        );
    }

    public function getPageJsCode($aOptions = array(), $bWrap = true)
    {
        $aOptions = array_merge($aOptions, array(
            'sActionUrl' => BX_DOL_URL_STUDIO . 'api.php'
        ));

        return parent::getPageJsCode($aOptions, $bWrap);
    }

    public function getPageMenu($aMenu = array(), $aMarkers = array())
    {
        $sJsObject = $this->getPageJsObject();

        $aMenu = array();
        foreach($this->aMenuItems as $sMenuItem => $aItem)
            $aMenu[] = array(
                'name' => $sMenuItem,
                'icon' => $aItem['icon'],
                'link' => $this->sSubpageUrl . $sMenuItem,
                'title' => _t('_adm_lmi_cpt_' . $sMenuItem),
                'selected' => $sMenuItem == $this->sPage
            );

        return parent::getPageMenu($aMenu);
    }

    protected function getSettings()
    {
        $oOptions = new BxTemplStudioOptions(BX_DOL_STUDIO_STG_TYPE_DEFAULT, [
            'api_general', 
            'api_layout'
        ]);

        $this->aPageCss = array_merge($this->aPageCss, $oOptions->getCss());
        $this->aPageJs = array_merge($this->aPageJs, $oOptions->getJs());
        
        return $oOptions->getCode();
    }
    
    protected function getApiConfig()
    {
        $oOptions = new BxTemplStudioOptionsApi(BX_DOL_STUDIO_STG_TYPE_DEFAULT, [
            'api_config'
        ]);

        $this->aPageCss = array_merge($this->aPageCss, $oOptions->getCss(), [BX_DIRECTORY_PATH_PLUGINS_PUBLIC . 'codemirror/|codemirror.css']);
        $this->aPageJs = array_merge($this->aPageJs, $oOptions->getJs(), ['codemirror/codemirror.min.js']);

        return $oOptions->getCode() . $this->getPageJsCode([
            'sCodeMirror' => "textarea[name='sys_api_config']"
        ]);
    }

    protected function getKeys()
    {
        return $this->getGrid($this->aGridObjects['keys']);
    }

    protected function getOrigins()
    {
        return $this->getGrid($this->aGridObjects['origins']);
    }

    protected function getGrid($sObjectName)
    {
        $oGrid = BxDolGrid::getObjectInstance($sObjectName);
        if(!$oGrid)
            return '';

        return $oGrid->getCode();
    }
}

/** @} */
