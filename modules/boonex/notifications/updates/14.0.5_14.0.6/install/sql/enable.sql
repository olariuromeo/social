SET @sName = 'bx_notifications';

-- SETTINGS
SET @iCategId = (SELECT `id` FROM `sys_options_categories` WHERE `name`=@sName LIMIT 1);
DELETE FROM `sys_options` WHERE `name`='bx_notifications_enable_reply_as_comment';
INSERT INTO `sys_options` (`name`, `value`, `category_id`, `caption`, `type`, `check`, `check_params`, `check_error`, `extra`, `order`) VALUES
('bx_notifications_enable_reply_as_comment', 'on', @iCategId, '_bx_ntfs_option_enable_reply_as_comment', 'checkbox', '', '', '', '', 42);
