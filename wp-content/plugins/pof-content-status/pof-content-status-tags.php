<?php
function pof_content_status_tags() {
	if ( !current_user_can( 'pof_manage_status' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Tagit</h1>';

    echo pof_content_status_tags_get_form();

	echo '</div>';

	if (   isset($_POST)
        && isset($_POST["pof_taxonomy"])) {
	    echo '<div class="wrap">';

        echo pof_content_status_tags_get_sub_form($_POST["pof_taxonomy"]);

        if (isset($_POST["pof_tag"])) {
            echo pof_content_status_tags_get_content($_POST["pof_taxonomy"], $_POST["pof_tag"]);
        }

    	echo '</div>';
    }

}

function pof_content_status_tags_get_tag_options() {
    $options = array();

    $options["place_of_performance"] = new stdClass();
    $options["place_of_performance"]->title = "Suorituspaikat";
    $options["place_of_performance"]->type = "multiselect";
    $options["place_of_performance"]->types = array('task', 'taskgroup');

    $options["groupsize"] = new stdClass();
    $options["groupsize"]->title = "Ryhm&auml;koot";
    $options["groupsize"]->type = "multiselect";
    $options["groupsize"]->types = array('task');

    $options["mandatory"] = new stdClass();
    $options["mandatory"]->title = "Pakollinen";
    $options["mandatory"]->type = "boolean";
    $options["mandatory"]->types = array('task', 'taskgroup');
    $options["mandatory"]->options = array('mandatory', 'mandatory_seascouts');

    $options["taskduration"] = new stdClass();
    $options["taskduration"]->title = "Aktiviteetin kestot";
    $options["taskduration"]->type = "select";
    $options["taskduration"]->types = array('task');
    $options["taskduration"]->meta_key = "duration";

    $options["taskpreaparationduration"] = new stdClass();
    $options["taskpreaparationduration"]->title = "Aktiviteetin valmistelun kestot";
    $options["taskpreaparationduration"]->type = "select";
    $options["taskpreaparationduration"]->types = array('task');
    $options["taskpreaparationduration"]->meta_key = "preparationduration";


    return $options;
}


function pof_content_status_tags_get_form() {

    $options = pof_content_status_tags_get_tag_options();

    $ret = "";

    $ret .= "<form method=\"POST\">";

    $selected_taxonomy = '';

    if (   isset($_POST)
        && isset($_POST["pof_taxonomy"])) {
        $selected_taxonomy = $_POST["pof_taxonomy"];
    }

    $ret .= "Valitse taxonomia: <br />";
    $ret .= '<select name="pof_taxonomy">';

    foreach ($options as $option_key => $option_value) {
        if ($selected_taxonomy == $option_key) {
            $ret .= "<option selected=\"selected\" value=\"".$option_key."\">".$option_value->title."</option>\n";
        } else {
            $ret .= "<option value=\"".$option_key."\">".$option_value->title."</option>\n";
        }
    }

    $ret .= '</select>';

	$ret .= '<input type="submit" name="Submit" value="Vaihda" />';

    $ret .= "</form>";

    return $ret;

}

function pof_content_status_tag_count($taxonomy, $tag)
{
    $options = pof_content_status_tags_get_tag_options();

    $option = $options[$taxonomy];

    $meta_key = $taxonomy;
    if (!empty($option->meta_key)) {
        $meta_key = $option->meta_key;
    }

    global $wpdb;
    $res = array();



    if ($option->type == 'boolean') {
        $query = "
		SELECT COUNT(*) AS counter
        FROM wp_posts posts
            JOIN wp_postmeta meta ON posts.ID = meta.post_id
        WHERE posts.post_status NOT IN ('trash', 'inherit')";

        $query_types = "";
        $query_boolean = "";
        if (strstr($tag, 'NON_')) {
            $query_boolean .=  "
                AND meta.meta_value = '0'
		    ";
        }
        else {
            $query_boolean .=  "
            AND meta.meta_value = '1'
		";
        }


        foreach ($option->types as $type) {
            if ($query_types == "") {
                $query_types = "(meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$query_boolean.")";
            } else {
                $query_types .= " OR (meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$query_boolean.")";
            }
        }


        $query .= "AND (".$query_types.") ";


    } else {
        $query = "
		SELECT COUNT(*) AS counter
        FROM wp_posts posts
            JOIN wp_postmeta meta ON posts.ID = meta.post_id
        WHERE posts.post_status NOT IN ('trash', 'inherit')";

        $query_types = "";

        $meta_value_query = "";
        if ($option->type == "multiselect") {
            $meta_value_query = "AND meta.meta_value LIKE '%\"".$tag."\"%'";
        } else {
            $meta_value_query = "AND meta.meta_value = '".$tag."'";
        }

        foreach ($option->types as $type) {
            if ($query_types == "") {
                $query_types = "(meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$meta_value_query.")";
            } else {
                $query_types .= " OR (meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$meta_value_query.")";
            }
        }

        $query .= "AND (".$query_types.") ";


    }



    $res = $wpdb->get_results($query);

    return $res[0]->counter;
}

function pof_content_status_tag_posts($taxonomy, $tag)
{
    $options = pof_content_status_tags_get_tag_options();

    $option = $options[$taxonomy];

    $meta_key = $taxonomy;
    if (!empty($option->meta_key)) {
        $meta_key = $option->meta_key;
    }

    global $wpdb;
    $res = array();

    if ($option->type == 'boolean') {
        $query = "
		SELECT posts.ID, posts.post_title, posts.post_status, posts.post_type
        FROM wp_posts posts
            JOIN wp_postmeta meta ON posts.ID = meta.post_id
        WHERE posts.post_status NOT IN ('trash', 'inherit')";

        $query_types = "";
        $query_boolean = "";

        if (strstr($tag, 'NON_')) {
            $query_boolean .=  "
                AND meta.meta_value = '0'
		    ";
        }
        else {
            $query_boolean .=  "
            AND meta.meta_value = '1'
		";
        }


        foreach ($option->types as $type) {
            if ($query_types == "") {
                $query_types = "(meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$query_boolean.")";
            } else {
                $query_types .= " OR (meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$query_boolean.")";
            }
        }


        $query .= "AND (".$query_types.") ";



        $query .=  " GROUP BY posts.ID, posts.post_title, posts.post_status, posts.post_type ORDER BY posts.post_type, posts.post_title";

        echo $query;
    }
    else {
        $query = "
		SELECT posts.ID, posts.post_title, posts.post_status, posts.post_type
        FROM wp_posts posts
            JOIN wp_postmeta meta ON posts.ID = meta.post_id
        WHERE posts.post_status NOT IN ('trash', 'inherit')";

        $query_types = "";

        $meta_value_query = "";
        if ($option->type == "multiselect") {
            $meta_value_query = "AND meta.meta_value LIKE '%\"".$tag."\"%'";
        } else {
            $meta_value_query = "AND meta.meta_value = '".$tag."'";
        }

        foreach ($option->types as $type) {
            if ($query_types == "") {
                $query_types = "(meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$meta_value_query.")";
            } else {
                $query_types .= " OR (meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$meta_value_query.")";
            }
        }

        $query .= " AND (".$query_types.") ";


        $query .=  " GROUP BY posts.ID, posts.post_title, posts.post_status, posts.post_type ORDER BY posts.post_type, posts.post_title";
    }



    $res = $wpdb->get_results($query);

    return $res;
}

function pof_content_status_tags_get_sub_form($selected_taxonomy) {

    $taxonomy_options = pof_content_status_tags_get_tag_options();

    $taxonomy_option = $taxonomy_options[$selected_taxonomy];

    $options = pof_taxonomy_translate_get_items_by_taxonomy_base_key($selected_taxonomy);

    $ret = "";

    $ret .= "<form method=\"POST\">";
    $ret .= "<input type=\"hidden\" name=\"pof_taxonomy\" value=\"".$selected_taxonomy."\" />";
    $selected_tag = '';

    if (   isset($_POST)
        && isset($_POST["pof_tag"])) {
        $selected_tag = $_POST["pof_tag"];
    }

    $ret .= "Valitse tagi: <br />";
    $ret .= '<select name="pof_tag">';
    if ($taxonomy_option->type == 'boolean')
    {
        foreach ($options as $option_key => $option_value) {

            if (in_array($option_key, $taxonomy_option->options)) {
                $count = pof_content_status_tag_count($selected_taxonomy, $option_key);

                $non_count = pof_content_status_tag_count($selected_taxonomy, 'NON_' . $option_key);


                if ($selected_tag == $option_key) {
                    $ret .= "<option selected=\"selected\" value=\"".$option_key."\">".$option_value." (".$option_key.") (".$count.")</option>\n";
                } else {
                    $ret .= "<option value=\"".$option_key."\">".$option_value." (".$option_key.") (".$count.")</option>\n";
                }

                if ($selected_tag == 'NON_' . $option_key) {
                    $ret .= "<option selected=\"selected\" value=\"".'NON_' . $option_key."\">!".$option_value." (".'NON_' . $option_key.") (".$non_count.")</option>\n";
                } else {
                    $ret .= "<option value=\"".'NON_' .$option_key."\">!".$option_value." (".'NON_' . $option_key.") (".$non_count.")</option>\n";
                }
            }
        }
    }
    else
    {
        foreach ($options as $option_key => $option_value) {

            $count = pof_content_status_tag_count($selected_taxonomy, $option_key);

            if ($selected_tag == $option_key) {
                $ret .= "<option selected=\"selected\" value=\"".$option_key."\">".$option_value." (".$option_key.") (".$count.")</option>\n";
            } else {
                $ret .= "<option value=\"".$option_key."\">".$option_value." (".$option_key.") (".$count.")</option>\n";
            }
        }
    }

    $ret .= '</select>';

	$ret .= '<input type="submit" name="Submit" value="Vaihda" />';

    $ret .= "</form>";

    return $ret;

}

function pof_content_status_tags_get_content($selected_taxonomy, $selected_tag) {

    $ret = '';

    $items = pof_content_status_tag_posts($selected_taxonomy, $selected_tag);

    if (count($items) > 0) {
        $ret .=  "<table>";
        $ret .=  "<thead>";
        $ret .=  "<tr>";
        $ret .=  "<th>Title</th>";
        $ret .=  "<th>Status</th>";
        $ret .=  "<th>Type</th>";
        $ret .=  "</tr>";
        $ret .=  "</thead>";
        $ret .=  "<tbody>";

        foreach ($items as $item) {

            $ret .=  "<tr>";
            $ret .=  "<td><a href=\"/wp-admin/post.php?post=" . $item->ID . "&action=edit\" target=\"_blank\">".$item->post_title."</a></td>";
            $ret .=  "<th>".$item->post_status."</th>";
            $ret .=  "<th>".$item->post_type."</th>";
            $ret .=  "</tr>";
        }
        $ret .=  "</tbody>";
        $ret .=  "</table>";
    } else {
        $ret .=  "No items";
    }




    return $ret;

}