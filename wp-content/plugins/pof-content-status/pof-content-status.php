<?php
/**
 * @package POF Content status
 */
/*
Plugin Name: POF Content status
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


/* Helper functions */
include( plugin_dir_path( __FILE__ ) . 'pof-content-status-generic.php');
include( plugin_dir_path( __FILE__ ) . 'pof-content-status-localization.php');
include( plugin_dir_path( __FILE__ ) . 'pof-content-status-orphan.php');
include( plugin_dir_path( __FILE__ ) . 'pof-content-status-tags.php');
include( plugin_dir_path( __FILE__ ) . 'pof-content-status-images.php');

add_action( 'admin_menu', 'pof_content_status_menu' );

function pof_content_status_menu() {
	add_menu_page('POF status', 'POF status', 'pof_manage_status', 'pof_content_status_frontpage-handle', 'pof_content_status_frontpage', 'dashicons-dashboard');
	add_submenu_page( 'pof_content_status_frontpage-handle', 'Yleiset', 'Yleiset', 'pof_manage_status', 'pof_content_status_generic-handle', 'pof_content_status_generic');
	add_submenu_page( 'pof_content_status_frontpage-handle', 'Sis&auml;ll&ouml;t', 'Sis&auml;ll&ouml;t', 'pof_manage_status', 'pof_content_status_localization-handle', 'pof_content_status_localization');
    add_submenu_page( 'pof_content_status_frontpage-handle', 'Orvot', 'Orvot', 'pof_manage_status', 'pof_content_status_orphan-handle', 'pof_content_status_orphan');
    add_submenu_page( 'pof_content_status_frontpage-handle', 'Tagit', 'Tagit', 'pof_manage_status', 'pof_content_status_tags-handle', 'pof_content_status_tags');
    add_submenu_page( 'pof_content_status_frontpage-handle', 'Kuvat ja liitteet', 'Kuvat ja liitteet', 'pof_manage_status', 'pof_content_status_images-handle', 'pof_content_status_images');
}

function pof_content_status_frontpage() {
	if ( !current_user_can( 'pof_manage_status' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>POF status</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat katsoa.</p>';
	echo '</div>';
}