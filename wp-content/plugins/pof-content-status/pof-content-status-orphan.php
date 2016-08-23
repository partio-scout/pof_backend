<?php
function pof_content_status_orphan() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Orvot</h1>';

    echo pof_content_status_orphan_get_form();

	echo '</div>';

	if (   isset($_POST)
        && isset($_POST["pof_post_type"])) {
	    echo '<div class="wrap">';
        pof_content_status_orphan_get_content($_POST["pof_post_type"]);
    	echo '</div>';
    }

}

function pof_content_status_orphan_get_form() {
    $ret = "";

    $ret .= "<form method=\"POST\">";

    $selected_post_type = '';

    if (   isset($_POST)
        && isset($_POST["pof_post_type"])) {
        $selected_post_type = $_POST["pof_post_type"];
    }

    $ret .= "Valitse tyyppi: <br />";
    $ret .= '<select name="pof_post_type">';
    if ($selected_post_type == 'pof_post_agegroup') {
        $ret .= "<option selected=\"selected\" value=\"pof_post_agegroup\">Ik&auml;ryhm&auml;</option>\n";
    } else {
        $ret .= "<option value=\"pof_post_agegroup\">Ik&auml;ryhm&auml;</option>\n";
    }

    if ($selected_post_type == 'pof_post_taskgroup') {
        $ret .= "<option selected=\"selected\" value=\"pof_post_taskgroup\">Aktiviteettipaketti</option>\n";
    } else {
        $ret .= "<option value=\"pof_post_taskgroup\">Aktiviteettipaketti</option>\n";
    }

    if ($selected_post_type == 'pof_post_task') {
        $ret .= "<option selected=\"selected\" value=\"pof_post_task\">Aktiviteetti</option>\n";
    } else {
        $ret .= "<option value=\"pof_post_task\">Aktiviteetti</option>\n";
    }

    if ($selected_post_type == 'pof_post_suggestion') {
        $ret .= "<option selected=\"selected\" value=\"pof_post_suggestion\">Vinkki</option>\n";
    } else {
        $ret .= "<option value=\"pof_post_suggestion\">Vinkki</option>\n";
    }

    $ret .= '</select>';

	$ret .= '<br /><br /><input type="submit" name="Submit" value="N&auml;yt&auml;" />';

    $ret .= "</form>";

    return $ret;

}

function pof_content_status_orphan_get_content($pof_post_type) {
    global $wpdb;
    $res = array();

    $res2 = array();

    $res3 = array();

    $res4 = array();


    switch ($pof_post_type) {
        case "pof_post_agegroup":

            $res = $wpdb->get_results(
                        "
			    SELECT wp_postmeta.meta_value, wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                FROM wp_postmeta
                JOIN wp_posts
                    ON wp_postmeta.post_id = wp_posts.ID
                WHERE wp_postmeta.meta_key = 'suoritusohjelma'
                    AND wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_agegroup'
                    AND wp_postmeta.meta_value = 'null'
                ORDER BY wp_posts.post_type, wp_posts.ID;
			    "
		    );

            $res2 = $wpdb->get_results(
                        "
			    SELECT wp_postmeta.meta_value, childpost.ID, childpost.post_type, childpost.post_name, childpost.post_title, parentpost.post_title
                FROM wp_postmeta
                JOIN wp_posts AS childpost
                    ON wp_postmeta.post_id = childpost.ID
				JOIN wp_posts AS parentpost
                    ON wp_postmeta.meta_value = parentpost.ID
                WHERE wp_postmeta.meta_key = 'suoritusohjelma'
                    AND childpost.post_status = 'publish'
                    AND childpost.post_type = 'pof_post_agegroup'
                    AND wp_postmeta.meta_value <> 'null'
					AND parentpost.post_status = 'trash'
                ORDER BY childpost.post_type, childpost.ID;
			    "
		    );

            $res3 = $wpdb->get_results(
                        "
			    SELECT wp_postmeta.meta_value, childpost.ID, childpost.post_type, childpost.post_name, childpost.post_title, parentpost.post_title
                FROM wp_postmeta
                JOIN wp_posts AS childpost
                    ON wp_postmeta.post_id = childpost.ID
				LEFT JOIN wp_posts AS parentpost
                    ON wp_postmeta.meta_value = parentpost.ID
                WHERE wp_postmeta.meta_key = 'suoritusohjelma'
                    AND childpost.post_status = 'publish'
                    AND childpost.post_type = 'pof_post_agegroup'
                    AND wp_postmeta.meta_value <> 'null'
                    AND parentpost.ID IS NULL
                ORDER BY childpost.post_type, childpost.ID;
			    "
		    );

            $res4 = $wpdb->get_results(
                        "
			    SELECT wp_postmeta.meta_value, childpost.ID, childpost.post_type, childpost.post_name, childpost.post_title, parentpost.post_title
                FROM wp_postmeta
                JOIN wp_posts AS childpost
                    ON wp_postmeta.post_id = childpost.ID
				JOIN wp_posts AS parentpost
                    ON wp_postmeta.meta_value = parentpost.ID
                WHERE wp_postmeta.meta_key = 'suoritusohjelma'
                    AND childpost.post_status = 'publish'
                    AND childpost.post_type = 'pof_post_agegroup'
                    AND wp_postmeta.meta_value <> 'null'
					AND (parentpost.post_status = 'draft' OR parentpost.post_status = 'pending')
                ORDER BY childpost.post_type, childpost.ID;
			    "
		    );

            break;
        case "pof_post_taskgroup":

            $res = $wpdb->get_results(
                        "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'ikakausi'
                        JOIN wp_postmeta as wppostmeta2
                            ON wppostmeta2.post_id = wp_posts.ID AND wppostmeta2.meta_key = 'suoritepaketti'
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'
                    AND wppostmeta1.meta_value = 'null'
                    AND wppostmeta2.meta_value = 'null';
			    "
		    );

            $res2 = $wpdb->get_results(
                        "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'ikakausi' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND parentpost.post_status = 'trash'
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'

                UNION

                SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'suoritepaketti' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND parentpost.post_status = 'trash'
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'
;
			    "
		    );

            $res3 = $wpdb->get_results(
                       "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title, parentpost.ID as parentpostid
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'ikakausi' AND wppostmeta1.meta_value <> 'null'
                    	LEFT JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'
                    AND parentpost.ID IS NULL

                UNION

                SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title, parentpost.ID as wppostmeta1
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'suoritepaketti' AND wppostmeta1.meta_value <> 'null'
                    	LEFT JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'
                    AND parentpost.ID IS NULL
;
			    "
           );

            $res4 = $wpdb->get_results(
                       "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'ikakausi' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND (parentpost.post_status = 'draft' OR parentpost.post_status = 'pending')
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'

                UNION

                SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'suoritepaketti' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND (parentpost.post_status = 'draft' OR parentpost.post_status = 'pending')
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_taskgroup'
;
			    "
           );

            break;

        case "pof_post_task":

            $res = $wpdb->get_results(
                        "
			    SELECT wp_postmeta.meta_value, wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                FROM wp_postmeta
                JOIN wp_posts
                    ON wp_postmeta.post_id = wp_posts.ID
                WHERE wp_postmeta.meta_key = 'suoritepaketti'
                    AND wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_task'
                    AND wp_postmeta.meta_value = 'null'
                ORDER BY wp_posts.post_type, wp_posts.ID;
			    "
		    );

            $res2 = $wpdb->get_results(
                        "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'suoritepaketti' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND parentpost.post_status = 'trash'
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_task';

			    "
		    );

            $res3 = $wpdb->get_results(
                       "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title, parentpost.ID as parentpostid
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'suoritepaketti' AND wppostmeta1.meta_value <> 'null'
                    	LEFT JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_task'
                    AND parentpost.ID IS NULL;

			    "
           );

            $res4 = $wpdb->get_results(
                       "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'suoritepaketti' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND (parentpost.post_status = 'draft' OR parentpost.post_status = 'pending')
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_task';

			    "
           );

            break;
        case "pof_post_suggestion":
            $res = $wpdb->get_results(
                        "
			    SELECT wp_postmeta.meta_value, wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                FROM wp_postmeta
                JOIN wp_posts
                    ON wp_postmeta.post_id = wp_posts.ID
                WHERE wp_postmeta.meta_key = 'pof_suggestion_task'
                    AND wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_suggestion'
                    AND wp_postmeta.meta_value = 'null'
                ORDER BY wp_posts.post_type, wp_posts.ID;
			    "
		    );

            $res2 = $wpdb->get_results(
                        "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'pof_suggestion_task' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND parentpost.post_status = 'trash'
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_suggestion';

			    "
		    );

            $res3 = $wpdb->get_results(
                       "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title, parentpost.ID as parentpostid
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'pof_suggestion_task' AND wppostmeta1.meta_value <> 'null'
                    	LEFT JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_suggestion'
                    AND parentpost.ID IS NULL;

			    "
           );

            $res4 = $wpdb->get_results(
                       "
			    SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_name, wp_posts.post_title
                    FROM wp_posts
                        JOIN wp_postmeta as wppostmeta1
                            ON wppostmeta1.post_id = wp_posts.ID AND wppostmeta1.meta_key = 'pof_suggestion_task' AND wppostmeta1.meta_value <> 'null'
                    	JOIN wp_posts AS parentpost
                    		ON wppostmeta1.meta_value = parentpost.ID AND (parentpost.post_status = 'draft' OR parentpost.post_status = 'pending')
                WHERE  wp_posts.post_status = 'publish'
                    AND wp_posts.post_type = 'pof_post_suggestion';

			    "
           );

            break;
    }



    if (count($res) || count($res2) || count($res3) || count($res4)) {
        echo '<ul>';

        foreach ($res as $item)
        {
            echo "<li><span class=\"dashicons dashicons-star-empty\"></span><a href=\"/wp-admin/post.php?post=" . $item->ID . "&action=edit\" target=\"_blank\">".$item->post_title."</a></li>";
        }

        foreach ($res2 as $item)
        {
            echo "<li><span class=\"dashicons dashicons-trash\"></span><a href=\"/wp-admin/post.php?post=" . $item->ID . "&action=edit\" target=\"_blank\">".$item->post_title."</a></li>";
        }


        foreach ($res3 as $item)
        {
            echo "<li><span class=\"dashicons dashicons-editor-removeformatting\"></span><a href=\"/wp-admin/post.php?post=" . $item->ID . "&action=edit\" target=\"_blank\">".$item->post_title."</a></li>";
        }

        foreach ($res4 as $item)
        {
            echo "<li><span class=\"dashicons dashicons-hidden\"></span><a href=\"/wp-admin/post.php?post=" . $item->ID . "&action=edit\" target=\"_blank\">".$item->post_title."</a></li>";
        }
        echo '</ul>';

        echo "<span class=\"dashicons dashicons-star-empty\"></span> => Ei parenttia m&auml;&auml;ritetty<br />";
        echo "<span class=\"dashicons dashicons-trash\"></span> => Parentti roskakorissa.<br />";
        echo "<span class=\"dashicons dashicons-editor-removeformatting\"></span> => Parent kokonaan poistettu<br />";
        echo "<span class=\"dashicons dashicons-hidden\"></span> => Parent on luonnos, eli piiloitettu<br />";


    } else {
        echo "Ei orpoja";
    }


}