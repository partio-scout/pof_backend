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

		<h3>Johtajan teht&auml;v&auml;t</h3>
		<p>
		<?php
			echo get_post_meta($post->ID, "leader_tasks_fi", true);
		?>
		</p>

		<h3>Kasvatustavoite</h3>
		<p>

		<?php
			echo get_post_meta($post->ID, "growth_target_fi", true);
		?>
		</p>

		<h3>Pakollinen</h3>
		<p>

		<?php
			echo get_post_meta($post->ID, "task_mandatory", true);
		?>
		</p>

		<h3>Pakollinen meripartiolaisille</h3>
		<p>

		<?php
			echo get_post_meta($post->ID, "task_mandatory_seascouts", true);
		?>
		</p>

		<h3>Ryhm&auml;koko</h3>
		<p>

		<?php

			$groupsize = get_post_meta($post->ID, "task_groupsize", false);
			if (count($groupsize) > 0) {
				foreach ($groupsize[0] as $tmp) {
					$foo = pof_taxonomy_translate_get_translation('groupsize', $tmp, 0, 'fi', true);
					if (isset($foo[0]->content)) {
						echo $foo[0]->content;
					} else {
						echo $tmp;
					}

					echo ", ";
				}
			}

		?>
		</p>

		<h3>Paikka</h3>
		<p>

		<?php

			$groupsize = get_post_meta($post->ID, "task_place_of_performance", false);
			if (count($groupsize) > 0) {
				foreach ($groupsize[0] as $tmp) {
					$foo = pof_taxonomy_translate_get_translation('place_of_performance', $tmp, 0, 'fi', true);
					if (isset($foo[0]->content)) {
						echo $foo[0]->content;
					} else {
						echo $tmp;
					}

					echo ", ";
				}
			}

		?>
		</p>

		<h3>Kesto</h3>
		<p>

		<?php

			$groupsize = get_post_meta($post->ID, "task_duration", false);
			if (count($groupsize) > 0) {
				foreach ($groupsize as $tmp) {
					$foo = pof_taxonomy_translate_get_translation('taskduration', $tmp, 0, 'fi', true);
					if (isset($foo[0]->content)) {
						echo $foo[0]->content;
					} else {
						echo $tmp;
					}

					echo ", ";

				}
			}

		?>
		</p>

		<h3>Suorituksen yl&auml;k&auml;site</h3>
		<?php
		echo get_post_meta($post->ID, "task_task_term", true);
		?>


	</div><!-- .entry-content -->

	<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>

</article><!-- #post-## -->
