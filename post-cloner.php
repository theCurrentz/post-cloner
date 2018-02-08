<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
Plugin Name: Post Cloner
Author: Parker Westfall
Description:  WordPress plugin that allows posts to be deep cloned into new Drafts, for further editing. Supports all post types, includes an API for attributing a canonical link back to the original post
Version: 1.0
License: MIT
License URI:  https://github.com/Timbral/post-cloner/blob/master/LICENSE
*/

//enqueue script and styles
function enqueue_post_duplicator_scripts() {
}

add_action('admin_enqueue_scripts', 'enqueue_post_duplicator_scripts');

require_once( plugin_dir_path( __FILE__ ) . '/classes/post-cloner-meta-box.php');
require_once( plugin_dir_path( __FILE__ ) . '/includes/clone-posts-canonical.php');
