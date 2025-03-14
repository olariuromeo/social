<?php
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'title' => 'Artificer',
    'version_from' => '14.0.7',
    'version_to' => '14.0.8',
    'vendor' => 'UNA INC',

    'compatible_with' => array(
        '14.0.0-RC4'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
     */
    'home_dir' => 'boonex/artificer/updates/update_14.0.7_14.0.8/',
    'home_uri' => 'artificer_update_1407_1408',

    'module_dir' => 'boonex/artificer/',
    'module_uri' => 'artificer',

    'db_prefix' => 'bx_artificer_',
    'class_prefix' => 'BxArtificer',

    /**
     * Installation/Uninstallation Section.
     */
    'install' => array(
        'execute_sql' => 0,
        'update_files' => 1,
        'update_languages' => 0,
        'clear_db_cache' => 0,
    ),

    /**
     * Category for language keys.
     */
    'language_category' => 'Boonex Artificer Template',

    /**
     * Files Section
     */
    'delete_files' => array(),
);
