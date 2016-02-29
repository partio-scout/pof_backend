<?php


function pof_translation_status_localization() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF K&auml;&auml;nn&ouml;kset status</h1>';

    echo pof_translation_status_localization_get_form();

	echo '</div>';

	if (   isset($_POST)
		&& isset($_POST["lang"])
		&& isset($_POST["agegroup"])) {
	    echo '<div class="wrap">';
        pof_translation_status_localization_get_content($_POST["lang"], $_POST["agegroup"]);
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
                echo pof_translation_status_localization_get_field_cell("ingress", $the_query->post->ID);
                echo pof_translation_status_localization_get_content_cell($the_query->post->post_content, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("leader_tasks_fi", $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("growth_target_fi", $the_query->post->ID);
                
                echo pof_translation_status_localization_get_field_cell("title_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("ingress_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("content_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("leader_tasks_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("growth_target_".$lang, $the_query->post->ID);
                ?>
            </tr>

            <?php
		}
	}

    wp_reset_query();
}

function pof_translation_status_localization_get_content_get_taskgroups($lang, $taskgroup_id, $indentation = 0) {

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
                echo pof_translation_status_localization_get_field_cell("ingress", $the_query->post->ID);
                echo pof_translation_status_localization_get_content_cell($the_query->post->post_content, $the_query->post->ID);

                ?>
                <td></td>
                <td></td>
                <?php
                echo pof_translation_status_localization_get_field_cell("title_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("ingress_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("content_".$lang, $the_query->post->ID);
                ?>
                <td></td>
                <td></td>
            </tr>

            <?php
            pof_translation_status_localization_get_content_get_taskgroups($lang, $the_query->post->ID, $indentation + 1);
            pof_translation_status_content_get_tasks($lang, $the_query->post->ID, $indentation + 1);
		}
	}

    wp_reset_query();
}


function pof_translation_status_localization_get_content($lang, $agegroup_id) {
    
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

    .pof_translation_status_counters {
        text-align: center;
        font-weight: bold;
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
                echo pof_translation_status_localization_get_field_cell("ingress", $the_query->post->ID);
                echo pof_translation_status_localization_get_content_cell($the_query->post->post_content, $the_query->post->ID);

                ?>
                <td></td>
                <td></td>
                <?php
                echo pof_translation_status_localization_get_field_cell("title_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("ingress_".$lang, $the_query->post->ID);
                echo pof_translation_status_localization_get_field_cell("content_".$lang, $the_query->post->ID);
                ?>
                <td></td>
                <td></td>
            </tr>

            <?php
            pof_translation_status_localization_get_content_get_taskgroups($lang, $the_query->post->ID, 1);
		}
	}
    wp_reset_query();

    ?>
        <tr>
            <td></td>
            <td></td>
            <?php
            echo pof_translation_status_localization_get_counters_cell("ingress");
            echo pof_translation_status_localization_get_counters_cell("content");
            echo pof_translation_status_localization_get_counters_cell("leader_tasks_fi");
            echo pof_translation_status_localization_get_counters_cell("growth_target_fi");

            echo pof_translation_status_localization_get_counters_cell("title_".$lang);
            echo pof_translation_status_localization_get_counters_cell("ingress_".$lang);
            echo pof_translation_status_localization_get_counters_cell("content_".$lang);
            echo pof_translation_status_localization_get_counters_cell("leader_tasks_".$lang);
            echo pof_translation_status_localization_get_counters_cell("growth_target_".$lang);
            ?>

        </tr>
        <tr>
            <td></td>
            <td></td>
            <?php
            echo pof_translation_status_localization_get_counters_cell_pros("ingress");
            echo pof_translation_status_localization_get_counters_cell_pros("content");
            echo pof_translation_status_localization_get_counters_cell_pros("leader_tasks_fi");
            echo pof_translation_status_localization_get_counters_cell_pros("growth_target_fi");

            echo pof_translation_status_localization_get_counters_cell_pros("title_".$lang);
            echo pof_translation_status_localization_get_counters_cell_pros("ingress_".$lang);
            echo pof_translation_status_localization_get_counters_cell_pros("content_".$lang);
            echo pof_translation_status_localization_get_counters_cell_pros("leader_tasks_".$lang);
            echo pof_translation_status_localization_get_counters_cell_pros("growth_target_".$lang);
            ?>

        </tr>
    <?php

	echo "</tbody>";
    echo "</table>";

}

function pof_translation_status_localization_get_counters_cell($field) {
    global $field_counters;
    return '<td class="pof_translation_status_counters">'.$field_counters[$field]->green . " / " . $field_counters[$field]->total."</td>";
}

function pof_translation_status_localization_get_counters_cell_pros($field) {
    global $field_counters;

    $pros = round(($field_counters[$field]->green  /  $field_counters[$field]->total * 100), 2);

    return '<td class="pof_translation_status_counters">'. $pros." %</td>";
}

$field_counters = array();

function pof_translation_status_localization_get_field_cell($field, $post_id) {

    global $field_counters;

    if (!array_key_exists($field, $field_counters)) {
        $field_counters[$field] = new stdClass();
        $field_counters[$field]->total = 0;
        $field_counters[$field]->green = 0;
    }

    $field_counters[$field]->total++;

    $content = get_field($field,$post_id);

    $class = "pof_translation_status_black";

    if (strlen($content) > 3) {
        $class = "pof_translation_status_green";
        $field_counters[$field]->green++;
    }

    $word_count = str_word_count(strip_tags($content));

    ?>
    <td class="<?php echo $class; ?>"><?php echo $word_count; ?></td>
    <?php
}

function pof_translation_status_localization_get_content_cell($content, $post_id) {

    global $field_counters;

    if (!array_key_exists('content', $field_counters)) {
        $field_counters['content'] = new stdClass();
        $field_counters['content']->total = 0;
        $field_counters['content']->green = 0;
    }

    $field_counters['content']->total++;

    $class = "pof_translation_status_black";

    if (strlen($content) > 3) {
        $class = "pof_translation_status_green";
        $field_counters['content']->green ++;
    }

    $word_count = str_word_count(strip_tags($content));

    ?>
    <td class="<?php echo $class; ?>"><?php echo $word_count; ?></td>
    <?php
}


function pof_translation_status_localization_get_form() {
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