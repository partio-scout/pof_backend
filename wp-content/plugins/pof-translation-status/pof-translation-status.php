<?php
/**
 * @package POF Translation status
 */
/*
Plugin Name: POF Translation status
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

add_action( 'admin_menu', 'pof_translation_status_menu' );

function pof_translation_status_menu() {
	add_menu_page('POF K&auml;&auml;nn&ouml;kset status', 'POF K&auml;&auml;nn&ouml;kset status', 'manage_options', 'pof_translation_status_frontpage-handle', 'pof_translation_status_frontpage', 'dashicons-dashboard');
}


function pof_translation_status_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF K&auml;&auml;nn&ouml;kset status</h1>';

    echo pof_translation_status_get_form();

	echo '</div>';

	if (   isset($_POST)
		&& isset($_POST["lang"])
		&& isset($_POST["agegroup"])) {
	    echo '<div class="wrap">';
        pof_translation_status_get_content($_POST["lang"], $_POST["agegroup"]);
    	echo '</div>';
    }

}

function pof_translation_status_content_get_tasks($lang, $taskgroup_id, $indentation = 0) {

    $args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_task',
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $taskgroup_id
	);

	$the_query = new WP_Query( $args );

    $intendation_str = "";

    if ($indentation > 0) {
        for ($i=0;$i < $indentation; $i++) {
            $intendation_str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        }
    }

    if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			// try to avoid infinite loops
			if ($the_query->post->ID == $taskgroup_id) {
				continue;
			}
                        
			?>
            <tr>
                <td>Aktiviteetti</td>
                <td><?php echo $intendation_str . "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                 <?php
                echo pof_translation_status_get_field_cell("ingress", $the_query->post->ID);
                echo pof_translation_status_get_content_cell($the_query->post->post_content, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("leader_tasks_fi", $the_query->post->ID);
                echo pof_translation_status_get_field_cell("growth_target_fi", $the_query->post->ID);
                
                echo pof_translation_status_get_field_cell("title_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("ingress_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("content_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("leader_tasks_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("growth_target_".$lang, $the_query->post->ID);
                ?>
            </tr>

            <?php
		}
	}

    wp_reset_query();
}

function pof_translation_status_content_get_taskgroups($lang, $taskgroup_id, $indentation = 0) {

    $args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $taskgroup_id
	);

	$the_query = new WP_Query( $args );

    $intendation_str = "";

    if ($indentation > 0) {
        for ($i=0;$i < $indentation; $i++) {
            $intendation_str .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        }
    }

    if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			// try to avoid infinite loops
			if ($the_query->post->ID == $taskgroup_id) {
				continue;
			}
                        
			?>
            <tr>
                <td>Paketti</td>
                <td><?php echo $intendation_str . "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                 <?php
                echo pof_translation_status_get_field_cell("ingress", $the_query->post->ID);
                echo pof_translation_status_get_content_cell($the_query->post->post_content, $the_query->post->ID);

                ?>
                <td></td>
                <td></td>
                <?php
                echo pof_translation_status_get_field_cell("title_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("ingress_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("content_".$lang, $the_query->post->ID);
                ?>
                <td></td>
                <td></td>
            </tr>

            <?php
            pof_translation_status_content_get_taskgroups($lang, $the_query->post->ID, $indentation + 1);
            pof_translation_status_content_get_tasks($lang, $the_query->post->ID, $indentation + 1);
		}
	}

    wp_reset_query();
}


function pof_translation_status_get_content($lang, $agegroup_id) {
    
    ?>

    <style>
    
    #pof_translation_status_table td {
        padding: 1px;

    }

    .pof_translation_status_black {
        background: black;
        text-align: center;
        color: white;
    }
    .pof_translation_status_green {
        background: green;
        text-align: center;
        color: white;
    }
    </style>

    <table cellpadding="1" cellspacing="1" border="1" id="pof_translation_status_table">
        <thead>
        <tr>
            <th></th>
            <th colspan="5">FI</th>
            <th colspan="5"><?php echo $lang; ?></th>
        </tr>
        <tr>
            <th>Tyyppi</th>
            <th>Otsikko</th>
            <th>Ingressi</th>
            <th>Sis&auml;lt&ouml;</th>
            <th>Johtajan teht&auml;v&auml;</th>
            <th>Kasvatustavoitteen avainsana</th>
            <th>Otsikko</th>
            <th>Ingressi</th>
            <th>Sis&auml;lt&ouml;</th>
            <th>Johtajan teht&auml;v&auml;</th>
            <th>Kasvatustavoitteen avainsana</th>
        </tr>
        </thead>
        <tbody>
    <?php

    $args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'ikakausi',
		'meta_value' => $agegroup_id
	);

	$the_query = new WP_Query( $args );


    if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			// try to avoid infinite loops
			if ($the_query->post->ID == $agegroup_id) {
				continue;
			}
                        
			?>
            <tr>
                <td>Paketti</td>
                <td><?php echo "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                <?php
                echo pof_translation_status_get_field_cell("ingress", $the_query->post->ID);
                echo pof_translation_status_get_content_cell($the_query->post->post_content, $the_query->post->ID);

                ?>
                <td></td>
                <td></td>
                <?php
                echo pof_translation_status_get_field_cell("title_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("ingress_".$lang, $the_query->post->ID);
                echo pof_translation_status_get_field_cell("content_".$lang, $the_query->post->ID);
                ?>
                <td></td>
                <td></td>
            </tr>

            <?php
            pof_translation_status_content_get_taskgroups($lang, $the_query->post->ID, 1);
		}
	}
    wp_reset_query();

	echo "</tbody>";
    echo "</table>";

}

function pof_translation_status_get_field_cell($field, $post_id) {

    $content = get_field($field,$post_id);

    $class = "pof_translation_status_black";

    if (strlen($content) > 3) {
        $class = "pof_translation_status_green";
    }

    $word_count = str_word_count(strip_tags($content));

    ?>
    <td class="<?php echo $class; ?>"><?php echo $word_count; ?></td>
    <?php
}

function pof_translation_status_get_content_cell($content, $post_id) {
    $class = "pof_translation_status_black";

    if (strlen($content) > 3) {
        $class = "pof_translation_status_green";
    }

    $word_count = str_word_count(strip_tags($content));

    ?>
    <td class="<?php echo $class; ?>"><?php echo $word_count; ?></td>
    <?php
}


function pof_translation_status_get_form() {
    $ret = "";

    $langs = pof_settings_get_all_languages();
    

    $ret .= "<form method=\"POST\">";
    
    $ret .=  "Valitse kieli: <br />";
    $ret .= "<select name=\"lang\">";

    foreach ($langs as $lang) {
        if ($lang->lang_code == 'fi') {
            continue;
        }
        $ret .= "<option value=\"" . $lang->lang_code . "\">" . $lang->lang_title . "</option>\n";
	}

    $ret .= "</select>";

    $ret .=  "<br />";
    $ret .=  "Valitse ik&auml;kausi: <br />";
    $ret .=  '<select name="agegroup">';

    $agegroups = pof_taxonomy_translate_get_agegroups();

    foreach ($agegroups as $agegroup) {
        if ($agegroup->id == 0) {
            continue;
        }
        $ret .= "<option value=\"" . $agegroup->id . "\">" . $agegroup->title . "</option>\n";
	}

    $ret .=  '</select>';
    
	$ret .= '<br /><br /><input type="submit" name="Submit" value="N&auml;yt&auml;" />';

    $ret .= "</form>";

    return $ret;

}