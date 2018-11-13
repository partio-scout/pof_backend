<?php
/*
 * Checks if any taskgroups are creating infinite loops i.e. taskgroup has itself as a parent taskgroup
 */
function pof_content_status_infinites() {
	if ( !current_user_can( 'pof_manage_status' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Ikuiset loopit</h1>';

  $args = array(
  	'numberposts' => -1,
  	'posts_per_page' => -1,
  	'post_type' => array( 'pof_post_taskgroup' ),
  );

  $taskgroups = get_posts( $args );
  $has_infinites = false;

  foreach($taskgroups as $taskgroup) {
    $parent_taskgroup = get_field( 'suoritepaketti', $taskgroup->ID);
    if( isset($parent_taskgroup) ) {
      if($parent_taskgroup->ID == $taskgroup->ID) {
        if( isset( $_POST['fix-infinites'] ) ) {
          update_field( 'suoritepaketti', '', $taskgroup->ID);
        } else {
          $has_infinites = true;
          echo '<a href="/wp-admin/post.php?post=' . $taskgroup->ID . '&action=edit" target="_blank">' . $taskgroup->post_title . '</a>';
        }
      }
    }
  }

  if( $has_infinites ) {
    echo '<br><br>';
    echo '<form method="POST"><input type="submit" name="fix-infinites" id="submit" class="button button-primary" value="Korjaa"></form>';
  } else {
    echo 'Ei aktiviteettipaketteja';
  }

	echo '</div>';
}