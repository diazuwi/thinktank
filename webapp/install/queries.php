<?php
/**
 * Requirements:
 * - Each statement must be separated by semicolon(;)
 * - After the 'PRIMARY KEY' keyword there must be two spaces
 */
$install_queries = "
ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE TABLE {$table['follows']} (
  user_id int(11) NOT NULL,
  follower_id int(11) NOT NULL,
  last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  active int(11) NOT NULL DEFAULT '1',
  network varchar(10) NOT NULL DEFAULT 'twitter',
  debug_api_call varchar(255) NOT NULL,
  PRIMARY KEY  (user_id,follower_id),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['instances']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  network_user_id bigint(11) NOT NULL,
  network_viewer_id bigint(20) NOT NULL DEFAULT '1',
  network_username varchar(255) NOT NULL,
  last_status_id bigint(11) DEFAULT NULL,
  crawler_last_run timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_page_fetched_replies int(11) NOT NULL DEFAULT '1',
  last_page_fetched_tweets int(11) NOT NULL DEFAULT '1',
  total_posts_by_owner int(11) DEFAULT '0',
  total_posts_in_system int(11) DEFAULT '0',
  total_replies_in_system int(11) DEFAULT NULL,
  total_users_in_system int(11) DEFAULT NULL,
  total_follows_in_system int(11) DEFAULT NULL,
  earliest_post_in_system datetime DEFAULT NULL,
  earliest_reply_in_system datetime DEFAULT NULL,
  is_archive_loaded_replies int(11) NOT NULL DEFAULT '0',
  is_archive_loaded_follows int(11) NOT NULL DEFAULT '0',
  api_calls_to_leave_unmade_per_minute decimal(11,1) NOT NULL DEFAULT '2.0',
  is_public int(1) NOT NULL DEFAULT '0',
  is_active int(1) NOT NULL DEFAULT '1',
  network varchar(20) NOT NULL DEFAULT 'twitter',
  PRIMARY KEY  (id),
  KEY twitter_user_id (network_user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['links']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  expanded_url varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  clicks int(11) NOT NULL DEFAULT '0',
  post_id bigint(11) DEFAULT NULL,
  is_image tinyint(4) NOT NULL DEFAULT '0',
  error varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (id),
  KEY status_id (post_id),
  KEY is_image (is_image)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['owner_instances']} (
  id int(20) NOT NULL AUTO_INCREMENT,
  owner_id int(10) NOT NULL,
  instance_id int(10) NOT NULL,
  oauth_access_token varchar(255) DEFAULT NULL,
  oauth_access_token_secret varchar(255) DEFAULT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['owners']} (
  id int(20) NOT NULL AUTO_INCREMENT,
  full_name varchar(200) NOT NULL,
  user_name varchar(200) NOT NULL,
  user_pwd varchar(200) NOT NULL,
  user_email varchar(200) NOT NULL,
  activation_code int(10) NOT NULL DEFAULT '0',
  joined date NOT NULL DEFAULT '0000-00-00',
  country varchar(100) NOT NULL,
  user_activated int(1) NOT NULL DEFAULT '0',
  is_admin int(1) NOT NULL DEFAULT '0',
  last_login date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['plugin_options']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  plugin_id int(11) NOT NULL,
  option_name varchar(255) NOT NULL,
  option_value varchar(255) NOT NULL,
  PRIMARY KEY  (id),
  KEY plugin_id (plugin_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['plugins']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  folder_name varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  author varchar(255) DEFAULT NULL,
  homepage varchar(255) DEFAULT NULL,
  version varchar(255) DEFAULT NULL,
  is_active tinyint(4) NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE {$table['post_errors']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id bigint(20) NOT NULL,
  error_code int(11) NOT NULL,
  error_text varchar(255) NOT NULL,
  error_issued_to_user_id int(11) NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY status_id (post_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['posts']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id bigint(11) NOT NULL,
  author_user_id bigint(11) NOT NULL,
  author_username varchar(50) NOT NULL,
  author_fullname varchar(50) NOT NULL,
  author_avatar varchar(255) NOT NULL,
  post_text varchar(255) NOT NULL,
  source varchar(255) DEFAULT NULL,
  location varchar(255) DEFAULT NULL,
  place varchar(255) DEFAULT NULL,
  geo varchar(255) DEFAULT NULL,
  pub_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  in_reply_to_user_id int(11) DEFAULT NULL,
  in_reply_to_post_id bigint(11) DEFAULT NULL,
  reply_count_cache int(11) NOT NULL DEFAULT '0',
  is_reply_by_friend tinyint(4) NOT NULL DEFAULT '0',
  in_retweet_of_post_id bigint(11) DEFAULT NULL,
  retweet_count_cache int(11) NOT NULL DEFAULT '0',
  is_retweet_by_friend tinyint(4) NOT NULL DEFAULT '0',
  network varchar(10) NOT NULL DEFAULT 'twitter',
  is_geo_encoded int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  UNIQUE KEY status_id (post_id),
  KEY author_username (author_username),
  KEY pub_date (pub_date),
  KEY author_user_id (author_user_id),
  KEY in_retweet_of_status_id (in_retweet_of_post_id),
  KEY in_reply_to_user_id (in_reply_to_user_id),
  FULLTEXT KEY tweets_fulltext (post_text)  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['user_errors']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(20) NOT NULL,
  error_code int(11) NOT NULL,
  error_text varchar(255) NOT NULL,
  error_issued_to_user_id int(11) NOT NULL,
  network varchar(10) NOT NULL DEFAULT 'twitter',
  PRIMARY KEY  (id),
  KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE {$table['users']} (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  user_name varchar(255) NOT NULL,
  full_name varchar(255) NOT NULL,
  avatar varchar(255) NOT NULL,
  location varchar(255) DEFAULT NULL,
  description text,
  url varchar(255) DEFAULT NULL,
  is_protected tinyint(1) NOT NULL,
  follower_count int(11) NOT NULL,
  friend_count int(11) NOT NULL DEFAULT '0',
  post_count int(11) NOT NULL,
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  found_in varchar(100) DEFAULT NULL,
  last_post timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  joined timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_post_id bigint(11) NOT NULL DEFAULT '0',
  network varchar(10) NOT NULL DEFAULT 'twitter',
  PRIMARY KEY  (id),
  UNIQUE KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO {$table['plugins']} (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Twitter', 'twitter', 'Twitter support', 'Gina Trapani', 'http://thinktankapp.com', '0.01', '1');";