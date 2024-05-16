<?php
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 */

class BxTimelineUpdater extends BxDolStudioUpdater
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }
    
    public function actionExecuteSql($sOperation)
    {
        if($sOperation == 'install') {
            if(!$this->oDb->isFieldExists('bx_timeline_photos', 'dimensions'))
                $this->oDb->query("ALTER TABLE `bx_timeline_photos` ADD `dimensions` varchar(24) NOT NULL AFTER `size`");
        }

        return parent::actionExecuteSql($sOperation);
    }
}
