<?php
/**
 * Main plugin file
 */

// load helper functions
require_once(dirname(__FILE__) . '/lib/functions.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'user_support_init');

/**
 * Gets called during system init
 *
 * @return void
 */
function user_support_init() {
	// extend css
	elgg_extend_view('elgg.css', 'css/user_support/site.css');
	
	elgg_extend_view('elgg.js', 'js/user_support/site.js');
	
	elgg_extend_view('page/elements/footer', 'user_support/button');
	
	elgg_extend_view('forms/comment/save', 'user_support/support_ticket/comment');
	
	// register page handler for nice URL's
	elgg_register_page_handler('user_support', '\ColdTrick\UserSupport\PageHandler::userSupport');
	
	// register subtypes for search
	elgg_register_entity_type('object', UserSupportFAQ::SUBTYPE);
	elgg_register_entity_type('object', UserSupportHelp::SUBTYPE);
	elgg_register_entity_type('object', UserSupportTicket::SUBTYPE);
	
	// register notifications
	elgg_register_notification_event('object', 'comment');
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\UserSupport\Notifications::getSupportTicketCommentSubscribers');
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:comment', '\ColdTrick\UserSupport\Notifications::prepareSupportTicketCommentMessage');
	
	elgg_register_notification_event('object', UserSupportTicket::SUBTYPE);
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\UserSupport\Notifications::getSupportTicketSubscribers');
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:' . UserSupportTicket::SUBTYPE, '\ColdTrick\UserSupport\Notifications::prepareSupportTicketMessage');
	
	elgg_register_plugin_hook_handler('enqueue', 'notification', '\ColdTrick\UserSupport\Notifications::allowTicketEnqueue', 9999);
	elgg_register_plugin_hook_handler('enqueue', 'notification', '\ColdTrick\UserSupport\Notifications::allowTicketCommentEnqueue', 9999);
	
	// add a group tool option for FAQ
	$group_faq = user_support_get_group_faq_setting();
	if ($group_faq !== 'no') {
		add_group_tool_option('faq', elgg_echo('user_support:group:tool_option'), $group_faq === 'yes');
		elgg_extend_view('groups/tool_latest', 'user_support/faq/group_module');
	}
	
	// register events
	elgg_register_event_handler('create', 'object', '\ColdTrick\UserSupport\Comments::supportTicketStatus');
	
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\UserSupport\Upgrade::setFAQClass');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\UserSupport\Upgrade::setHelpClass');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\UserSupport\Upgrade::setTicketClass');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\UserSupport\Upgrade::registerSupportTicketAccessUpgrade');
	
	// plugin hooks
	elgg_register_plugin_hook_handler('handlers', 'widgets', '\ColdTrick\UserSupport\Widgets::registerFAQ');
	elgg_register_plugin_hook_handler('handlers', 'widgets', '\ColdTrick\UserSupport\Widgets::registerSupportTicket');
	elgg_register_plugin_hook_handler('handlers', 'widgets', '\ColdTrick\UserSupport\Widgets::registerSupportStaff');
	
	elgg_register_plugin_hook_handler('likes:is_likable', 'object:' . UserSupportFAQ::SUBTYPE, '\Elgg\Values::getTrue');
	
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\UserSupport\Menus\Entity::registerTicket');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\UserSupport\Menus\Entity::cleanupTicket', 9999);
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\UserSupport\Menus\Entity::registerHelp');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\UserSupport\Menus\Entity::promoteCommentToFAQ');
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', '\ColdTrick\UserSupport\Menus\OwnerBlock::registerUserSupportTickets');
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', '\ColdTrick\UserSupport\Menus\OwnerBlock::registerGroupFAQ');
	elgg_register_plugin_hook_handler('register', 'menu:title', '\ColdTrick\UserSupport\Menus\Title::registerFAQ');
	elgg_register_plugin_hook_handler('register', 'menu:title', '\ColdTrick\UserSupport\Menus\Title::registerSupportTicket');
	elgg_register_plugin_hook_handler('register', 'menu:site', '\ColdTrick\UserSupport\Menus\Site::registerFAQ');
	elgg_register_plugin_hook_handler('register', 'menu:site', '\ColdTrick\UserSupport\Menus\Site::registerHelpCenter');
	elgg_register_plugin_hook_handler('register', 'menu:site', '\ColdTrick\UserSupport\Menus\Site::registerUserSupportTickets');
	elgg_register_plugin_hook_handler('register', 'menu:page', '\ColdTrick\UserSupport\Menus\Page::registerFAQ');
	elgg_register_plugin_hook_handler('register', 'menu:page', '\ColdTrick\UserSupport\Menus\Page::registerUserSupportTickets');
	elgg_register_plugin_hook_handler('register', 'menu:footer', '\ColdTrick\UserSupport\Menus\Footer::registerFAQ');
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', '\ColdTrick\UserSupport\Menus\UserHover::registerStaff');
	elgg_register_plugin_hook_handler('register', 'menu:user_support', '\ColdTrick\UserSupport\Menus\UserSupport::registerUserSupportTickets');
	elgg_register_plugin_hook_handler('register', 'menu:user_support', '\ColdTrick\UserSupport\Menus\UserSupport::registerStaff');
	
	elgg_register_plugin_hook_handler('entity:url', 'object', '\ColdTrick\UserSupport\WidgetManager::widgetURL');
	elgg_register_plugin_hook_handler('reshare', 'object', '\ColdTrick\UserSupport\TheWireTools::blockHelpReshare');
	elgg_register_plugin_hook_handler('reshare', 'object', '\ColdTrick\UserSupport\TheWireTools::blockTicketReshare');
	elgg_register_plugin_hook_handler('type_subtypes', 'quicklinks', '\ColdTrick\UserSupport\QuickLinks::blockHelpLink');
	elgg_register_plugin_hook_handler('type_subtypes', 'quicklinks', '\ColdTrick\UserSupport\QuickLinks::blockTicketLink');
	
	// permissions
	elgg_register_plugin_hook_handler('container_logic_check', 'object', '\ColdTrick\UserSupport\Permissions::faqLogicCheck');
	elgg_register_plugin_hook_handler('container_permissions_check', 'object', '\ColdTrick\UserSupport\Permissions::faqContainerWriteCheck');
	
	elgg_register_plugin_hook_handler('permissions_check', 'object', '\ColdTrick\UserSupport\Permissions::editSupportTicket');
	
	elgg_register_plugin_hook_handler('permissions_check:delete', 'object', '\ColdTrick\UserSupport\Permissions::deleteSupportTicket');
	elgg_register_plugin_hook_handler('permissions_check:delete', 'object', '\ColdTrick\UserSupport\Permissions::deleteHelp');
	elgg_register_plugin_hook_handler('permissions_check:delete', 'object', '\ColdTrick\UserSupport\Permissions::deleteFAQ');
	
	// register actions
	elgg_register_action('user_support/help/edit', dirname(__FILE__) . '/actions/help/edit.php', 'admin');
	elgg_register_action('user_support/help/delete', dirname(__FILE__) . '/actions/help/delete.php', 'admin');
	elgg_register_action('user_support/upgrades/support_ticket_access', dirname(__FILE__) . '/actions/upgrades/support_ticket_access.php', 'admin');
	
	elgg_register_action('user_support/support_ticket/edit', dirname(__FILE__) . '/actions/ticket/edit.php');
	elgg_register_action('user_support/support_ticket/delete', dirname(__FILE__) . '/actions/ticket/delete.php');
	elgg_register_action('user_support/support_ticket/close', dirname(__FILE__) . '/actions/ticket/close.php');
	elgg_register_action('user_support/support_ticket/reopen', dirname(__FILE__) . '/actions/ticket/reopen.php');
	
	elgg_register_action('user_support/faq/edit', dirname(__FILE__) . '/actions/faq/edit.php');
	elgg_register_action('user_support/faq/delete', dirname(__FILE__) . '/actions/faq/delete.php');
	
	elgg_register_action('user_support/support_staff', dirname(__FILE__) . '/actions/support_staff.php', 'admin');
}
