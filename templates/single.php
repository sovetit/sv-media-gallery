<?php defined( 'ABSPATH' ) || exit;
get_header() ?>
<?php while ( have_posts() ) : the_post() ?>
	<article id="single-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>
		<!-- SV Media Gallery -->
		<div class="sv-container">
			<div class="sv-gallery">
				<?php $galleries = carbon_get_post_meta( get_the_ID(), 'photo' ) ?>
				<?php foreach ( $galleries as $gallery ) : ?>
					<a href="<?php echo wp_get_attachment_image_url( $gallery, 'full' ) ?>">
						<img class="sv-gallery-img" src="<?php echo wp_get_attachment_image_url( $gallery, 'sv-gallery' ) ?>" alt="<?php the_title() ?>">
					</a>
				<?php endforeach; ?>
			</div><!-- .sv-gallery -->
		</div><!-- .sv-container -->
		<!--/ SV Media Gallery -->
	</article><!-- #single-<?php the_ID(); ?> -->
<?php endwhile;
get_footer();
