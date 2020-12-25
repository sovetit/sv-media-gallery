<?php defined( 'ABSPATH' ) || exit;
get_header();
$description = get_the_archive_description() ?>
<?php if ( have_posts() ) : ?>

	<header class="page-header">
		<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
		<?php if ( $description ) : ?>
			<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
		<?php endif; ?>
	</header><!-- .page-header -->

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="entry-header">
				<?php
				the_title( sprintf( '<h2 class="entry-title default-max-width"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' );
				?>
			</header><!-- .entry-header -->

		</article>
	<?php endwhile; ?>

	<?php the_posts_pagination(); ?>

<?php endif; ?>
<?php get_footer();
