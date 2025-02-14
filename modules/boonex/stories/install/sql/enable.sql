
-- SETTINGS
SET @iTypeOrder = (SELECT MAX(`order`) FROM `sys_options_types` WHERE `group` = 'modules');
INSERT INTO `sys_options_types`(`group`, `name`, `caption`, `icon`, `order`) VALUES 
('modules', 'bx_stories', '_bx_stories', 'bx_stories@modules/boonex/stories/|std-icon.svg', IF(ISNULL(@iTypeOrder), 1, @iTypeOrder + 1));
SET @iTypeId = LAST_INSERT_ID();

INSERT INTO `sys_options_categories` (`type_id`, `name`, `caption`, `order`)
VALUES (@iTypeId, 'bx_stories', '_bx_stories', 1);
SET @iCategId = LAST_INSERT_ID();

INSERT INTO `sys_options` (`name`, `value`, `category_id`, `caption`, `type`, `check`, `check_error`, `extra`, `order`) VALUES
('bx_stories_enable_auto_approve', 'on', @iCategId, '_bx_stories_option_enable_auto_approve', 'checkbox', '', '', '', 0),
('bx_stories_summary_chars', '700', @iCategId, '_bx_stories_option_summary_chars', 'digit', '', '', '', 10),
('bx_stories_plain_summary_chars', '200', @iCategId, '_bx_stories_option_plain_summary_chars', 'digit', '', '', '', 12),
('bx_stories_card_media_num', '10', @iCategId, '_bx_stories_option_card_media_num', 'digit', '', '', '', 16),
('bx_stories_per_page_browse', '12', @iCategId, '_bx_stories_option_per_page_browse', 'digit', '', '', '', 20),
('bx_stories_per_page_profile', '6', @iCategId, '_bx_stories_option_per_page_profile', 'digit', '', '', '', 22),
('bx_stories_rss_num', '10', @iCategId, '_bx_stories_option_rss_num', 'digit', '', '', '', 30),
('bx_stories_searchable_fields', '', @iCategId, '_bx_stories_option_searchable_fields', 'list', '', '', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:21:"get_searchable_fields";}', 40),
('bx_stories_expiration_period', '48', @iCategId, '_bx_stories_option_expiration_period', 'digit', '', '', '', 50),
('bx_stories_duration', '3', @iCategId, '_bx_stories_option_duration', 'digit', '', '', '', 52);

-- PAGE: create entry
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_create_entry', '_bx_stories_page_title_sys_create_entry', '_bx_stories_page_title_create_entry', 'bx_stories', 5, 2147483647, 1, 'create-story', 'page.php?i=create-story', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES
('bx_stories_create_entry', 1, 'bx_stories', '_bx_stories_page_block_title_create_entry', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"entity_create";}', 0, 1, 1);

-- PAGE: add images
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_add_media', '_bx_stories_page_title_sys_add_media', '_bx_stories_page_title_add_media', 'bx_stories', 5, 2147483647, 1, 'story-add-media', '', '', '', '', 0, 1, 0, 'BxStoriesPageEntry', 'modules/boonex/stories/classes/BxStoriesPageEntry.php');

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES
('bx_stories_add_media', 1, 'bx_stories', '_bx_stories_page_block_title_add_media', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:16:"entity_add_files";}', 0, 0, 0);

-- PAGE: edit entry
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_edit_entry', '_bx_stories_page_title_sys_edit_entry', '_bx_stories_page_title_edit_entry', 'bx_stories', 5, 2147483647, 1, 'edit-story', '', '', '', '', 0, 1, 0, 'BxStoriesPageEntry', 'modules/boonex/stories/classes/BxStoriesPageEntry.php');

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES
('bx_stories_edit_entry', 1, 'bx_stories', '_bx_stories_page_block_title_edit_entry', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:11:"entity_edit";}', 0, 0, 0);

-- PAGE: delete entry
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_delete_entry', '_bx_stories_page_title_sys_delete_entry', '_bx_stories_page_title_delete_entry', 'bx_stories', 5, 2147483647, 1, 'delete-story', '', '', '', '', 0, 1, 0, 'BxStoriesPageEntry', 'modules/boonex/stories/classes/BxStoriesPageEntry.php');

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES
('bx_stories_delete_entry', 1, 'bx_stories', '_bx_stories_page_block_title_delete_entry', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"entity_delete";}', 0, 0, 0);

-- PAGE: view entry
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_view_entry', '_bx_stories_page_title_sys_view_entry', '_bx_stories_page_title_view_entry', 'bx_stories', 12, 2147483647, 1, 'view-story', '', '', '', '', 0, 1, 0, 'BxStoriesPageEntry', 'modules/boonex/stories/classes/BxStoriesPageEntry.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES 
('bx_stories_view_entry', 2, 'bx_stories','' , '_bx_stories_page_block_title_entry_text', 13, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:17:"entity_text_block";}', 0, 0, 1, 1),
('bx_stories_view_entry', 2, 'bx_stories','' , '_bx_stories_page_block_title_entry_attachments', 0, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:18:"entity_attachments";}', 0, 0, 1, 3),
('bx_stories_view_entry', 4, 'bx_stories','' , '_bx_stories_page_block_title_entry_social_sharing', 13, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:21:"entity_social_sharing";}', 0, 0, 0, 0),
('bx_stories_view_entry', 2, 'bx_stories','' , '_bx_stories_page_block_title_entry_comments', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:15:"entity_comments";}', 0, 0, 1, 4),
('bx_stories_view_entry', 3, 'bx_stories','' , '_bx_stories_page_block_title_entry_location', 3, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:13:"locations_map";s:6:"params";a:2:{i:0;s:10:"bx_stories";i:1;s:4:"{id}";}s:5:"class";s:20:"TemplServiceMetatags";}', 0, 0, 1, 4),
('bx_stories_view_entry', 3, 'bx_stories','' , '_bx_stories_page_block_title_entry_author', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"entity_author";}', 0, 0, 1, 2),
('bx_stories_view_entry', 3, 'bx_stories', '_bx_stories_page_block_title_sys_entry_context', '_bx_stories_page_block_title_entry_context', 13, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"entity_context";}', 0, 0, 1, 1),
('bx_stories_view_entry', 3, 'bx_stories','' , '_bx_stories_page_block_title_entry_info', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:11:"entity_info";}', 0, 0, 1, 3),
('bx_stories_view_entry', 3, 'bx_stories', '', '_bx_stories_page_block_title_entry_actions', 13, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"entity_actions";}', 0, 0, 0, 0),
('bx_stories_view_entry', 2, 'bx_stories','' , '_bx_stories_page_block_title_entry_all_actions', 13, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:18:"entity_all_actions";}', 0, 0, 1, 2),
('bx_stories_view_entry', 2, 'bx_stories', '', '_bx_stories_page_block_title_entry_reports', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"entity_reports";}', 0, 0, 1, 6);

-- PAGE: view entry comments
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_view_entry_comments', '_bx_stories_page_title_sys_view_entry_comments', '_bx_stories_page_title_view_entry_comments', 'bx_stories', 5, 2147483647, 1, 'view-story-comments', '', '', '', '', 0, 1, 0, 'BxStoriesPageEntry', 'modules/boonex/stories/classes/BxStoriesPageEntry.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES 
('bx_stories_view_entry_comments', 1, 'bx_stories', '_bx_stories_page_block_title_entry_comments', '_bx_stories_page_block_title_entry_comments_link', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:15:"entity_comments";}', 0, 0, 1);

-- PAGE: popular stories
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_popular', '_bx_stories_page_title_sys_entries_popular', '_bx_stories_page_title_entries_popular', 'bx_stories', 5, 2147483647, 1, 'stories-popular', 'page.php?i=stories-popular', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES 
('bx_stories_popular', 1, 'bx_stories', '_bx_stories_page_block_title_popular_entries', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"browse_popular";s:6:"params";a:3:{s:9:"unit_view";s:7:"gallery";s:13:"empty_message";b:1;s:13:"ajax_paginate";b:0;}}', 0, 1, 1);

-- PAGE: top stories
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_top', '_bx_stories_page_title_sys_entries_top', '_bx_stories_page_title_entries_top', 'bx_stories', 5, 2147483647, 1, 'stories-top', 'page.php?i=stories-top', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES 
('bx_stories_top', 1, 'bx_stories', '_bx_stories_page_block_title_top_entries', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:10:"browse_top";s:6:"params";a:3:{s:9:"unit_view";s:7:"gallery";s:13:"empty_message";b:1;s:13:"ajax_paginate";b:0;}}', 0, 1, 1);

-- PAGE: entries of author
INSERT INTO `sys_objects_page`(`object`, `uri`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_author', 'stories-author', '_bx_stories_page_title_sys_entries_of_author', '_bx_stories_page_title_entries_of_author', 'bx_stories', 5, 2147483647, 1, '', '', '', '', 0, 1, 0, 'BxStoriesPageAuthor', 'modules/boonex/stories/classes/BxStoriesPageAuthor.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES 
('bx_stories_author', 1, 'bx_stories', '', '_bx_stories_page_block_title_entries_actions', 13, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:18:"my_entries_actions";}', 0, 0, 1, 1),
('bx_stories_author', 1, 'bx_stories', '_bx_stories_page_block_title_sys_entries_of_author', '_bx_stories_page_block_title_entries_of_author', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"browse_author";}', 0, 0, 1, 2),
('bx_stories_author', 1, 'bx_stories', '_bx_stories_page_block_title_sys_entries_in_context', '_bx_stories_page_block_title_entries_in_context', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"browse_context";s:6:"params";a:2:{s:10:"profile_id";s:12:"{profile_id}";i:0;a:1:{s:13:"empty_message";b:0;}}}', 0, 0, 1, 3);

-- PAGE: entries in context
INSERT INTO `sys_objects_page`(`object`, `uri`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_context', 'stories-context', '_bx_stories_page_title_sys_entries_in_context', '_bx_stories_page_title_entries_in_context', 'bx_stories', 5, 2147483647, 1, '', '', '', '', 0, 1, 0, 'BxStoriesPageAuthor', 'modules/boonex/stories/classes/BxStoriesPageAuthor.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES 
('bx_stories_context', 1, 'bx_stories', '_bx_stories_page_block_title_sys_entries_in_context', '_bx_stories_page_block_title_entries_in_context', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"browse_context";}', 0, 0, 1, 1);

-- PAGE: module home
INSERT INTO `sys_objects_page`(`object`, `uri`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_home', 'stories-home', '_bx_stories_page_title_sys_home', '_bx_stories_page_title_home', 'bx_stories', 5, 2147483647, 1, 'page.php?i=stories-home', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES
('bx_stories_home', 0, 'bx_stories', '', '_bx_stories_page_block_title_popular_keywords_stories', 11, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:14:"keywords_cloud";s:6:"params";a:3:{i:0;s:10:"bx_stories";i:1;s:10:"bx_stories";i:2;a:1:{s:10:"show_empty";b:1;}}s:5:"class";s:20:"TemplServiceMetatags";}', 1, 0, 1, 0),
('bx_stories_home', 1, 'bx_stories', '_bx_stories_page_block_title_sys_recent_entries_view_gallery', '_bx_stories_page_block_title_recent_entries', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"browse_public";s:6:"params";a:2:{s:9:"unit_view";s:7:"gallery";s:13:"empty_message";b:1;}}', 0, 1, 1, 1);

-- PAGE: search for entries
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_search', '_bx_stories_page_title_sys_entries_search', '_bx_stories_page_title_entries_search', 'bx_stories', 1, 2147483647, 1, 'stories-search', 'page.php?i=stories-search', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES 
('bx_stories_search', 0, 'bx_stories', '_bx_stories_page_block_title_search_form_cmts', 11, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:8:"get_form";s:6:"params";a:1:{i:0;a:1:{s:6:"object";s:15:"bx_stories_cmts";}}s:5:"class";s:27:"TemplSearchExtendedServices";}', 0, 1, 0, 0),
('bx_stories_search', 0, 'bx_stories', '_bx_stories_page_block_title_search_results_cmts', 11, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_results";s:6:"params";a:1:{i:0;a:2:{s:6:"object";s:15:"bx_stories_cmts";s:10:"show_empty";b:0;}}s:5:"class";s:27:"TemplSearchExtendedServices";}', 0, 1, 0, 0),
('bx_stories_search', 1, 'bx_stories', '_bx_stories_page_block_title_search_form', 11, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:8:"get_form";s:6:"params";a:1:{i:0;a:1:{s:6:"object";s:10:"bx_stories";}}s:5:"class";s:27:"TemplSearchExtendedServices";}', 0, 1, 1, 1),
('bx_stories_search', 2, 'bx_stories', '_bx_stories_page_block_title_search_results', 11, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_results";s:6:"params";a:1:{i:0;a:2:{s:6:"object";s:10:"bx_stories";s:10:"show_empty";b:1;}}s:5:"class";s:27:"TemplSearchExtendedServices";}', 0, 1, 1, 1);

-- PAGE: module manage own
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_manage', '_bx_stories_page_title_sys_manage', '_bx_stories_page_title_manage', 'bx_stories', 5, 2147483647, 1, 'stories-manage', 'page.php?i=stories-manage', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES 
('bx_stories_manage', 1, 'bx_stories', '_bx_stories_page_block_title_system_manage', '_bx_stories_page_block_title_manage', 11, 2147483647, 'service', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:12:"manage_tools";}}', 0, 1, 0);

-- PAGE: module manage all
INSERT INTO `sys_objects_page`(`object`, `title_system`, `title`, `module`, `layout_id`, `visible_for_levels`, `visible_for_levels_editable`, `uri`, `url`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `deletable`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_administration', '_bx_stories_page_title_sys_manage_administration', '_bx_stories_page_title_manage', 'bx_stories', 5, 192, 1, 'stories-administration', 'page.php?i=stories-administration', '', '', '', 0, 1, 0, 'BxStoriesPageBrowse', 'modules/boonex/stories/classes/BxStoriesPageBrowse.php');

INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES 
('bx_stories_administration', 1, 'bx_stories', '_bx_stories_page_block_title_system_manage_administration', '_bx_stories_page_block_title_manage', 11, 192, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:12:"manage_tools";s:6:"params";a:1:{i:0;s:14:"administration";}}', 0, 1, 0);

-- PAGE: add block to homepage
SET @iBlockOrder = (SELECT `order` FROM `sys_pages_blocks` WHERE `object` = 'sys_home' AND `cell_id` = 1 ORDER BY `order` DESC LIMIT 1);
INSERT INTO `sys_pages_blocks`(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES 
('sys_home', 1, 'bx_stories', '_bx_stories_page_block_title_sys_recent_entries_view_showcase', '_bx_stories_page_block_title_recent_entries', 13, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"browse_public";s:6:"params";a:3:{s:9:"unit_view";s:8:"showcase";s:13:"empty_message";b:0;s:13:"ajax_paginate";b:0;}}', 1, 0, 1, IFNULL(@iBlockOrder, 0) + 1);

-- TODO: Block should check Expiration.

-- PAGES: add page block to profiles modules (trigger* page objects are processed separately upon modules enable/disable)
SET @iPBCellProfile = 3;
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `order`) VALUES
('trigger_page_profile_view_entry', @iPBCellProfile, 'bx_stories', '_bx_stories_page_block_title_sys_my_entries', '_bx_stories_page_block_title_my_entries', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"browse_author";s:6:"params";a:2:{i:0;s:12:"{profile_id}";i:1;a:2:{s:8:"per_page";s:27:"bx_stories_per_page_profile";s:13:"empty_message";b:0;}}}', 0, 0, 0);

-- PAGE: service blocks
SET @iBlockOrder = (SELECT `order` FROM `sys_pages_blocks` WHERE `object` = '' AND `cell_id` = 0 ORDER BY `order` DESC LIMIT 1);
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `visible_for_levels`, `type`, `content`, `deletable`, `copyable`, `active`, `order`) VALUES
('', 0, 'bx_stories', '', '_bx_stories_page_block_title_popular_keywords_stories', 11, 2147483647, 'service', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:14:"keywords_cloud";s:6:"params";a:2:{i:0;s:10:"bx_stories";i:1;s:10:"bx_stories";}s:5:"class";s:20:"TemplServiceMetatags";}', 0, 1, 1, @iBlockOrder + 1),
('', 0, 'bx_stories', '', '_bx_stories_page_block_title_recent_entries', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:13:"browse_public";s:6:"params";a:3:{s:9:"unit_view";s:8:"extended";s:13:"empty_message";b:1;s:13:"ajax_paginate";b:0;}}', 0, 1, 1, @iBlockOrder + 2),
('', 0, 'bx_stories', '', '_bx_stories_page_block_title_updated_entries', 11, 2147483647, 'service', 'a:3:{s:6:"module";s:10:"bx_stories";s:6:"method";s:14:"browse_updated";s:6:"params";a:3:{s:9:"unit_view";s:8:"extended";s:13:"empty_message";b:1;s:13:"ajax_paginate";b:0;}}', 0, 1, 1, @iBlockOrder + 3);

-- MENU: add to site menu
SET @iSiteMenuOrder = (SELECT `order` FROM `sys_menu_items` WHERE `set_name` = 'sys_site' AND `active` = 1 AND `order` < 9999 ORDER BY `order` DESC LIMIT 1);
INSERT INTO `sys_menu_items` (`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('sys_site', 'bx_stories', 'stories-home', '_bx_stories_menu_item_title_system_entries_home', '_bx_stories_menu_item_title_entries_home', 'page.php?i=stories-home', '', '', 'far image col-blue1', 'bx_stories_submenu', 2147483647, 1, 1, IFNULL(@iSiteMenuOrder, 0) + 1);

-- MENU: add to homepage menu
SET @iHomepageMenuOrder = (SELECT `order` FROM `sys_menu_items` WHERE `set_name` = 'sys_homepage' AND `active` = 1 ORDER BY `order` DESC LIMIT 1);
INSERT INTO `sys_menu_items` (`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('sys_homepage', 'bx_stories', 'stories-home', '_bx_stories_menu_item_title_system_entries_home', '_bx_stories_menu_item_title_entries_home', 'page.php?i=stories-home', '', '', 'far image col-blue1', 'bx_stories_submenu', 2147483647, 1, 1, IFNULL(@iHomepageMenuOrder, 0) + 1);

-- MENU: add to "add content" menu
SET @iAddMenuOrder = (SELECT `order` FROM `sys_menu_items` WHERE `set_name` = 'sys_add_content_links' AND `active` = 1 ORDER BY `order` DESC LIMIT 1);
INSERT INTO `sys_menu_items` (`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('sys_add_content_links', 'bx_stories', 'create-story', '_bx_stories_menu_item_title_system_create_entry', '_bx_stories_menu_item_title_create_entry', 'page.php?i=create-story', '', '', 'far image col-blue1', '', 2147483647, 1, 1, IFNULL(@iAddMenuOrder, 0) + 1);

-- MENU: actions menu for view entry 
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_view', '_bx_stories_menu_title_view_entry', 'bx_stories_view', 'bx_stories', 9, 0, 1, 'BxStoriesMenuView', 'modules/boonex/stories/classes/BxStoriesMenuView.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_view', 'bx_stories', '_bx_stories_menu_set_title_view_entry', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('bx_stories_view', 'bx_stories', 'add-media-to-story', '_bx_stories_menu_item_title_system_add_media', '_bx_stories_menu_item_title_add_media', 'page.php?i=story-add-media&id={content_id}', '', '', 'plus', '', 2147483647, 1, 0, 10),
('bx_stories_view', 'bx_stories', 'edit-story', '_bx_stories_menu_item_title_system_edit_entry', '_bx_stories_menu_item_title_edit_entry', 'page.php?i=edit-story&id={content_id}', '', '', 'pencil-alt', '', 2147483647, 1, 0, 20),
('bx_stories_view', 'bx_stories', 'delete-story', '_bx_stories_menu_item_title_system_delete_entry', '_bx_stories_menu_item_title_delete_entry', 'page.php?i=delete-story&id={content_id}', '', '', 'remove', '', 2147483647, 1, 0, 30),
('bx_stories_view', 'bx_stories', 'approve', '_sys_menu_item_title_system_va_approve', '_sys_menu_item_title_va_approve', 'javascript:void(0)', 'javascript:bx_approve(this, ''{module_uri}'', {content_id});', '', 'check', '', 2147483647, 1, 0, 40);

-- MENU: all actions menu for view entry 
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_view_actions', '_sys_menu_title_view_actions', 'bx_stories_view_actions', 'bx_stories', 15, 0, 1, 'BxStoriesMenuViewActions', 'modules/boonex/stories/classes/BxStoriesMenuViewActions.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_view_actions', 'bx_stories', '_sys_menu_set_title_view_actions', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `submenu_object`, `submenu_popup`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('bx_stories_view_actions', 'bx_stories', 'edit-story', '_bx_stories_menu_item_title_system_edit_entry', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 10),
('bx_stories_view_actions', 'bx_stories', 'delete-story', '_bx_stories_menu_item_title_system_delete_entry', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 20),
('bx_stories_view_actions', 'bx_stories', 'approve', '_sys_menu_item_title_system_va_approve', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 30),
('bx_stories_view_actions', 'bx_stories', 'comment', '_sys_menu_item_title_system_va_comment', '', '', '', '', '', '', '', 0, 2147483647, 0, 0, 200),
('bx_stories_view_actions', 'bx_stories', 'view', '_sys_menu_item_title_system_va_view', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 210),
('bx_stories_view_actions', 'bx_stories', 'vote', '_sys_menu_item_title_system_va_vote', '', '', '', '', '', '', '', 0, 2147483647, 0, 0, 220),
('bx_stories_view_actions', 'bx_stories', 'reaction', '_sys_menu_item_title_system_va_reaction', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 225),
('bx_stories_view_actions', 'bx_stories', 'score', '_sys_menu_item_title_system_va_score', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 230),
('bx_stories_view_actions', 'bx_stories', 'favorite', '_sys_menu_item_title_system_va_favorite', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 240),
('bx_stories_view_actions', 'bx_stories', 'feature', '_sys_menu_item_title_system_va_feature', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 250),
('bx_stories_view_actions', 'bx_stories', 'repost', '_sys_menu_item_title_system_va_repost', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 260),
('bx_stories_view_actions', 'bx_stories', 'report', '_sys_menu_item_title_system_va_report', '', '', '', '', '', '', '', 0, 2147483647, 1, 0, 270),
('bx_stories_view_actions', 'bx_stories', 'notes', '_sys_menu_item_title_system_va_notes', '_sys_menu_item_title_va_notes', 'javascript:void(0)', 'javascript:bx_get_notes(this,  ''{module_uri}'', {content_id});', '', 'exclamation-triangle', '', '', 0, 2147483647, 1, 0, 280),
('bx_stories_view_actions', 'bx_stories', 'audit', '_sys_menu_item_title_system_va_audit', '_sys_menu_item_title_va_audit', 'page.php?i=dashboard-audit&module=bx_stories&content_id={content_id}', '', '', 'history', '', '', 0, 192, 1, 0, 290),
('bx_stories_view_actions', 'bx_stories', 'social-sharing', '_sys_menu_item_title_system_social_sharing', '_sys_menu_item_title_social_sharing', 'javascript:void(0)', 'oBxDolPage.share(this, \'{url_encoded}\')', '', 'share', '', '', 0, 2147483647, 1, 0, 300),
('bx_stories_view_actions', 'bx_stories', 'more-auto', '_sys_menu_item_title_system_va_more_auto', '_sys_menu_item_title_va_more_auto', 'javascript:void(0)', '', '', 'ellipsis-v', '', '', 0, 2147483647, 1, 0, 9999);


-- MENU: actions menu for view media
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_view_actions_media', '_bx_stories_menu_title_view_actions_media', 'bx_stories_view_actions_media', 'bx_stories', 9, 0, 1, 'BxStoriesMenuView', 'modules/boonex/stories/classes/BxStoriesMenuView.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_view_actions_media', 'bx_stories', '_bx_stories_menu_set_title_view_actions_media', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('bx_stories_view_actions_media', 'bx_stories', 'edit-media', '_bx_stories_menu_item_title_system_edit_image', '_bx_stories_menu_item_title_edit_image', 'javascript:void(0)', 'javascript:{js_object}.editMedia(this, {id});', '', 'pencil-alt', '', 2147483647, 1, 0, 10),
('bx_stories_view_actions_media', 'bx_stories', 'delete-media', '_bx_stories_menu_item_title_system_delete_image', '_bx_stories_menu_item_title_delete_image', 'javascript:void(0)', 'javascript:{js_object}.deleteMedia(this, {id});', '', 'remove', '', 2147483647, 1, 0, 20);


-- MENU: actions menu for my entries
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_my', '_bx_stories_menu_title_entries_my', 'bx_stories_my', 'bx_stories', 9, 0, 1, 'BxStoriesMenu', 'modules/boonex/stories/classes/BxStoriesMenu.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_my', 'bx_stories', '_bx_stories_menu_set_title_entries_my', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('bx_stories_my', 'bx_stories', 'create-story', '_bx_stories_menu_item_title_system_create_entry', '_bx_stories_menu_item_title_create_entry', 'page.php?i=create-story', '', '', 'plus', '', 2147483647, 1, 0, 0);

-- MENU: module sub-menu
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_submenu', '_bx_stories_menu_title_submenu', 'bx_stories_submenu', 'bx_stories', 8, 0, 1, '', '');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_submenu', 'bx_stories', '_bx_stories_menu_set_title_submenu', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('bx_stories_submenu', 'bx_stories', 'stories-home', '_bx_stories_menu_item_title_system_entries_recent', '_bx_stories_menu_item_title_entries_recent', 'page.php?i=stories-home', '', '', '', '', 2147483647, 1, 1, 1),
('bx_stories_submenu', 'bx_stories', 'stories-popular', '_bx_stories_menu_item_title_system_entries_popular', '_bx_stories_menu_item_title_entries_popular', 'page.php?i=stories-popular', '', '', '', '', 2147483647, 1, 1, 2),
('bx_stories_submenu', 'bx_stories', 'stories-top', '_bx_stories_menu_item_title_system_entries_top', '_bx_stories_menu_item_title_entries_top', 'page.php?i=stories-top', '', '', '', '', 2147483647, 1, 1, 3),
('bx_stories_submenu', 'bx_stories', 'stories-search', '_bx_stories_menu_item_title_system_entries_search', '_bx_stories_menu_item_title_entries_search', 'page.php?i=stories-search', '', '', '', '', 2147483647, 1, 1, 4),
('bx_stories_submenu', 'bx_stories', 'stories-manage', '_bx_stories_menu_item_title_system_entries_manage', '_bx_stories_menu_item_title_entries_manage', 'page.php?i=stories-manage', '', '', '', '', 2147483646, 1, 1, 5);

-- MENU: sub-menu for view entry
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_view_submenu', '_bx_stories_menu_title_view_entry_submenu', 'bx_stories_view_submenu', 'bx_stories', 8, 0, 1, 'BxStoriesMenuView', 'modules/boonex/stories/classes/BxStoriesMenuView.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_view_submenu', 'bx_stories', '_bx_stories_menu_set_title_view_entry_submenu', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('bx_stories_view_submenu', 'bx_stories', 'view-story', '_bx_stories_menu_item_title_system_view_entry', '_bx_stories_menu_item_title_view_entry_submenu_entry', 'page.php?i=view-story&id={content_id}', '', '', '', '', 2147483647, 1, 0, 1),
('bx_stories_view_submenu', 'bx_stories', 'view-story-comments', '_bx_stories_menu_item_title_system_view_entry_comments', '_bx_stories_menu_item_title_view_entry_submenu_comments', 'page.php?i=view-story-comments&id={content_id}', '', '', '', '', 2147483647, 1, 0, 2);

-- MENU: custom menu for snippet meta info
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_snippet_meta', '_sys_menu_title_snippet_meta', 'bx_stories_snippet_meta', 'bx_stories', 15, 0, 1, 'BxStoriesMenuSnippetMeta', 'modules/boonex/stories/classes/BxStoriesMenuSnippetMeta.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_snippet_meta', 'bx_stories', '_sys_menu_set_title_snippet_meta', 0);

INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `editable`, `order`) VALUES 
('bx_stories_snippet_meta', 'bx_stories', 'date', '_sys_menu_item_title_system_sm_date', '_sys_menu_item_title_sm_date', '', '', '', '', '', 2147483647, 1, 0, 1, 1),
('bx_stories_snippet_meta', 'bx_stories', 'author', '_sys_menu_item_title_system_sm_author', '_sys_menu_item_title_sm_author', '', '', '', '', '', 2147483647, 1, 0, 1, 2),
('bx_stories_snippet_meta', 'bx_stories', 'tags', '_sys_menu_item_title_system_sm_tags', '_sys_menu_item_title_sm_tags', '', '', '', '', '', 2147483647, 0, 0, 1, 3),
('bx_stories_snippet_meta', 'bx_stories', 'views', '_sys_menu_item_title_system_sm_views', '_sys_menu_item_title_sm_views', '', '', '', '', '', 2147483647, 0, 0, 1, 4),
('bx_stories_snippet_meta', 'bx_stories', 'comments', '_sys_menu_item_title_system_sm_comments', '_sys_menu_item_title_sm_comments', '', '', '', '', '', 2147483647, 0, 0, 1, 5),
('bx_stories_snippet_meta', 'bx_stories', 'items', '_bx_stories_menu_item_title_system_sm_items', '_bx_stories_menu_item_title_sm_items', '', '', '', '', '', 2147483647, 0, 0, 1, 6);

-- MENU: profile stats
SET @iNotifMenuOrder = (SELECT IFNULL(MAX(`order`), 0) FROM `sys_menu_items` WHERE `set_name` = 'sys_profile_stats' AND `active` = 1 LIMIT 1);
INSERT INTO `sys_menu_items` (`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES
('sys_profile_stats', 'bx_stories', 'profile-stats-my-stories', '_bx_stories_menu_item_title_system_manage_my_stories', '_bx_stories_menu_item_title_manage_my_stories', 'page.php?i=stories-author&profile_id={member_id}', '', '_self', 'far image col-blue1', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:41:"get_menu_addon_manage_tools_profile_stats";}', '', 2147483646, 1, 0, @iNotifMenuOrder + 1);

-- MENU: manage tools submenu
INSERT INTO `sys_objects_menu`(`object`, `title`, `set_name`, `module`, `template_id`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES 
('bx_stories_menu_manage_tools', '_bx_stories_menu_title_manage_tools', 'bx_stories_menu_manage_tools', 'bx_stories', 6, 0, 1, 'BxStoriesMenuManageTools', 'modules/boonex/stories/classes/BxStoriesMenuManageTools.php');

INSERT INTO `sys_menu_sets`(`set_name`, `module`, `title`, `deletable`) VALUES 
('bx_stories_menu_manage_tools', 'bx_stories', '_bx_stories_menu_set_title_manage_tools', 0);

--INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
--('bx_stories_menu_manage_tools', 'bx_stories', 'delete-with-content', '_bx_stories_menu_item_title_system_delete_with_content', '_bx_stories_menu_item_title_delete_with_content', 'javascript:void(0)', 'javascript:{js_object}.onClickDeleteWithContent({content_id});', '_self', 'far trash-alt', '', 128, 1, 0, 0);

-- MENU: dashboard manage tools
SET @iManageMenuOrder = (SELECT IFNULL(MAX(`order`), 0) FROM `sys_menu_items` WHERE `set_name`='sys_account_dashboard_manage_tools' LIMIT 1);
INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('sys_account_dashboard_manage_tools', 'bx_stories', 'stories-administration', '_bx_stories_menu_item_title_system_admt_stories', '_bx_stories_menu_item_title_admt_stories', 'page.php?i=stories-administration', '', '_self', 'far image', 'a:2:{s:6:"module";s:10:"bx_stories";s:6:"method";s:27:"get_menu_addon_manage_tools";}', '', 192, 1, 0, @iManageMenuOrder + 1);

-- MENU: add menu item to profiles modules (trigger* menu sets are processed separately upon modules enable/disable)
INSERT INTO `sys_menu_items`(`set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `submenu_object`, `visible_for_levels`, `active`, `copyable`, `order`) VALUES 
('trigger_profile_view_submenu', 'bx_stories', 'stories-author', '_bx_stories_menu_item_title_system_view_entries_author', '_bx_stories_menu_item_title_view_entries_author', 'page.php?i=stories-author&profile_id={profile_id}', '', '', 'far image col-blue1', '', 2147483647, 1, 0, 0),
('trigger_group_view_submenu', 'bx_stories', 'stories-context', '_bx_stories_menu_item_title_system_view_entries_in_context', '_bx_stories_menu_item_title_view_entries_in_context', 'page.php?i=stories-context&profile_id={profile_id}', '', '', 'far image col-blue1', '', 2147483647, 1, 0, 0);


-- PRIVACY 
INSERT INTO `sys_objects_privacy` (`object`, `module`, `action`, `title`, `default_group`, `table`, `table_field_id`, `table_field_author`, `override_class_name`, `override_class_file`) VALUES
('bx_stories_allow_view_to', 'bx_stories', 'view', '_bx_stories_form_entry_input_allow_view_to', '3', 'bx_stories_entries', 'id', 'author', '', '');


-- ACL
INSERT INTO `sys_acl_actions` (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`) VALUES
('bx_stories', 'create entry', NULL, '_bx_stories_acl_action_create_entry', '', 1, 3);
SET @iIdActionEntryCreate = LAST_INSERT_ID();

INSERT INTO `sys_acl_actions` (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`) VALUES
('bx_stories', 'delete entry', NULL, '_bx_stories_acl_action_delete_entry', '', 1, 3);
SET @iIdActionEntryDelete = LAST_INSERT_ID();

INSERT INTO `sys_acl_actions` (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`) VALUES
('bx_stories', 'view entry', NULL, '_bx_stories_acl_action_view_entry', '', 1, 0);
SET @iIdActionEntryView = LAST_INSERT_ID();

INSERT INTO `sys_acl_actions` (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`) VALUES
('bx_stories', 'edit any entry', NULL, '_bx_stories_acl_action_edit_any_entry', '', 1, 3);
SET @iIdActionEntryEditAny = LAST_INSERT_ID();

INSERT INTO `sys_acl_actions` (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`) VALUES
('bx_stories', 'delete any entry', NULL, '_bx_stories_acl_action_delete_any_entry', '', 1, 3);
SET @iIdActionEntryDeleteAny = LAST_INSERT_ID();

SET @iUnauthenticated = 1;
SET @iAccount = 2;
SET @iStandard = 3;
SET @iUnconfirmed = 4;
SET @iPending = 5;
SET @iSuspended = 6;
SET @iModerator = 7;
SET @iAdministrator = 8;
SET @iPremium = 9;

INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES

-- entry create
(@iStandard, @iIdActionEntryCreate),
(@iModerator, @iIdActionEntryCreate),
(@iAdministrator, @iIdActionEntryCreate),
(@iPremium, @iIdActionEntryCreate),

-- entry delete
(@iStandard, @iIdActionEntryDelete),
(@iModerator, @iIdActionEntryDelete),
(@iAdministrator, @iIdActionEntryDelete),
(@iPremium, @iIdActionEntryDelete),

-- entry view
(@iUnauthenticated, @iIdActionEntryView),
(@iAccount, @iIdActionEntryView),
(@iStandard, @iIdActionEntryView),
(@iUnconfirmed, @iIdActionEntryView),
(@iPending, @iIdActionEntryView),
(@iModerator, @iIdActionEntryView),
(@iAdministrator, @iIdActionEntryView),
(@iPremium, @iIdActionEntryView),

-- edit any entry
(@iModerator, @iIdActionEntryEditAny),
(@iAdministrator, @iIdActionEntryEditAny),

-- delete any entry
(@iAdministrator, @iIdActionEntryDeleteAny);


-- SEARCH
SET @iSearchOrder = (SELECT IFNULL(MAX(`Order`), 0) FROM `sys_objects_search`);
INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `Order`, `GlobalSearch`, `ClassName`, `ClassPath`) VALUES
('bx_stories', '_bx_stories', @iSearchOrder + 1, 1, 'BxStoriesSearchResult', 'modules/boonex/stories/classes/BxStoriesSearchResult.php'),
('bx_stories_cmts', '_bx_stories_cmts', @iSearchOrder + 2, 1, 'BxStoriesCmtsSearchResult', 'modules/boonex/stories/classes/BxStoriesCmtsSearchResult.php'),
('bx_stories_media', '_bx_stories_media', @iSearchOrder + 3, 0, 'BxStoriesSearchResultMedia', 'modules/boonex/stories/classes/BxStoriesSearchResultMedia.php');


-- METATAGS
INSERT INTO `sys_objects_metatags` (`object`, `module`, `table_keywords`, `table_locations`, `table_mentions`, `override_class_name`, `override_class_file`) VALUES
('bx_stories', 'bx_stories', 'bx_stories_meta_keywords', '', 'bx_stories_meta_mentions', '', '');


-- STATS
SET @iMaxOrderStats = (SELECT IFNULL(MAX(`order`), 0) FROM `sys_statistics`);
INSERT INTO `sys_statistics` (`module`, `name`, `title`, `link`, `icon`, `query`, `order`) VALUES 
('bx_stories', 'bx_stories', '_bx_stories', 'page.php?i=stories-home', 'far image col-blue1', 'SELECT COUNT(*) FROM `bx_stories_entries` WHERE 1 AND `status` = ''active'' AND `status_admin` = ''active''', @iMaxOrderStats + 1);


-- CHARTS
SET @iMaxOrderCharts = (SELECT IFNULL(MAX(`order`), 0) FROM `sys_objects_chart`);
INSERT INTO `sys_objects_chart` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `field_status`, `query`, `active`, `order`, `class_name`, `class_file`) VALUES
('bx_stories_growth', '_bx_stories_chart_growth', 'bx_stories_entries', 'added', '', 'status,status_admin', '', 1, @iMaxOrderCharts + 1, 'BxDolChartGrowth', ''),
('bx_stories_growth_speed', '_bx_stories_chart_growth_speed', 'bx_stories_entries', 'added', '', 'status,status_admin', '', 1, @iMaxOrderCharts + 2, 'BxDolChartGrowthSpeed', '');


-- GRIDS: moderation tools
INSERT INTO `sys_objects_grid` (`object`, `source_type`, `source`, `table`, `field_id`, `field_order`, `field_active`, `paginate_url`, `paginate_per_page`, `paginate_simple`, `paginate_get_start`, `paginate_get_per_page`, `filter_fields`, `filter_fields_translatable`, `filter_mode`, `sorting_fields`, `sorting_fields_translatable`, `visible_for_levels`, `override_class_name`, `override_class_file`) VALUES
('bx_stories_administration', 'Sql', 'SELECT * FROM `bx_stories_entries` WHERE 1 ', 'bx_stories_entries', 'id', 'added', 'status_admin', '', 20, NULL, 'start', '', 'title,text', '', 'like', 'reports', '', 192, 'BxStoriesGridAdministration', 'modules/boonex/stories/classes/BxStoriesGridAdministration.php'),
('bx_stories_common', 'Sql', 'SELECT * FROM `bx_stories_entries` WHERE 1 ', 'bx_stories_entries', 'id', 'added', 'status', '', 20, NULL, 'start', '', 'title,text', '', 'like', '', '', 2147483647, 'BxStoriesGridCommon', 'modules/boonex/stories/classes/BxStoriesGridCommon.php');

INSERT INTO `sys_grid_fields` (`object`, `name`, `title`, `width`, `translatable`, `chars_limit`, `params`, `order`) VALUES
('bx_stories_administration', 'checkbox', '_sys_select', '2%', 0, '', '', 1),
('bx_stories_administration', 'switcher', '_bx_stories_grid_column_title_adm_active', '8%', 0, '', '', 2),
('bx_stories_administration', 'reports', '_sys_txt_reports_title', '5%', 0, '', '', 3),
('bx_stories_administration', 'title', '_bx_stories_grid_column_title_adm_title', '25%', 0, '', '', 4),
('bx_stories_administration', 'added', '_bx_stories_grid_column_title_adm_added', '20%', 1, '25', '', 5),
('bx_stories_administration', 'author', '_bx_stories_grid_column_title_adm_author', '20%', 0, '25', '', 6),
('bx_stories_administration', 'actions', '', '20%', 0, '', '', 7),

('bx_stories_common', 'checkbox', '_sys_select', '2%', 0, '', '', 1),
('bx_stories_common', 'switcher', '', '8%', 0, '', '', 2),
('bx_stories_common', 'title', '_bx_stories_grid_column_title_adm_title', '40%', 0, '', '', 3),
('bx_stories_common', 'added', '_bx_stories_grid_column_title_adm_added', '15%', 1, '25', '', 4),
('bx_stories_common', 'status_admin', '_bx_stories_grid_column_title_adm_status_admin', '15%', 0, '16', '', 5),
('bx_stories_common', 'actions', '', '20%', 0, '', '', 6);

INSERT INTO `sys_grid_actions` (`object`, `type`, `name`, `title`, `icon`, `icon_only`, `confirm`, `order`) VALUES
('bx_stories_administration', 'bulk', 'delete', '_bx_stories_grid_action_title_adm_delete', '', 0, 1, 1),
('bx_stories_administration', 'bulk', 'clear_reports', '_bx_stories_grid_action_title_adm_clear_reports', '', 0, 1, 2),
('bx_stories_administration', 'single', 'edit', '_bx_stories_grid_action_title_adm_edit', 'pencil-alt', 1, 0, 1),
('bx_stories_administration', 'single', 'delete', '_bx_stories_grid_action_title_adm_delete', 'remove', 1, 1, 2),
('bx_stories_administration', 'single', 'settings', '_bx_stories_grid_action_title_adm_more_actions', 'cog', 1, 0, 3),
('bx_stories_administration', 'single', 'audit_content', '_bx_stories_grid_action_title_adm_audit_content', 'search', 1, 0, 4),
('bx_stories_administration', 'single', 'clear_reports', '_bx_stories_grid_action_title_adm_clear_reports', 'eraser', 1, 0, 5),
('bx_stories_common', 'bulk', 'delete', '_bx_stories_grid_action_title_adm_delete', '', 0, 1, 1),
('bx_stories_common', 'single', 'edit', '_bx_stories_grid_action_title_adm_edit', 'pencil-alt', 1, 0, 1),
('bx_stories_common', 'single', 'delete', '_bx_stories_grid_action_title_adm_delete', 'remove', 1, 1, 2),
('bx_stories_common', 'single', 'settings', '_bx_stories_grid_action_title_adm_more_actions', 'cog', 1, 0, 3);


-- UPLOADERS
INSERT INTO `sys_objects_uploader` (`object`, `active`, `override_class_name`, `override_class_file`) VALUES
('bx_stories_html5', 1, 'BxStoriesUploaderHTML5', 'modules/boonex/stories/classes/BxStoriesUploaderHTML5.php'),
('bx_stories_record_video', 1, 'BxStoriesUploaderRecordVideo', 'modules/boonex/stories/classes/BxStoriesUploaderRecordVideo.php'),
('bx_stories_crop', 1, 'BxStoriesUploaderCrop', 'modules/boonex/stories/classes/BxStoriesUploaderCrop.php');


-- ALERTS
INSERT INTO `sys_alerts_handlers` (`name`, `class`, `file`, `service_call`) VALUES 
('bx_stories', 'BxStoriesAlertsResponse', 'modules/boonex/stories/classes/BxStoriesAlertsResponse.php', '');
SET @iHandler := LAST_INSERT_ID();

INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('system', 'save_setting', @iHandler),
('profile', 'delete', @iHandler),

('bx_stories_files', 'file_deleted', @iHandler),
('bx_stories_video_mp4', 'transcoded', @iHandler);
