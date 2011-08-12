<?php
/*
Plugin Name: Style Author Base
Plugin URI: http://codecto.com/
Description: This plugin can change the author posts link.
Version: 1.0
Author: bolo1988
Author URI: http://codecto.com/
License: GPLv2 or later
*/

add_filter('author_link', 'no_author_base', 1000, 2);
function no_author_base($link, $author_id) {
	//$link_base = trailingslashit(get_option('permalink_structure'));
	$link_base = explode('/', get_option('permalink_structure'));
	$link_base = $link_base[1];
	$new_author_base = get_option('author_base')?get_option('author_base'):$rewrite->author_base;
	$link = str_replace('/'.$link_base.'/author/', '/'.$new_author_base.'/', $link);
	return $link;
}

add_filter('author_rewrite_rules', 'no_author_base_rewrite_rules');
function no_author_base_rewrite_rules($author_rewrite) {
	global $wpdb;
	$author_rewrite = array();
	$authors = $wpdb->get_results("SELECT user_nicename AS nicename from $wpdb->users");
	$new_author_base = get_option('author_base')?get_option('author_base'):$rewrite->author_base;
	foreach($authors as $author) {
		$author_rewrite[$new_author_base."/({$author->nicename})/page/?([0-9]+)/?$"] = 'index.php?author_name=$matches[1]&paged=$matches[2]';
		$author_rewrite[$new_author_base."/({$author->nicename})/?$"] = 'index.php?author_name=$matches[1]';
	}
	return $author_rewrite;
}

add_action('admin_init', 'custom_permalink_init');
function custom_permalink_init(){
	global $pagenow;
	add_settings_section('author_base', __('Style Author Base'), 'custom_author_base_section', 'permalink');
	add_settings_field('author_base', __('Author base'), 'custom_author_base_field', 'permalink', 'optional', array('label_for'=>'author_base'));
	register_setting('permalink', 'author_base', 'esc_attr');
	if($pagenow == 'options-permalink.php' && wp_verify_nonce($_POST['_wpnonce'], 'update-permalink')){
		update_option('author_base', $_POST['author_base']);
	}
}

function custom_author_base_field($args){
	$rewrite = new WP_Rewrite;
	$author_base = get_option('author_base')?get_option('author_base'):$rewrite->author_base;
	foreach($args as $name):?>
		<input name="<?php echo $name; ?>" id="<?php echo $name; ?>" type="text" class="regular-text code" value="<?php echo $author_base; ?>" />
	<?php endforeach;
}

function custom_author_base_section(){
	echo '<p>'.__('Author base setting is powered by <a href="http://codecto.com/" target="_blank">CodeCTO.com</a>.').'</p>';
}
