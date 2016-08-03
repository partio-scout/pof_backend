<?php
/**
 * @package POF Sitemap
 */
/*
Plugin Name: POF Sitemap
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

add_action( 'admin_menu', 'pof_sitemap_menu' );

function pof_sitemap_menu() {
	add_menu_page('POF sitemap', 'POF sitemap', 'manage_options', 'pof_sitemap_frontpage-handle', 'pof_sitemap_frontpage', 'dashicons-admin-site');
	add_submenu_page( 'pof_sitemap_frontpage-handle', 'Aja', 'Aja', 'manage_options', 'pof_sitemap_run-handle', 'pof_sitemap_run');
}

function pof_sitemap_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>POF Sitemap</h1>';
	echo '<p></p>';
	echo '</div>';
}

function pof_sitemap_run() {
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    global $wpdb;

    $res = $wpdb->get_results(
			    "
			    SELECT wp_postmeta.meta_value, wp_posts.ID, wp_posts.post_type, wp_posts.post_name
                FROM wp_postmeta
                JOIN wp_posts
                    ON wp_postmeta.post_id = wp_posts.ID
                WHERE wp_postmeta.meta_key = 'post_guid'
                    AND wp_posts.post_status = 'publish'
                    AND wp_posts.post_type IN ('page', 'pof_post_agegroup', 'pof_post_program', 'pof_post_task', 'pof_post_taskgroup')
                ORDER BY wp_posts.post_type, wp_posts.ID;
			    "
		    );

    $langs = pof_settigs_get_active_lang_codes(false);

    $content = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    foreach ($res as $item)
    {
        switch ($item->post_type) {
            case "page":
                $content .= "<url>";
                $content .= "<loc>".get_site_url()."/".$item->post_name."</loc>";
                $content .= "<priority>0.9</priority>";
                $content .= "</url>";
                break;
            case "pof_post_program":
                $content .= "<url>\n";
                $content .= "<loc>".get_site_url()."/spn-ohjelma-json-taysi/?postGUID=".$item->meta_value."</loc>";
                $content .= "<priority>0.9</priority>";
                $content .= "</url>";
                break;
            case "pof_post_task":
                foreach ($langs as $lang_code) {
                    $content .= "<url>\n";
                    $content .= "<loc>".get_site_url()."/item-json/?postGUID=".$item->meta_value."&amp;lang=".$lang_code."</loc>";
                    $content .= "<priority>0.8</priority>";
                    $content .= "</url>";
                    $content .= "<url>";
                    $content .= "<loc>".get_site_url()."/item-jso-vinkit/?postGUID=".$item->meta_value."&amp;lang=".$lang_code."</loc>";
                    $content .= "<priority>0.2</priority>";
                    $content .= "</url>";
                }

                break;
            default:
                foreach ($langs as $lang_code) {
                    $content .= "<url>";
                    $content .= "<loc>".get_site_url()."/item-json/?postGUID=".$item->meta_value."&amp;lang=".$lang_code."</loc>";
                    $content .= "<priority>0.8</priority>";
                    $content .= "</url>";
                }

                break;
        }
    }

    $content .= "</urlset>";


    $destination = get_home_path() . "/pof-sitemap.xml";

	if (file_exists($destination)) {
		unlink($destination);
	}

	$file2 = fopen($destination, "w+");
	fputs($file2, $content);
	fclose($file2);


    echo '<div class="wrap">';
	echo '<h1>POF Sitemap</h1>';
	echo '<p><textarea rows="40" cols="40">'.$content.'</textarea></p>';
	echo '</div>';
}