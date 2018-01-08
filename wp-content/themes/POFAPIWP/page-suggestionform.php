<?php
/*
Template Name: Suggestion form
*/

$domains = pof_settings_get_suggestions_allowed_domains();
$http_origin = $_SERVER['HTTP_ORIGIN'];

foreach ($domains as $domain) {
    if (strlen(trim($domain)) > 0 && $domain = $http_origin) {
        header('Access-Control-Allow-Origin: '.$domain);
    }
}

$lang_key = 'fi';
$partio_id = '';
$post_guid = '';
$parent_post = null;
$parent_post_id = 0;
$parent_post_title = "";

if (   $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST)
    && array_key_exists('suggestion_name', $_POST)
    && $_POST['suggestion_name'] != ""
    && array_key_exists('suggestion_content', $_POST)
    && $_POST['suggestion_content'] != "" ) {

    if (array_key_exists('lang', $_POST) && $_POST['lang'] != "") {
        $lang_key = strtolower($_POST['lang']);
    }
    if (array_key_exists('partio_id', $_POST) && $_POST['partio_id'] != "") {
        $partio_id = $_POST['partio_id'];
    }
    if (array_key_exists('post_guid', $_POST) && $_POST['post_guid'] != "") {
        $post_guid = trim($_POST['post_guid']);
    }
    $wp_error = false;

    $suggestion_title = "";

    if (   array_key_exists('suggestion_title', $_POST)
        && trim($_POST['suggestion_title']) != "") {
        $suggestion_title = mb_convert_encoding(trim($_POST['suggestion_title']),"UTF-8", "auto");
    }

    if ($suggestion_title == "") {
        switch ($lang_key) {
            default:
            case "fi":
                $suggestion_title = "Toteutusvinkki";
                break;
            case "sv":
                $suggestion_title = "Tips";
                break;
            case "en":
                $suggestion_title = "Example";
                break;
        }

        if ($post_guid != "") {

            $args = array(
		        'numberposts' => -1,
		        'posts_per_page' => -1,
		        'post_type' => 'any',
		        'meta_key' => 'post_guid',
		        'meta_value' => $post_guid
	        );

            $the_query = new WP_Query( $args );

            if( $the_query->have_posts() ) {
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    $parent_post = $the_query->post;
                    $parent_post_id = $the_query->post->ID;
		        }

                if ($parent_post_id > 0) {

                    $parent_post_title = get_post_meta($parent_post_id, "title_".$lang_key, true );

                    if ($parent_post_title != "")
                    {
                        $suggestion_title .= ": " . $parent_post_title;
                    }
                    else
                    {
                        $suggestion_title .= ": " . $parent_post->post_title;
                    }
                }
	        }
        }
    }

    $wp_error = null;

    $suggestion_content = mb_convert_encoding(trim($_POST['suggestion_content']),"UTF-8", "auto");

    $suggestion = array(
        'post_title'    => $suggestion_title,
        'post_content'  => $suggestion_content,
        'post_type'     => 'pof_post_suggestion',
        'post_status'   => 'draft'
    );
	$suggestion_id = wp_insert_post( $suggestion, $wp_error );

    if ($suggestion_id == 0) {
        $location = "Location: " . $url=strtok($_SERVER["REQUEST_URI"],'?') . "?form_submit=error&lang=" . $lang_key . "&return_val=" . $return_val;

        header($location);
        exit();
    }

    $mypost = false;

    $mypost_id = 0;

    if ($post_guid != '') {

        if ($parent_post_id > 0) {
            update_post_meta($suggestion_id, "pof_suggestion_task", $parent_post_id);
            $mypost_id = $parent_post_id;
        } else {
            $args = array(
	        'numberposts' => -1,
	        'posts_per_page' => -1,
	        'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' ),
	        'meta_key' => 'post_guid',
	        'meta_value' => $post_guid
        );

            $the_query = new WP_Query( $args );

            if( $the_query->have_posts() ) {
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    $mypost = $the_query->post;
                    update_post_meta($suggestion_id, "pof_suggestion_task", $mypost->ID);
                }
            }
        }
    }

    if (array_key_exists('suggestion_file_user', $_FILES)) {

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $uploadedfile = $_FILES['suggestion_file_user'];

        $upload_overrides = array( 'test_form' => false );

        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

        if ( $movefile && !isset( $movefile['error'] ) ) {
            // echo "File is valid, and was successfully uploaded.\n";
            // var_dump( $movefile);
            // $filename should be the path to a file in the upload directory.
            $filename = $movefile['file'];

            // The ID of the post this attachment is for.
            $parent_post_id = $suggestion_id;

            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filetype = wp_check_filetype( basename( $filename ), null );

            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();

            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            // Insert the attachment.
            $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            // Update File Field
//            update_field('pof_suggestion_file_user', $attach_id, $suggestion_id);

            update_post_meta($suggestion_id, "pof_suggestion_file_user", $attach_id);
        }
    }
    update_post_meta($suggestion_id, "pof_suggestion_lang", $lang_key);
    update_post_meta($suggestion_id, "pof_suggestion_writer", mb_convert_encoding(trim($_POST['suggestion_name']),"UTF-8", "auto"));
    update_post_meta($suggestion_id, "pof_suggestion_writer_id", $partio_id);
    update_post_meta($suggestion_id, "pof_suggestion_from_form", 1);
    update_post_meta($suggestion_id, "pof_suggestion_from_form_date", date("Y-m-d H:i:s"));

    $suggestion_guid = get_post_meta( $suggestion_id, "post_guid", true );

    $emails_str = pof_settings_get_suggestions_emails();

    $content = "Uusi vinkki\n\n";
    $content .= "Aktiviteetti: ";
    if ($mypost === false) {

        if ($mypost_id > 0) {
            $mypost = get_post($mypost_id);
            if (!empty($mypost) && $mypost != null && $mypost != false) {
                $content .= $mypost->post_title."\n\n";
            } else {
                $content .= "--"."\n\n";
            }
        }
        else
        {
            $content .= "--"."\n\n";
        }


    } else {
        $lang_title = '';
        if ($lang_key != 'fi') {
            $parent_post_title = get_post_meta( $mypost->ID, "title_".$lang_key, true );
            if (trim($parent_post_title) != "") {
                $lang_title = " (".$parent_post_title.")";
            }
        }
        $content .= $mypost->post_title.$lang_title."\n\n";
    }
    $content .= "Vinkin otsikko: ".$suggestion_title."\n\n";

    $content .= "Vinkin sis�lt�: ".$suggestion_content."\n\n";

    $content .= "Kirjoittaja: ".$_POST['suggestion_name']."\n\n";

    $content .= "Kieli: ".$lang_key."\n\n";

    $content .= "Lue: " . get_site_url()."/wp-admin/post.php?post=".$suggestion_id."&action=edit";

    wp_mail( $emails_str, "[POF] Uusi vinkki", $content, 'From: "' . pof_settings_get_suggestions_email_sender_name() . '" <'.pof_settings_get_suggestions_email_sender_email().'>');

	$return_val = 'json';
	if (array_key_exists('return_val', $_POST)
    && $_POST['return_val'] != "") {
		$return_val = $_POST['return_val'];
	}

    $location = "Location: " . $url=strtok($_SERVER["REQUEST_URI"],'?') . "?form_submit=ok&lang=" . $lang_key . "&return_val=" . $return_val;

    if ($suggestion_guid != "") {
        $location .= "&suggestion_guid=" . $suggestion_guid;
    }

	header($location);
	exit();
//    echo pof_taxonomy_translate_get_translation_content("common", "suggestion_form_done", 0, $lang_key);

}

else if (   $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST)
    && array_key_exists('suggestion_name', $_POST)
    && array_key_exists('suggestion_content', $_POST) ) {
    // Form post, but empty content
    if (   $_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST)) {
        $tmp = new stdClass();
        $tmp->status = "error";
        $tmp->message = pof_taxonomy_translate_get_translation_content("common", "suggestion_form_error_empty", 0, $lang_key);
        header('Content-Type: application/json');
        echo json_encode($tmp);
        exit();
    }
}
else {

    if (array_key_exists('lang', $_GET) && $_GET['lang'] != "") {
        $lang_key = $_GET['lang'];
    }

    if (   array_key_exists('form_submit', $_GET) && $_GET['form_submit'] != ""
        && array_key_exists('return_val', $_GET) && $_GET['return_val'] != "html") {
        $tmp = new stdClass();
        if ($_GET['form_submit'] == "ok") {
            $tmp->status = "ok";
            $tmp->message = pof_taxonomy_translate_get_translation_content("common", "suggestion_form_done", 0, $lang_key);
            if (   array_key_exists('suggestion_guid', $_GET) && $_GET['suggestion_guid'] != "") {
                $tmp->suggestion_guid = $_GET['suggestion_guid'];
            }
        } else {
            $tmp->status = "error";
            $tmp->message = pof_taxonomy_translate_get_translation_content("common", "suggestion_form_error", 0, $lang_key);
        }
        header('Content-Type: application/json');
        echo json_encode($tmp);
        exit();
    }
    else {

        get_header();

?>


	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	            <header class="entry-header">
		            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	            </header><!-- .entry-header -->

			    <div class="entry-content">
                    <?php
                    if (array_key_exists('form_submit', $_GET) && $_GET['form_submit'] != "") {
                        if ($_GET['form_submit'] == "ok") {
                            echo '<h2>' . pof_taxonomy_translate_get_translation_content("common", "suggestion_form_done", 0, $lang_key) . "</h2>";
                        } else {
                            echo '<h2>' . pof_taxonomy_translate_get_translation_content("common", "suggestion_form_error", 0, $lang_key) . "</h2>";
                        }
                    }


                    ?>
                    <form action="" method="POST" class="tips__form" enctype="multipart/form-data">
                        <input type="hidden" name="return_val" value="html" />
                        <br />
                        <input class="radius" type="text" name="suggestion_name" placeholder="<?php echo pof_taxonomy_translate_get_translation_content("common", "suggestion_form_name_placeholder", 0, $lang_key, true); ?> *" aria-label="Name" />
                        <br />
                        <br />
                        <input class="radius" type="text" name="suggestion_title" placeholder="<?php echo pof_taxonomy_translate_get_translation_content("common", "suggestion_form_title_placeholder", 0, $lang_key, true); ?> *" aria-label="Title" />
                        <br />
                        <br />
                        <textarea class="radius form-textarea" name="suggestion_content" placeholder="<?php echo pof_taxonomy_translate_get_translation_content("common", "suggestion_form_content_placeholder", 0, $lang_key, true); ?>"></textarea>
                        <br />
                        <br />
                        <input class="button radius" type="submit" name="submit-tip" value="<?php echo pof_taxonomy_translate_get_translation_content("common", "suggestion_form_sendbutton", 0, $lang_key, true); ?>" aria-label="Send" />

                    </form>
                </div>
            </article>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>

<?php    }
    }
?>