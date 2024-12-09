<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Developer Developer
 * @ingroup     UnaModules
 *
 * @{
 */

class BxDevForms extends BxTemplStudioForms
{
    protected $oModule;
    protected $aParams;
    protected $aGridObjects;

    function __construct($aParams)
    {
        parent::__construct(isset($aParams['page']) ? $aParams['page'] : '');
        
        $this->bPageMenuTitle = false;

        $this->aParams = $aParams;
        $this->sSubpageUrl = $this->aParams['url'] . '&form_page=';

        unset(
            $this->aMenuItems[BX_DOL_STUDIO_FORM_TYPE_LABELS],
            $this->aMenuItems[BX_DOL_STUDIO_FORM_TYPE_CATEGORIES],
            $this->aMenuItems[BX_DOL_STUDIO_FORM_TYPE_GROUPS_ROLES]
        );

        $this->oModule = BxDolModule::getInstance('bx_developer');

        $this->aGridObjects = array(
            'forms' => $this->oModule->_oConfig->getObject('grid_forms'),
            'displays' => $this->oModule->_oConfig->getObject('grid_forms_displays'),
            'fields' => $this->oModule->_oConfig->getObject('grid_forms_fields'),

            'pre_lists' => $this->oModule->_oConfig->getObject('grid_forms_pre_lists'),
            'pre_values' => $this->oModule->_oConfig->getObject('grid_forms_pre_values'),

            'search_forms' => $this->oModule->_oConfig->getObject('grid_search_forms'),
            'search_fields' => $this->oModule->_oConfig->getObject('grid_search_forms_fields'),
        );

        $this->oModule->_oTemplate->addStudioCss(array('forms.css'));
    }
}

/** @} */
