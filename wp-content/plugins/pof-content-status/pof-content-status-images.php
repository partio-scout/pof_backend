<?php


function pof_content_status_images() {
	if ( !current_user_can( 'pof_manage_status' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Kuvat ja liitteet</h1>';

    echo pof_content_status_images_get_form();

	echo '</div>';

	if (   isset($_POST)
		&& isset($_POST["agegroup"])) {
	    echo '<div class="wrap">';
        pof_content_status_images_get_content($_POST["agegroup"]);
    	echo '</div>';
    }

}

function pof_content_status_images_get_form() {
    $ret = "";

    $ret .= "<form method=\"POST\">";

    $ret .=  "Valitse ik&auml;kausi: <br />";
    $ret .=  '<select name="agegroup">';

    $agegroups = pof_taxonomy_translate_get_agegroups();

    foreach ($agegroups as $agegroup) {
        if ($agegroup->id == 0) {
            continue;
        }
        $selected = "";
        if (   isset($_POST)
	        && isset($_POST["agegroup"])) {
            if ($_POST["agegroup"] == $agegroup->id) {
                $selected = " selected=\"selected\"";
            }
        }
        $ret .= "<option".$selected." value=\"" . $agegroup->id . "\">" . $agegroup->title . "</option>\n";
	}

    $ret .=  '</select>';

	$ret .= '<br /><br /><input type="submit" name="Submit" value="N&auml;yt&auml;" />';

    $ret .= "</form>";

    return $ret;

}

function pof_content_status_images_get_image_field($post_id, $image_field) {

    $ret = "";

    $image = get_field($image_field, $post_id);
	if ($image) {
        // /wp-admin/upload.php?item=7109

        $ret .= "<a href=\"/wp-admin/upload.php?item=" . $image['id'] . "\" target=\"_blank\">" . $image['title'] . "</a>";
        $ret .= "<br />";
        $ret .= $image['mime_type'];
        $ret .= "<br />";
        $ret .= "<span class=\"nowrap\">o: <a href=\"" . $image['url'] . "\" target=\"_blank\">" . $image['width'] . " x " . $image['height'] . "</a></span>";
        $ret .= "<br />";

        if (!empty($image['sizes'])) {
			if (!empty($image['sizes']['thumbnail'])) {
                $ret .= "<span class=\"nowrap\">t: <a href=\"" . $image['sizes']['thumbnail'] . "\" target=\"_blank\">" . $image['sizes']['thumbnail-width'] . " x " . $image['sizes']['thumbnail-height'] . "</a></span>";
                $ret .= "<br />";
            }
            if (!empty($image['sizes']['thumbnailcropped'])) {
                $ret .= "<span class=\"nowrap\">tc: <a href=\"" . $image['sizes']['thumbnailcropped'] . "\" target=\"_blank\">" . $image['sizes']['thumbnailcropped-width'] . " x " . $image['sizes']['thumbnailcropped-height'] . "</a></span>";
                $ret .= "<br />";
            }
			if (!empty($image['sizes']['medium'])) {
                $ret .= "<span class=\"nowrap\">m: <a href=\"" . $image['sizes']['medium'] . "\" target=\"_blank\">" . $image['sizes']['medium-width'] . " x " . $image['sizes']['medium-height'] . "</a></span>";
                $ret .= "<br />";
            }
			if (!empty($image['sizes']['large'])) {
                $ret .= "<span class=\"nowrap\">l: <a href=\"" . $image['sizes']['large'] . "\" target=\"_blank\">" . $image['sizes']['large-width'] . " x " . $image['sizes']['large-height'] . "</a></span>";
                $ret .= "<br />";
            }
		}

	}
    return $ret;
}

function pof_content_status_images_get_content($agegroup_id) {

?>

    <style>

    #pof_content_status_table td {
        padding: 2px;
        text-align: left;
        vertical-align: top;
    }

    #pof_content_status_table td.title {
        text-align: left;
    }

    .pof_content_status_black {
        background: black;
        text-align: center;
        color: white;
    }
    .pof_content_status_green {
        background: green;
        text-align: center;
        color: white;
    }

     .pof_content_status_grey {
        background: #808080;
        text-align: center;
        color: white;
    }

    .pof_content_status_counters {
        text-align: center;
        font-weight: bold;
    }

    .nowrap {
        white-space: nowrap;
    }

    </style>

<?php
    $langs = pof_settings_get_all_languages();
    $langs_count = count($langs);

?>

    <table cellpadding="1" cellspacing="1" border="1" id="pof_content_status_table">
        <thead>
        <tr>
            <th colspan="2"></th>
            <th rowspan="3">Logo</th>
            <th rowspan="3">Iso kuva</th>
            <th colspan="<?php echo ($langs_count * 3 + 3 ); ?>">Lis&auml;liitteet</th>
        </tr>
            <tr>
                <th colspan="2"></th>
                <?php
                foreach($langs as $lang) {
                    ?>
                    <th colspan="3"><?php echo  $lang->lang_title; ?></th>
                    <?php
                }
                    ?>
                    <th colspan="3">??</th>
            </tr>
            <tr>
                <th colspan="2"></th>
                <?php

                for ($i = 0;$i<($langs_count + 1); $i++) {
                ?>
                    <th>Kuvat</th>
                    <th>Tiedostot</th>
                    <th>Linkit</th>
                    <?php
                }

                    ?>
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
                <td class="title"><?php echo "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                <td><?php echo pof_content_status_images_get_image_field($the_query->post->ID, 'logo_image'); ?></td>
                <td><?php echo pof_content_status_images_get_image_field($the_query->post->ID, 'main_image'); ?></td>

                <?php
            foreach($langs as $lang) {
                ?>
                    <td><?php echo pof_content_status_images_get_additional_images($the_query->post->ID, $lang->lang_code); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_files($the_query->post->ID, $lang->lang_code); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_links($the_query->post->ID, $lang->lang_code); ?></td>
                    <?php
            }
                    ?>
                    <td><?php echo pof_content_status_images_get_additional_images($the_query->post->ID, ""); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_files($the_query->post->ID, ""); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_links($the_query->post->ID, ""); ?></td>
            </tr>

            <?php
            pof_content_status_images_clear_additional_content_arrays();
            pof_content_status_images_content_get_taskgroups($the_query->post->ID, 1);
            $indentation = 0;
            pof_content_status_images_content_get_tasks($the_query->post->ID);
		}
	}
    wp_reset_query();


	echo "</tbody>";
    echo "</table>";

}

function pof_content_status_images_content_get_taskgroups($taskgroup_id, $indentation = 0) {
    $langs = pof_settings_get_all_languages();
    $langs_count = count($langs);

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
                <td class="title"><?php echo $intendation_str . "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                <td><?php echo pof_content_status_images_get_image_field($the_query->post->ID, 'logo_image'); ?></td>
                <td><?php echo pof_content_status_images_get_image_field($the_query->post->ID, 'main_image'); ?></td>
                <?php
            foreach($langs as $lang) {
                ?>
                    <td><?php echo pof_content_status_images_get_additional_images($the_query->post->ID, $lang->lang_code); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_files($the_query->post->ID, $lang->lang_code); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_links($the_query->post->ID, $lang->lang_code); ?></td>
                    <?php
            }
                    ?>
                    <td><?php echo pof_content_status_images_get_additional_images($the_query->post->ID, ""); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_files($the_query->post->ID, ""); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_links($the_query->post->ID, ""); ?></td>
            </tr>

            <?php
            pof_content_status_images_clear_additional_content_arrays();
            pof_content_status_images_content_get_taskgroups($the_query->post->ID, $indentation + 1);
            pof_content_status_images_content_get_tasks($the_query->post->ID, $indentation + 1);
		}
	}

    wp_reset_query();
}


function pof_content_status_images_content_get_tasks($taskgroup_id, $indentation = 0) {

    $langs = pof_settings_get_all_languages();
    $langs_count = count($langs);

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
                <td class="title"><?php echo $intendation_str . "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                <td><?php echo pof_content_status_images_get_image_field($the_query->post->ID, 'logo_image'); ?></td>
                <td><?php echo pof_content_status_images_get_image_field($the_query->post->ID, 'main_image'); ?></td>
                <?php
            foreach($langs as $lang) {
                ?>
                    <td><?php echo pof_content_status_images_get_additional_images($the_query->post->ID, $lang->lang_code); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_files($the_query->post->ID, $lang->lang_code); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_links($the_query->post->ID, $lang->lang_code); ?></td>
                    <?php
            }
                    ?>
                    <td><?php echo pof_content_status_images_get_additional_images($the_query->post->ID, ""); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_files($the_query->post->ID, ""); ?></td>
                    <td><?php echo pof_content_status_images_get_additional_links($the_query->post->ID, ""); ?></td>
            </tr>

            <?php

		}
	}

    wp_reset_query();
    pof_content_status_images_clear_additional_content_arrays();
}

$field_counters = array();

$additional_images = array();
$additional_files = array();
$additional_links = array();

function pof_content_status_images_pof_lang_to_simple_fields_lang_dropdown($pof_lang, $simple_fields_lang)
{
    if (($pof_lang == null || $pof_lang == "") && ($simple_fields_lang == null || $simple_fields_lang == "")) {
        return true;
    }

    switch($pof_lang) {
        case "fi":
            if ($simple_fields_lang == "Suomi") { return true; }
            break;
        case "sv":
            if ($simple_fields_lang == "Ruotsi") { return true; }
            break;
        case "en":
            if ($simple_fields_lang == "Englanti") { return true; }
            break;
    }

    return false;
}


function pof_content_status_images_get_additional_images($post_id, $lang) {
    global $additional_images;

    $ret = "";

    if (!array_key_exists($post_id, $additional_images)) {
        $additional_images[$post_id] = simple_fields_fieldgroup("additional_images_fg", $post_id);
    }

    if ($additional_images[$post_id]) {
		foreach ($additional_images[$post_id] as $additional_image) {
			if ($additional_image['additional_image']) {
				$image = $additional_image['additional_image'];
                $image_lang = null;

                $image_lang_arr = $additional_image['additional_image_lang'];

                if (is_array($image_lang_arr)) {
                    $image_lang = $image_lang_arr["selected_value"];
                }

                $lang_match = pof_content_status_images_pof_lang_to_simple_fields_lang_dropdown($lang, $image_lang);

                if (!$lang_match) {
                    continue;
                }

                $ret .= "<a href=\"/wp-admin/upload.php?item=" . $image['id'] . "\" target=\"_blank\">" . $additional_image['additional_image_text'] . "</a>";
                $ret .= "<br />";
                $ret .= $image['mime'];

                if (!is_array($image['metadata'])) {
                    $ret .= "ERROR!!!!";
                    continue;
                }
                $ret .= "<br />";
                $ret .= "<span class=\"nowrap\">o: <a href=\"" . $image['url'] . "\" target=\"_blank\">" . $image['metadata']['width'] . " x " . $image['metadata']['height'] . "</a></span>";
                $ret .= "<br />";

                if (!empty($image['image_src'])) {
                    if (!empty($image['image_src']['thumbnail'])) {
                        $ret .= "<span class=\"nowrap\">t: <a href=\"" . $image['image_src']['thumbnail'][0] . "\" target=\"_blank\">" . $image['image_src']['thumbnail'][2] . " x " . $image['image_src']['thumbnail'][1] . "</a></span>";
                        $ret .= "<br />";
                    }
                    if (!empty($image['image_src']['medium'])) {
                        $ret .= "<span class=\"nowrap\">m: <a href=\"" . $image['image_src']['medium'][0] . "\" target=\"_blank\">" . $image['image_src']['medium'][2] . " x " . $image['image_src']['medium'][1] . "</a></span>";
                        $ret .= "<br />";
                    }
                    if (!empty($image['image_src']['large'])) {
                        $ret .= "<span class=\"nowrap\">l: <a href=\"" . $image['image_src']['large'][0] . "\" target=\"_blank\">" . $image['image_src']['large'][2] . " x " . $image['image_src']['large'][1] . "</a></span>";
                        $ret .= "<br />";
                    }
                }

			}
		}
	}

    return $ret;
}

function pof_content_status_images_get_additional_files($post_id, $lang) {
    global $additional_files;

    $ret = "";

    if (!array_key_exists($post_id, $additional_files)) {
        $additional_files[$post_id] = simple_fields_fieldgroup("additional_files_fg", $post_id);
    }


	if ($additional_files[$post_id]) {
		foreach ($additional_files[$post_id] as $additional_file) {

            if ($additional_file['additional_file']) {

				$file = $additional_file['additional_file'];

                if ($file['url'] == false) {
                    continue;
                }

                $file_lang = null;

                $file_lang_arr = $additional_file['additional_file_lang'];

                if (is_array($file_lang_arr)) {
                    $file_lang = $file_lang_arr["selected_value"];
                }

                $lang_match = pof_content_status_images_pof_lang_to_simple_fields_lang_dropdown($lang, $file_lang);

                if (!$lang_match) {
                    continue;
                }

                $ret .= "<span class=\"nowrap\"><a href=\"" . $file['url'] . "\" target=\"_blank\">" . $additional_file['additional_file_text'] . "</a></span><br />";
                $ret .= $file['mime'] . "<br />";

                if (!empty($file['post'])) {
                    $ret .= $file['post']->post_name . "<br />";
                }
                $ret .= "<br /><br />";
			}
		}
	}
    return $ret;
}

function pof_content_status_images_get_additional_links($post_id, $lang) {
    global $additional_links;
    $ret= "";

    if (!array_key_exists($post_id, $additional_links)) {
        $additional_links[$post_id] = simple_fields_fieldgroup("additional_links_fg", $post_id);
    }

	if ($additional_links[$post_id]) {
		foreach ($additional_links[$post_id] as $additional_link) {
			if ($additional_link['additional_link_url']) {

				$link = $additional_link['additional_link_url'];

                $link_lang = null;

                $link_lang_arr = $additional_link['additional_link_lang'];

                if (is_array($link_lang_arr)) {
                    $link_lang = $link_lang_arr["selected_value"];
                }

                $lang_match = pof_content_status_images_pof_lang_to_simple_fields_lang_dropdown($lang, $link_lang);

                if (!$lang_match) {
                    continue;
                }

                $ret .= "<span class=\"nowrap\"><a href=\"" . $link . "\" target=\"_blank\">" . $additional_link['additional_link_text'] . "</a></span><br />";

			}
		}
	}

    return $ret;
}



function pof_content_status_images_clear_additional_content_arrays() {
    global $additional_images;
    global $additional_links;
    global $additional_images;
    $additional_images = array();
    $additional_files = array();
    $additional_links = array();
}