<?php
/**
 * The template used for displaying page content
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		// Post thumbnail.
		twentyfifteen_post_thumbnail();
	?>

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<p class="ingress" style="font-style: italic;">
		<?php
			echo get_post_meta($post->ID, "ingress", true);
		?>
		</p>

		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfifteen' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) );
		?>

		<h3>Vapaavalinnaisten lukum&auml;&auml;r&auml;</h3>
		<?php
		echo get_post_meta($post->ID, "taskgroup_additional_tasks_count", true);
		?>

		<h3>Suoriteiden yl&auml;k&auml;site</h3>
		<?php
		echo get_post_meta($post->ID, "taskgroup_subtask_term", true);
		?>

		<h3>Suoritepakettien yl&auml;k&auml;site</h3>
		<?php
		echo get_post_meta($post->ID, "taskgroup_subtaskgroup_term", true);
		?>

		<h3>Suoritepaketin yl&auml;k&auml;site</h3>
		<?php
		echo get_post_meta($post->ID, "taskgroup_taskgroup_term", true);
		?>

	

	</div><!-- .entry-content -->

	<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>

</article><!-- #post-## -->
