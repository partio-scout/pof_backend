<?php


function pof_translation_status_generic() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Yleiset status</h1>';

    echo pof_translation_status_generic_get_form();

	echo '</div>';

	if (   isset($_POST)
		&& isset($_POST["agegroup"])) {
	    echo '<div class="wrap">';
        pof_translation_status_generic_get_content($_POST["agegroup"]);
    	echo '</div>';
    }

}

function pof_translation_status_generic_get_form() {
    $ret = "";

    
    

    $ret .= "<form method=\"POST\">";
    
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

function pof_translation_status_generic_get_content($agegroup_id) {
    
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

<?php
    $langs = pof_settings_get_all_languages();
    $langs_count = count($langs);
    
?>

    <table cellpadding="1" cellspacing="1" border="1" id="pof_translation_status_table">
        <thead>
        <tr>
            <th colspan="2"></th>
            <th rowspan="2">Vapaavalintaisten<br />lukum&auml;&auml;r&auml;</th>
            <th colspan="2">Yl&auml;k&auml;site</th>
            <th colspan="<?php echo $langs_count; ?>">Vinkit</th>
        </tr>
            <tr>
                <th>Tyyppi</th>
                <th>Otsikko</th>
                <th>Paketti</th>
                <th>Suorite</th>
                <th>Taitoalueet</th>
                <th>Tarvikkeet</th>
                <th>Pakollinen</th>
                <th>Ryhm&auml;koko</th>
                <th>Paikka</th>
                <th>Kesto</th>
                <th>Valmistelun kesto</th>
                <th>Taso</th>
                <?php
            foreach ($langs as $lang) {
                ?>
                <th><?php echo  $lang->lang_title; ?></th>

             
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
                <td><?php echo "<a href=\"/wp-admin/post.php?post=" . $the_query->post->ID . "&action=edit\" target=\"_blank\">" . $the_query->post->post_title . "</a>"; ?></td>
                <td><?php echo get_field("taskgroup_additional_tasks_count", $the_query->post->ID); ?></td>
                <td><?php echo get_field("task_task_term", $the_query->post->ID); ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <?php
            foreach ($langs as $lang) {
                echo "<td></td>";
            }
                ?>
            </tr>

            <?php
            pof_translation_status_generic_content_get_taskgroups($the_query->post->ID, 1);
		}
	}
    wp_reset_query();

            ?>
        <tr>
            <td colspan="5"></td>
            <?php
    echo pof_translation_status_get_counters_cell("pof_tax_skillarea");
    echo pof_translation_status_get_counters_cell("pof_tax_equipment");
    echo pof_translation_status_get_counters_cell("task_mandatory");

            ?>

        </tr>
        <tr>
            <td colspan="5"></td>
            <?php
    echo pof_translation_status_get_counters_cell_pros("pof_tax_skillarea");
    echo pof_translation_status_get_counters_cell_pros("pof_tax_equipment");
    echo pof_translation_status_get_counters_cell_pros("task_mandatory");

            ?>
        </tr>
    <?php

	echo "</tbody>";
    echo "</table>";

}

function pof_translation_status_generic_content_get_taskgroups($taskgroup_id, $indentation = 0) {
    $langs = pof_settings_get_all_languages();
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
                <td><?php echo get_field("taskgroup_additional_tasks_count", $the_query->post->ID); ?></td>
                <td><?php echo get_field("task_task_term", $the_query->post->ID); ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <?php
                foreach ($langs as $lang) {
                    echo "<td></td>";
                }
                    ?>
            </tr>

            <?php
            pof_translation_status_generic_content_get_taskgroups($the_query->post->ID, $indentation + 1);
            pof_translation_status_generic_content_get_tasks($the_query->post->ID, $indentation + 1);
		}
	}

    wp_reset_query();
}


function pof_translation_status_generic_content_get_tasks($taskgroup_id, $indentation = 0) {

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
                <td></td>
                <td></td>
                <td><?php echo get_field("task_task_term", $the_query->post->ID); ?></td>
                <?php pof_translation_status_get_tag_count_cell("pof_tax_skillarea", $the_query->post->ID); ?>
                <?php pof_translation_status_get_tag_count_cell("pof_tax_equipment", $the_query->post->ID); ?>
                <?php pof_translation_status_get_checkbox_cell("task_mandatory", $the_query->post->ID); ?>
                <?php pof_translation_status_get_field_count_cell("task_groupsize", $the_query->post->ID); ?>
                <?php pof_translation_status_get_field_count_cell("task_place_of_performance", $the_query->post->ID); ?>
                <td><?php echo get_field("task_duration", $the_query->post->ID); ?></td>
                <td><?php echo get_field("task_preparationduration", $the_query->post->ID); ?></td>
                <td><?php echo get_field("task_level", $the_query->post->ID); ?></td>
                <?php pof_translation_status_get_suggestions($the_query->post->ID); ?>
            </tr>

            <?php
		}
	}

    wp_reset_query();
}

function pof_translation_status_get_suggestions($post_id) {

    $tmp = array();

    $args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_suggestion',
		'meta_key' => 'pof_suggestion_task',
		'meta_value' => $post_id
	);

	$the_query_suggestion = new WP_Query( $args );

	if( $the_query_suggestion->have_posts() ) {
		while ( $the_query_suggestion->have_posts() ) {
			$the_query_suggestion->the_post();
            $lang = get_field("pof_suggestion_lang", $the_query_suggestion->post->ID);
            if (!array_key_exists($lang, $tmp)) {
                $tmp[$lang] = 1;
            } else {
                $tmp[$lang]++;
            }

		}
	}

    $langs = pof_settings_get_all_languages();

    foreach ($langs as $lang) {
        echo "<td>";
        if (array_key_exists($lang->lang_code, $tmp)) {
            echo $tmp[$lang->lang_code];
        }
        echo "</td>";
    }

}

function pof_translation_status_get_tag_count_cell($taxonomy, $post_id) {
    $tags = wp_get_post_terms($post_id, $taxonomy);
    $count = count($tags);

    global $field_counters;

    if (!array_key_exists($taxonomy, $field_counters)) {
        $field_counters[$taxonomy] = new stdClass();
        $field_counters[$taxonomy]->total = 0;
        $field_counters[$taxonomy]->green = 0;
    }
    $field_counters[$taxonomy]->total++;

    $class = "pof_translation_status_black";

    if ($count > 0) {
        $class = "pof_translation_status_green";
        $field_counters[$taxonomy]->green++;
    }

     ?>
    <td class="<?php echo $class; ?>"><?php echo $count; ?></td>
    <?php
}

function pof_translation_status_get_field_count_cell($field, $post_id) {
    $res = get_field($field, $post_id);
    
    $count = count($res);

    global $field_counters;

    if (!array_key_exists($taxonomy, $field_counters)) {
        $field_counters[$taxonomy] = new stdClass();
        $field_counters[$taxonomy]->total = 0;
        $field_counters[$taxonomy]->green = 0;
    }
    $field_counters[$taxonomy]->total++;

    $class = "pof_translation_status_black";

    if ($count > 0) {
        $class = "pof_translation_status_green";
        $field_counters[$taxonomy]->green++;
    }

    ?>
    <td class="<?php echo $class; ?>"><?php echo $count; ?></td>
    <?php
}

function pof_translation_status_get_checkbox_cell($taxonomy, $post_id) {
    global $field_counters;

    if (!array_key_exists($taxonomy, $field_counters)) {
        $field_counters[$taxonomy] = new stdClass();
        $field_counters[$taxonomy]->total = 0;
        $field_counters[$taxonomy]->green = 0;
    }
    $field_counters[$taxonomy]->total++;

    $class = "pof_translation_status_black";
    if (get_field($taxonomy, $post_id)) {
        $class = "pof_translation_status_green";
        $field_counters[$taxonomy]->green++;
    }

    ?>
    <td class="<?php echo $class; ?>"><?php echo get_field($taxonomy, $post_id); ?></td>
    <?php
}

function pof_translation_status_get_counters_cell($field) {
    global $field_counters;
    return '<td class="pof_translation_status_counters">'.$field_counters[$field]->green . " / " . $field_counters[$field]->total."</td>";
}

function pof_translation_status_get_counters_cell_pros($field) {
    global $field_counters;

    $pros = round(($field_counters[$field]->green  /  $field_counters[$field]->total * 100), 2);

    return '<td class="pof_translation_status_counters">'. $pros." %</td>";
}

$field_counters = array();

function pof_translation_status_get_field_cell($field, $post_id) {

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

function pof_translation_status_get_content_cell($content, $post_id) {

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


