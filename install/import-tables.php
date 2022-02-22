<?php

return array(
	'ads' => "
		CREATE TABLE `" . DB_PREFIX . "ads` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `unique_name` varchar(32) NOT NULL,
		  `ad_content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		  `ad_activ` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `unique_name` (`unique_name`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'cached' => "
		CREATE TABLE `" . DB_PREFIX . "cached` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `cache_hash` int(10) NOT NULL,
		  `cache_stored` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `cache_data` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		  `cache_type` varchar(10) NOT NULL DEFAULT 'files' COMMENT 'social, files, options, upload',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `cache_hash` (`cache_hash`,`cache_type`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'contests' => "
		CREATE TABLE `" . DB_PREFIX . "contests` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `contest_title` text NOT NULL,
		  `contest_description` text NOT NULL,
		  `contest_activ` tinyint(1) NOT NULL DEFAULT '0',
		  `contest_expire` datetime NOT NULL,
		  `contest_winner` varchar(300) NOT NULL DEFAULT 'Nie wylosowano',
		  `contest_winner_id` int(10) NOT NULL DEFAULT '0',
		  `contest_start` datetime NOT NULL,
		  `contest_type` varchar(20) NOT NULL,
		  `contest_status` tinyint(1) NOT NULL DEFAULT '1',
		  `contest_thumb` varchar(100) NOT NULL DEFAULT '0',
		  `contest_category_id` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'contests_captions' => "
		CREATE TABLE `" . DB_PREFIX . "contests_captions` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `caption` varchar(255) NOT NULL,
		  `caption_title` varchar(255) NOT NULL,
		  `caption_author` varchar(255) NOT NULL,
		  `caption_opinion` int(10) NOT NULL,
		  `caption_votes` int(10) NOT NULL,
		  `contest_id` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'contests_users' => "
		CREATE TABLE `" . DB_PREFIX . "contests_users` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `contest_user_id` int(10) NOT NULL,
		  `contest_id` int(10) NOT NULL,
		  `remain_votes_up` tinyint(2) NOT NULL DEFAULT '0',
		  `remain_votes_down` tinyint(2) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'contests_voting' => "
		CREATE TABLE `" . DB_PREFIX . "contests_voting` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `contest_id` int(10) NOT NULL,
		  `contest_user_id` int(10) NOT NULL,
		  `caption_id` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'fanpage_posts' => "
		CREATE TABLE `" . DB_PREFIX . "fanpage_posts` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `album_id` varchar(100) NOT NULL DEFAULT '0',
		  `upload_id` int(10) NOT NULL DEFAULT '0',
		  `fanpage_id` varchar(64) NOT NULL,
		  `post_id` varchar(100) NOT NULL,
		  `post_title` text NOT NULL,
		  `post_data` datetime NOT NULL,
		  `post_url` varchar(512) NOT NULL,
		  `post_type` varchar(10) NOT NULL DEFAULT 'post',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'history' => "
		CREATE TABLE `" . DB_PREFIX . "history` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `date_add` datetime NOT NULL,
		  `action` varchar(255) NOT NULL,
		  `user_id` int(10) NOT NULL,
		  `object_id` int(10) NOT NULL DEFAULT '0',
		  `object_name` varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `one_action_per_file` (`action`,`object_id`,`user_id`),
		  KEY `action` (`action`),
		  KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'mailing_service' => "
		CREATE TABLE `" . DB_PREFIX . "mailing_service` (
		  `mailing_id` int(10) NOT NULL AUTO_INCREMENT,
		  `subject` text COLLATE utf8_unicode_ci NOT NULL,
		  `content` text COLLATE utf8_unicode_ci NOT NULL,
		  `footer` text COLLATE utf8_unicode_ci NOT NULL,
		  `only_adult` tinyint(1) NOT NULL,
		  `activ_status` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
		  `user_language` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'all',
		  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
		  `users_send` int(10) NOT NULL DEFAULT '0',
		  `users_not_send` int(10) NOT NULL DEFAULT '0',
		  `page_num` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'mem_generator' => "
		CREATE TABLE `" . DB_PREFIX . "mem_generator` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `mem_title` varchar(128) NOT NULL,
		  `mem_title_dupiaty` varchar(128) NOT NULL DEFAULT '',
		  `mem_title_fr` varchar(128) NOT NULL DEFAULT '',
		  `mem_title_en` varchar(128) NOT NULL DEFAULT '',
		  `mem_title_pl` varchar(128) NOT NULL DEFAULT '',
		  `mem_image` varchar(128) NOT NULL,
		  `mem_category` int(10) NOT NULL,
		  `mem_date_add` datetime NOT NULL,
		  `mem_activ` tinyint(1) NOT NULL DEFAULT '1',
		  `mem_generated` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'mem_generator_categories' => "
		CREATE TABLE `" . DB_PREFIX . "mem_generator_categories` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `category_text` text NOT NULL,
		  `category_description` text NOT NULL,
		  `rewrite_text` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'menus' => "
		CREATE TABLE `" . DB_PREFIX . "menus` (
		  `menu_id` varchar(50) NOT NULL,
		  `item_id` varchar(50) NOT NULL,
		  `item_anchor` varchar(255) NOT NULL,
		  `item_title` varchar(255) NOT NULL,
		  `item_class` varchar(50) NOT NULL,
		  `item_url` varchar(255) NOT NULL,
		  `item_target` varchar(10) NOT NULL,
		  `item_activ` int(1) NOT NULL,
		  `item_position` int(2) NOT NULL,
		  UNIQUE KEY `menu_index` (`item_id`,`menu_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'pinit_boards' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_boards` (
		  `board_id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `category_id` bigint(20) NOT NULL,
		  `user_id` bigint(20) NOT NULL,
		  `board_cover` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `date_add` datetime NOT NULL,
		  `date_modified` datetime NOT NULL,
		  `board_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `board_description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
		  `board_followers` int(20) NOT NULL DEFAULT '0',
		  `board_pins` int(20) NOT NULL DEFAULT '0',
		  `board_privacy` enum('public','private') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
		  `latest_pins` varchar(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
		  `board_views` int(20) NOT NULL DEFAULT '0',
		  `board_has_map` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`board_id`),
		  KEY `category_id` (`category_id`),
		  KEY `user_id` (`user_id`),
		  KEY `board_visible` (`board_privacy`),
		  KEY `board_views` (`board_views`),
		  FULLTEXT KEY `title` (`board_title`,`board_description`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'pinit_pins' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_pins` (
		  `id` int(20) NOT NULL AUTO_INCREMENT,
		  `category_id` int(20) NOT NULL,
		  `board_id` int(20) NOT NULL,
		  `user_id` int(20) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `date_modified` datetime NOT NULL,
		  `pin_likes` int(20) NOT NULL DEFAULT '0',
		  `comments` int(20) NOT NULL DEFAULT '0',
		  `comments_facebook` smallint(4) NOT NULL DEFAULT '0',
		  `pin_repins` int(20) NOT NULL DEFAULT '0',
		  `pin_description` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
		  `pin_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `upload_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `pin_source` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `pin_source_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `pin_has_place` tinyint(1) NOT NULL DEFAULT '0',
		  `repin_from` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
		  `upload_views` int(20) NOT NULL DEFAULT '1',
		  `pin_privacy` enum('public','private') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
		  `autopost` tinyint(1) NOT NULL DEFAULT '0',
		  `pin_featured` tinyint(1) NOT NULL DEFAULT '0',
		  `seo_link` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `upload_sizes` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `pin_type` enum('image','gif','video','gallery') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'image',
		  `pin_color` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FFFFFF',
		  `video_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`),
		  KEY `category_id` (`category_id`),
		  KEY `user_id` (`user_id`),
		  KEY `board_id` (`board_id`),
		  KEY `pin_visible` (`pin_privacy`),
		  KEY `user_id_visible` (`user_id`,`pin_privacy`),
		  KEY `pinned_source_hash` (`pin_source_hash`),
		  KEY `filter_likes_comments_repins` (`pin_likes`,`comments`,`pin_repins`),
		  KEY `pin_views` (`upload_views`),
		  FULLTEXT KEY `pin_description` (`pin_description`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'pinit_pins_likes' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_pins_likes` (
		  `like_id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `pin_id` bigint(20) NOT NULL,
		  `user_id` bigint(20) NOT NULL,
		  PRIMARY KEY (`like_id`),
		  KEY `pin_id` (`pin_id`,`user_id`),
		  KEY `pin_id_self` (`pin_id`),
		  KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'pinit_places' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_places` (
		  `p_id` int(20) NOT NULL AUTO_INCREMENT,
		  `place_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
		  `place_longitude` float NOT NULL,
		  `place_latitude` float NOT NULL,
		  `place_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `place_data` text COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`p_id`),
		  UNIQUE KEY `place_id` (`place_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='GoogleMaps/Foursquare places';
	",
	'pinit_places_pins' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_places_pins` (
		  `p_id` int(20) NOT NULL,
		  `pin_id` int(20) NOT NULL,
		  `board_id` int(20) NOT NULL,
		  UNIQUE KEY `p_pin` (`p_id`,`pin_id`),
		  KEY `board_id` (`board_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Store relation place/pin/board';
	",
	'pinit_users_boards' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_users_boards` (
		  `id` int(20) NOT NULL AUTO_INCREMENT,
		  `user_id` int(20) NOT NULL,
		  `board_id` int(20) NOT NULL,
		  `is_author` tinyint(1) NOT NULL DEFAULT '0',
		  `allow_pin` tinyint(1) NOT NULL DEFAULT '1',
		  `invited_by` int(20) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `user_id` (`user_id`,`board_id`),
		  KEY `is_author` (`is_author`,`allow_pin`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'pinit_users_follow_board' => "
		CREATE TABLE `" . DB_PREFIX . "pinit_users_follow_board` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `user_id` int(20) NOT NULL,
		  `board_id` int(20) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `prevent_duplicate_rows` (`user_id`,`board_id`),
		  KEY `user_id` (`user_id`,`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'posts' => "
		CREATE TABLE `" . DB_PREFIX . "posts` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `post_uid` varchar(32) NOT NULL DEFAULT '',
		  `post_author` varchar(255) NOT NULL,
		  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `post_content` longtext NOT NULL,
		  `post_title` text NOT NULL,
		  `post_type` varchar(5) NOT NULL DEFAULT 'pages',
		  `post_visibility` enum('public','private') DEFAULT 'public',
		  `post_permalink` varchar(255) NOT NULL,
		  `post_language` varchar(16) NOT NULL DEFAULT 'all',
		  PRIMARY KEY (`id`),
		  KEY `post_date` (`post_date`),
		  KEY `post_author` (`post_author`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'premium_codes' => "
		CREATE TABLE `" . DB_PREFIX . "premium_codes` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `code` varchar(64) NOT NULL,
		  `code_activ` tinyint(1) NOT NULL DEFAULT '1',
		  `service_id` int(10) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `service` (`code`,`service_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'premium_services' => "
		CREATE TABLE `" . DB_PREFIX . "premium_services` (
		  `service_id` int(10) NOT NULL AUTO_INCREMENT,
		  `sms_number` int(16) NOT NULL,
		  `sms_price` varchar(20) NOT NULL,
		  `sms_service_name` varchar(20) NOT NULL DEFAULT '',
		  `sms_content` varchar(20) NOT NULL,
		  `sms_extend_premium` int(5) NOT NULL,
		  `sms_codes_verify` tinyint(1) NOT NULL DEFAULT '1',
		  `sms_description` text NOT NULL,
		  `delete_codes` tinyint(1) NOT NULL DEFAULT '0',
		  `provider_id` varchar(64) NOT NULL DEFAULT '',
		  `codes_used` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`service_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'premium_users' => "
		CREATE TABLE `" . DB_PREFIX . "premium_users` (
		  `premium_id` int(10) NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) NOT NULL,
		  `premium_from` datetime NOT NULL,
		  `days` smallint(5) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`premium_id`),
		  UNIQUE KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'reporting' => "
		CREATE TABLE `" . DB_PREFIX . "reporting` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) NOT NULL,
		  `upload_id` int(10) NOT NULL,
		  `report_type` varchar(12) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'shares' => "
		CREATE TABLE `" . DB_PREFIX . "shares` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `upload_id` int(10) NOT NULL,
		  `share` int(5) NOT NULL DEFAULT '0',
		  `google` int(5) NOT NULL DEFAULT '0',
		  `nk` int(5) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `upload_id` (`upload_id`),
		  KEY `all_shares` (`share`,`google`,`nk`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'system_settings' => "
		CREATE TABLE `" . DB_PREFIX . "system_settings` (
		  `settings_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
		  `settings_name` varchar(64) NOT NULL DEFAULT '',
		  `settings_value` mediumtext NOT NULL,
		  `autoload` varchar(20) NOT NULL DEFAULT 'yes',
		  PRIMARY KEY (`settings_id`),
		  UNIQUE KEY `option_name` (`settings_name`),
		  UNIQUE KEY `unique_autoload` (`settings_name`,`autoload`),
		  KEY `autoload` (`autoload`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'temporary' => "
		CREATE TABLE `" . DB_PREFIX . "temporary` (
		  `id` int(6) NOT NULL AUTO_INCREMENT,
		  `ip` char(15) NOT NULL,
		  `user_id` int(20) DEFAULT NULL,
		  `time` int(11) NOT NULL,
		  `action` varchar(32) NOT NULL,
		  `object_id` int(20) DEFAULT NULL,
		  `temporary_extra` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'translations' => "
		CREATE TABLE `" . DB_PREFIX . "translations` (
		  `translation_name` varchar(64) NOT NULL DEFAULT '',
		  `translation_value` text NOT NULL,
		  `orginal` text NOT NULL,
		  `language` varchar(20) NOT NULL DEFAULT 'PL',
		  UNIQUE KEY `duplicates` (`translation_name`,`language`),
		  KEY `language` (`language`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'updates_table' => "
		CREATE TABLE `" . DB_PREFIX . "updates_table` (
		  `up_id` int(11) NOT NULL AUTO_INCREMENT,
		  `up_created` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
		  `up_installed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `up_title` text COLLATE utf8_unicode_ci NOT NULL,
		  `up_description` text COLLATE utf8_unicode_ci NOT NULL,
		  `up_hash` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
		  `up_status` enum('available','downloaded','installed') COLLATE utf8_unicode_ci NOT NULL,
		  `up_directory` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`up_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Store updates data';
	",
	'upload_categories' => "
		CREATE TABLE `" . DB_PREFIX . "upload_categories` (
		  `id_category` smallint(4) NOT NULL AUTO_INCREMENT,
		  `category_name` varchar(255) NOT NULL,
		  `category_name_dupiaty` varchar(255) NOT NULL DEFAULT '',
		  `category_name_fr` varchar(255) NOT NULL DEFAULT '',
		  `category_name_en` varchar(255) NOT NULL DEFAULT '',
		  `category_name_pl` varchar(255) NOT NULL DEFAULT '',
		  `category_image` varchar(100) NOT NULL DEFAULT '',
		  `only_adult` tinyint(1) NOT NULL DEFAULT '0',
		  `only_premium` tinyint(1) NOT NULL DEFAULT '0',
		  `only_logged_in` tinyint(1) NOT NULL DEFAULT '0',
		  `is_default_category` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id_category`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_comments' => "
		CREATE TABLE `" . DB_PREFIX . "upload_comments` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `user_login` varchar(100) NOT NULL,
		  `user_id` int(10) NOT NULL,
		  `content` text NOT NULL,
		  `upload_id` int(9) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `comment_opinion` int(10) NOT NULL DEFAULT '0',
		  `comment_votes` int(10) NOT NULL DEFAULT '0',
		  `is_reply` tinyint(1) NOT NULL DEFAULT '0',
		  `is_reply_for_id` int(10) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_imported' => "
		CREATE TABLE `" . DB_PREFIX . "upload_imported` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL,
		  `link` varchar(255) NOT NULL,
		  `source_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_meta' => "
		CREATE TABLE `" . DB_PREFIX . "upload_meta` (
		  `upload_meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `upload_id` bigint(20) unsigned NOT NULL DEFAULT '0',
		  `meta_key` varchar(255) DEFAULT NULL,
		  `meta_value` varchar(512) DEFAULT NULL,
		  PRIMARY KEY (`upload_meta_id`),
		  UNIQUE KEY `upload_id_meta` (`upload_id`,`meta_key`),
		  KEY `meta_key` (`meta_key`),
		  KEY `upload_id` (`upload_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_post' => "
		CREATE TABLE `" . DB_PREFIX . "upload_post` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL,
		  `top_line` varchar(255) NOT NULL,
		  `bottom_line` varchar(255) NOT NULL,
		  `user_login` varchar(100) NOT NULL,
		  `user_id` int(10) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `votes_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `votes_up` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `votes_down` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `votes_opinion` mediumint(8) NOT NULL DEFAULT '0',
		  `comments` smallint(4) NOT NULL DEFAULT '0',
		  `comments_facebook` smallint(4) NOT NULL DEFAULT '0',
		  `upload_image` varchar(255) NOT NULL,
		  `upload_activ` tinyint(1) NOT NULL,
		  `upload_type` enum('animation','article','demotywator','gallery','image','mem','ranking','text','video') NOT NULL DEFAULT 'image',
		  `upload_status` enum('archive','private','public') NOT NULL DEFAULT 'public',
		  `upload_subtype` enum('image','video','mp4','text','animation','swf') NOT NULL DEFAULT 'image',
		  `upload_source` varchar(512) NOT NULL DEFAULT '',
		  `upload_video` varchar(512) NOT NULL,
		  `category_id` int(10) NOT NULL DEFAULT '0',
		  `upload_adult` tinyint(1) NOT NULL DEFAULT '0',
		  `upload_data` varchar(1024) NOT NULL,
		  `up_lock` varchar(16) NOT NULL DEFAULT 'off',
		  `seo_link` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `upload_views` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `date_add` (`date_add`),
		  KEY `typ` (`upload_type`),
		  KEY `user_id` (`user_id`),
		  KEY `upload_status` (`upload_status`),
		  FULLTEXT KEY `title` (`title`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_ranking_files' => "
		CREATE TABLE `" . DB_PREFIX . "upload_ranking_files` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `upload_id` int(10) NOT NULL,
		  `src` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
		  `votes_up` int(10) NOT NULL DEFAULT '0',
		  `votes_down` int(10) NOT NULL DEFAULT '0',
		  `votes_count` int(10) NOT NULL DEFAULT '0',
		  `votes_opinion` int(10) NOT NULL DEFAULT '0',
		  `share` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `file_id` (`upload_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'upload_tags' => "
		CREATE TABLE `" . DB_PREFIX . "upload_tags` (
		  `id_tag` int(11) NOT NULL AUTO_INCREMENT,
		  `tag` varchar(50) NOT NULL,
		  PRIMARY KEY (`id_tag`),
		  UNIQUE KEY `id_tag_tag` (`id_tag`,`tag`),
		  UNIQUE KEY `tag_text` (`tag`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_tags_post' => "
		CREATE TABLE `" . DB_PREFIX . "upload_tags_post` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `upload_id` int(10) NOT NULL,
		  `id_tag` int(10) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `upload_id_tag` (`upload_id`,`id_tag`),
		  KEY `id_tag` (`id_tag`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'upload_text' => "
		CREATE TABLE `" . DB_PREFIX . "upload_text` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `upload_id` int(10) NOT NULL,
		  `intro_text` text COLLATE utf8_unicode_ci NOT NULL,
		  `long_text` text COLLATE utf8_unicode_ci NOT NULL,
		  `upload_extra` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `upload_id` (`upload_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'users' => "
		CREATE TABLE `" . DB_PREFIX . "users` (
		  `id` int(6) NOT NULL AUTO_INCREMENT,
		  `login` varchar(100) NOT NULL,
		  `first_name` varchar(100) NOT NULL DEFAULT '',
		  `last_name` varchar(100) NOT NULL DEFAULT '',
		  `password` char(32) NOT NULL,
		  `email` varchar(100) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `avatar` varchar(100) NOT NULL DEFAULT 'anonymus.png',
		  `activ` tinyint(1) NOT NULL,
		  `user_banned` tinyint(1) NOT NULL DEFAULT '0',
		  `user_birth_date` datetime NOT NULL,
		  `user_is_following` int(10) NOT NULL DEFAULT '0',
		  `user_followers` int(10) NOT NULL DEFAULT '0',
		  `user_boards` int(10) NOT NULL DEFAULT '0',
		  `user_uploads` int(10) NOT NULL DEFAULT '0',
		  `user_likes` int(10) NOT NULL DEFAULT '0',
		  `user_comments` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `login` (`login`),
		  UNIQUE KEY `login_email` (`login`,`email`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'users_data' => "
		CREATE TABLE `" . DB_PREFIX . "users_data` (
		  `users_data_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
		  `setting_key` varchar(255) DEFAULT NULL,
		  `setting_value` longtext,
		  PRIMARY KEY (`users_data_id`),
		  UNIQUE KEY `user_id_meta` (`user_id`,`setting_key`),
		  KEY `user_id` (`user_id`),
		  KEY `meta_key` (`setting_key`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'users_favourites' => "
		CREATE TABLE `" . DB_PREFIX . "users_favourites` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) NOT NULL,
		  `upload_id` int(11) NOT NULL,
		  `date_add` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'users_follow_user' => "
		CREATE TABLE `" . DB_PREFIX . "users_follow_user` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `user_id` int(20) NOT NULL,
		  `user_followed_id` int(20) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `user_id` (`user_id`,`user_followed_id`,`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'users_messages' => "
		CREATE TABLE `" . DB_PREFIX . "users_messages` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `message_title` varchar(255) DEFAULT NULL,
		  `message` text NOT NULL,
		  `from_user_id` int(11) NOT NULL,
		  `user_to_id` int(11) NOT NULL,
		  `is_readed` tinyint(1) NOT NULL DEFAULT '0',
		  `to_delete` tinyint(1) NOT NULL DEFAULT '0',
		  `created` datetime NOT NULL,
		  `viewed_date` datetime NOT NULL,
		  `moved_to_delete_date` datetime NOT NULL,
		  `is_system_info` tinyint(1) NOT NULL DEFAULT '0',
		  `ajax_read` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	'users_notifications' => "
		CREATE TABLE `" . DB_PREFIX . "users_notifications` (
		  `notify_id` int(20) NOT NULL AUTO_INCREMENT,
		  `notify_user_id` int(20) NOT NULL,
		  `notify_sender_id` int(20) NOT NULL,
		  `notify_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
		  `notify_content` text COLLATE utf8_unicode_ci NOT NULL,
		  `notify_message` text COLLATE utf8_unicode_ci NOT NULL,
		  `notify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `notify_viewed` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`notify_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	",
	'users_online' => "
		CREATE TABLE `" . DB_PREFIX . "users_online` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `ip` int(15) NOT NULL,
		  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `user_id` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ip` (`ip`),
		  KEY `user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	",
	
);