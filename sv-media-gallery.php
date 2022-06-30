<?php
/**
 * Plugin Name: SV Media Gallery
 * Description: Gallery for a site based on the Carbon Fields plugin
 * Plugin URI:  https://sovetit.ru/wordpress/plugins/carbon-fields/media-gallery/
 * Author URI:  https://sovetit.ru/about/
 * Author:      Pavel Ketov
 * Version:     1.4.1.1
 * Text Domain: sv_media_gallery
 * Domain Path: /languages
 */
defined( 'ABSPATH' ) || exit;

/** Импортируем нужные классы Carbon Fields  */
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Проверяем существование указанной константы
if ( ! defined( 'SV_GALLERY_PLUGIN_DIR' ) ) {

	// Абсолютный путь к каталогу плагина
	define( 'SV_GALLERY_PLUGIN_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ) );

	// Имя каталога плагина
	define( 'SV_GALLERY_PLUGIN_NAME', basename( SV_GALLERY_PLUGIN_DIR ) );

	// URL каталога плагина
	define( 'SV_GALLERY_PLUGIN_URL', plugins_url( SV_GALLERY_PLUGIN_NAME ) );

	// Версия плагина
	define( 'SV_GALLERY_VERSION', '1.4.0' );
}

/**
 * Подключаем Carbon Fields из директории plugins со всеми зависимостями
 * @see sv_gallery_carbon_load
 */
function sv_gallery_carbon_load() {
	require_once( 'vendor/autoload.php' );
	\Carbon_Fields\Carbon_Fields::boot();
}
add_action( 'after_setup_theme', 'sv_gallery_carbon_load' );

/**
 * Локализация плагина
 * @see sv_gallery_languages
 */
function sv_gallery_languages() {
	load_plugin_textdomain(
		'sv_media_gallery',
		false,
		SV_GALLERY_PLUGIN_NAME . '/languages/'
	);
}
add_action( 'plugins_loaded', 'sv_gallery_languages' );

/**
 * Создаем новый тип записи Галерея
 * @see sv_gallery_init
 */
function sv_gallery_init() {
	$labels = [
		'name'               => __( 'Galleries', 'sv_media_gallery' ), // Галереи
		'singular_name'      => __( 'Gallery', 'sv_media_gallery' ), // Галерея
	];
	$args = [
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => [ 'slug' => 'sv_gallery' ],
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' ),
		'menu_icon'          => 'dashicons-images-alt2'
	];
	register_post_type( 'sv_gallery', $args );
}
add_action( 'init', 'sv_gallery_init' );

/**
 * Добавляем наш мета-бокс Галерея
 * @see sv_media_gallery
 */
function sv_media_gallery() {
	Container::make( 'post_meta', 'page-gallery', __( 'Gallery', 'sv_media_gallery' ) )
	         ->where( 'post_type', '=', 'sv_gallery' ) // Выводим только на типе записи галерея
	         ->add_fields([
			Field::make('media_gallery', 'photo', __( 'Photo', 'sv_media_gallery' ) )
			     ->set_type('image')
		])->set_context('normal');
}
add_action( 'carbon_fields_register_fields', 'sv_media_gallery' );

// Ширина изображения
$image_size_width = get_option( 'image_size_width' );
$image_size_width_option = ( empty( $image_size_width ) ) ? 184 : $image_size_width;

// Высота изображения
$image_size_height = get_option( 'image_size_height' );
$image_size_height_option = ( empty( $image_size_height ) ) ? 125 : $image_size_height;

/**
 * Регистрирует новый размер картинки (миниатюры).
 */
add_image_size( 'sv-gallery', $image_size_width_option, $image_size_height_option, true );

/**
 * Загружаем наш шаблон из директории templates
 * @see sv_gallery_templates
 *
 * @param $template
 *
 * @return mixed|void
 */
function sv_gallery_templates( $template ) {
	$template_slug = rtrim( $template, '.php' );
	$template = $template_slug . '.php';
	$file = SV_GALLERY_PLUGIN_DIR . '/templates/' . $template;
	return apply_filters( 'sv_gallery_templates' . $template, $file );
}
/**
 * Не используем созданный по умолчанию файл single.php
 * @see sv_gallery_template_include
 *
 * @param $template
 *
 * @return mixed|void
 */
function sv_gallery_template_include( $template ) {
	$post_id = get_the_ID();
	if ( get_post_type( $post_id ) !== 'sv_gallery' ) {
		return $template;
	} elseif ( is_archive() ) {
		return sv_gallery_templates( 'archive' );
	} else {
		return sv_gallery_templates( 'single' );
	}
}
add_filter( 'template_include', 'sv_gallery_template_include' );

/**
 * Подключаем JS и CSS
 * @see sv_gallery_scripts
 */
function sv_gallery_scripts() {

	if ( has_shortcode( get_the_content(), 'sv-gallery' ) ) {

		// Общие настройки галереи, так же по умолчанию для страниц с использованием шорткод(ов)
		$is_gallery = true;

		// Эффект
		$mode = carbon_get_theme_option( 'mode' );
		$gallery_option_mode = ( empty( $mode ) ) ? 'lg-slide' : $mode;

		// Миниатюры
		$gallery_option_thumbnail = (
		empty( carbon_get_theme_option( 'thumbnail' ) )
		) ? 0 : 1;

		// Анимация
		$gallery_option_animate_thumb = (
		empty( carbon_get_theme_option( 'animate_thumb' ) )
		) ? 0 : 1;

		// Эскизы
		$gallery_option_show_thumb_by_default = (
		empty( carbon_get_theme_option( 'show_thumb_by_default' ) )
		) ? 0 : 1;

		// Кнопки поделиться
		$gallery_option_share = (
		empty( carbon_get_theme_option(  'share' ) )
		) ? 0 : 1;

		// Кнопка загрузки изображения
		$gallery_option_download = (
		empty( carbon_get_theme_option(  'download' ) )
		) ? 0 : 1;

		// Количество изображений
		$preload = carbon_get_theme_option( 'preload' );
		$gallery_option_preload = ( empty( $preload ) ) ? 2 : $preload;

	} else {

		$is_gallery = false;
		$post_id = get_the_ID();

		// Эффект
		$gallery_option_mode = carbon_get_post_meta( $post_id, 'mode' );

		// Миниатюры
		$gallery_option_thumbnail = (
			empty( carbon_get_post_meta( $post_id, 'thumbnail' ) )
		) ? 0 : 1;

		// Анимация
		$gallery_option_animate_thumb = (
			empty( carbon_get_post_meta( $post_id, 'animate_thumb' ) )
		) ? 0 : 1;

		// Эскизы
		$gallery_option_show_thumb_by_default = (
			empty( carbon_get_post_meta( $post_id, 'show_thumb_by_default' ) )
		) ? 0 : 1;

		// Кнопки поделиться
		$gallery_option_share = (
			empty( carbon_get_post_meta( $post_id, 'share' ) )
		) ? 0 : 1;

		// Кнопка загрузки изображения
		$gallery_option_download = (
			empty( carbon_get_post_meta( $post_id, 'download' ) )
		) ? 0 : 1;

		// Количество изображений
		$gallery_option_preload = carbon_get_post_meta( $post_id, 'preload' );
	}

	if ( is_singular( 'sv_gallery' )    || // Подключаем только на внутренних страницах галерей
	     is_singular( 'page' )          || // Используем шорткод только на внутренних post_type == page
	     is_singular( 'post' )          || // Используем шорткод только на внутренних post_type == post
	     // Проверим существует ли шорткод [sv-gallery] в тексте контента
	     $is_gallery === true
	) {

		wp_enqueue_style( 'sv-gallery-lightgallery',
			SV_GALLERY_PLUGIN_URL .
			'/lightgallery/css/lightgallery.min.css',
			array(), SV_GALLERY_VERSION );

		wp_enqueue_style( 'sv-gallery-style',
			SV_GALLERY_PLUGIN_URL .
			'/css/style.css',
			array(), SV_GALLERY_VERSION );

		/** Не нужен, если уже подключен JQuery */
		wp_enqueue_script( 'sv-gallery-jquery',
			'https://code.jquery.com/jquery-3.5.1.min.js',
			array(), SV_GALLERY_VERSION, true );

		wp_enqueue_script( 'sv-gallery-lightgallery',
			SV_GALLERY_PLUGIN_URL .
			'/lightgallery/js/lightgallery-all.min.js',
			array(), SV_GALLERY_VERSION, true );

		/** Подключаем jQuery Mousewheel используя CDN */
		wp_enqueue_script( 'sv-gallery-jquery-mousewheel',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.13/jquery.mousewheel.min.js',
			array(), SV_GALLERY_VERSION, true );

		wp_enqueue_script( 'sv-gallery-lightgallery-lg-thumbnail',
			SV_GALLERY_PLUGIN_URL .
			'/lightgallery/js/lg-thumbnail.min.js',
			array(), SV_GALLERY_VERSION, true );

		wp_enqueue_script( 'sv-gallery-lightgallery-lg-fullscreen',
			SV_GALLERY_PLUGIN_URL .
			'/lightgallery/js/lg-fullscreen.min.js',
			array(), SV_GALLERY_VERSION, true );

		/**
		 * Lightgallery API
		 * @link http://sachinchoolur.github.io/lightGallery/docs/api.html
		 */
		wp_add_inline_script( 'sv-gallery-lightgallery', "
$(function() {
	$( '.sv-gallery' ).lightGallery({
		mode: 				'{$gallery_option_mode}',
		thumbnail:			{$gallery_option_thumbnail},
		animateThumb:		{$gallery_option_animate_thumb},
		showThumbByDefault: {$gallery_option_show_thumb_by_default},
		share: 				{$gallery_option_share},
		download: 			{$gallery_option_download},
		preload: 			{$gallery_option_preload}
	});
});
		", 'after' );

	}

	// Ширина контейнера
	$container_width = carbon_get_theme_option( 'container_width' );
	$container_width_option = ( empty( $container_width ) ) ? 100 : $container_width;

	$sv_gallery_style = "
		.sv-container{
			width: {$container_width_option}%;
		}
	";
	wp_add_inline_style( 'sv-gallery-style', trim( $sv_gallery_style ) );

}
add_action( 'wp_enqueue_scripts', 'sv_gallery_scripts' );

/**
 * Создаем новую колонку Шорткод
 * @see sv_gallery_short_code
 *
 * @param $columns
 *
 * @return array
 */
function sv_gallery_short_code( $columns ) {
	$num = 2; // после какой по счету колонки вставлять
	$new_columns = array(
		'short_code' => __( 'Short code', 'sv_media_gallery' ),
	);
	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}
add_filter( 'manage_sv_gallery_posts_columns', 'sv_gallery_short_code', 4 );

/**
 * Заполняем колонку Шорткод данными
 * @see sv_gallery_column
 *
 * @param $colname
 * @param $post_id
 */
function sv_gallery_column( $colname, $post_id ) {
	if( $colname === 'short_code' ) {
		echo '<input type="text" onfocus="this.select();" readonly="readonly" value="[sv-gallery id=&quot;' . $post_id . '&quot;]" class="large-text code">';
	}
}
add_filter( 'manage_sv_gallery_posts_custom_column', 'sv_gallery_column', 5, 2 );

/**
 * Вывод галереи на Фронт с помощью шорткода
 * @see sv_gallery_get_short_code
 *
 * @param $args
 *
 *  @return bool|string
 */
function sv_gallery_get_short_code( $args ) {

	$the_post   = get_post( $args['id'] );
	$galleries  = carbon_get_post_meta( $the_post->ID, 'photo' );

	// Проверяем, существует ли галерея или была удалена, то ничего не выводим
	if ( empty( $galleries ) ) return false;

	$html = '
<div class="sv-container">
	<div class="sv-gallery">
	';
	foreach ( $galleries as $gallery ) {
		$html .= '
			<a href="' . wp_get_attachment_image_url( $gallery, 'full' ) . '">
				<img class="sv-gallery-img" src="' . wp_get_attachment_image_url( $gallery, 'sv-gallery' ) . '" alt="' . $the_post->post_title . '">
			</a>
		';
	}
	$html .= '
	</div>
</div>
	';

	return trim( $html );
}
add_shortcode( 'sv-gallery', 'sv_gallery_get_short_code' );

/**
 * Эффекты
 * @see sv_gallery_field_mode
 * @return array
 */
function sv_gallery_field_mode() {
	$mode = [
		'lg-slide'                        => 'Slide',
		'lg-fade'                         => 'Fade',
		'lg-zoom-in'                      => 'Zoom-in',
		'lg-zoom-in-big'                  => 'Zoom-in-big',
		'lg-zoom-out'                     => 'Zoom-out',
		'lg-zoom-out-big'                 => 'Zoom-out-big',
		'lg-zoom-out-in'                  => 'Zoom-out-in',
		'lg-zoom-in-out'                  => 'Zoom-in-out',
		'lg-soft-zoom'                    => 'Soft-zoom',
		'lg-scale-up'                     => 'Scale-up',
		'lg-slide-circular'               => 'Slide-circular',
		'lg-slide-circular-vertical'      => 'Slide-circular-vertical',
		'lg-slide-vertical'               => 'Slide-vertical',
		'lg-slide-vertical-growth'        => 'Slide-vertical-growth',
		'lg-slide-skew-only'              => 'Slide-skew-only',
		'lg-slide-skew-only-rev'          => 'Slide-skew-only-rev',
		'lg-slide-skew-only-y'            => 'Slide-skew-only-y',
		'lg-slide-skew-only-y-rev'        => 'Slide-skew-only-y-rev',
		'lg-slide-skew'                   => 'Slide-skew',
		'lg-slide-skew-rev'               => 'Slide-skew-rev',
		'lg-slide-skew-cross'             => 'Slide-skew-cross',
		'lg-slide-skew-cross-rev'         => 'Slide-skew-cross-rev',
		'lg-slide-skew-ver'               => 'Slide-skew-ver',
		'lg-slide-skew-ver-rev'           => 'Slide-skew-ver-rev',
		'lg-slide-skew-ver-cross'         => 'Slide-skew-ver-cross',
		'lg-slide-skew-ver-cross-rev'     => 'Slide-skew-ver-cross-rev',
		'lg-lollipop'                     => 'Lollipop',
		'lg-lollipop-rev'                 => 'Lollipop-rev',
		'lg-rotate'                       => 'Rotate',
		'lg-rotate-rev'                   => 'Rotate-rev',
		'lg-tube'                         => 'Tube'
	];
	return $mode;
}

/**
 * Настройки галереи
 * @see sv_gallery_options
 */
function sv_gallery_options() {
	Container::make( 'post_meta', 'sv-gallery-options', __( 'Gallery settings', 'sv_media_gallery' ) )
         ->where( 'post_type', '=', 'sv_gallery' )
         ->add_fields([
	         Field::make( 'select', 'mode', __( 'Effect', 'sv_media_gallery' ) )
	              ->add_options(
	              	sv_gallery_field_mode()
	              )->set_help_text( __( 'Image transition effect', 'sv_media_gallery' ) ),

	         Field::make( 'checkbox', 'thumbnail', __( 'Miniatures', 'sv_media_gallery' ) )
	              ->set_option_value( 'yes' )
	              ->set_default_value( 'yes' ) // По умолчанию миниатюры для галереи включены
	              ->set_help_text( __( 'Enable gallery thumbnails', 'sv_media_gallery' ) ),

	         Field::make( 'checkbox', 'animate_thumb', __( 'Animation', 'sv_media_gallery' ) )
	              ->set_option_value( 'yes' )
	              ->set_default_value( 'yes' ) // По умолчанию анимация миниатюр включена
	              ->set_help_text( __( 'Enable thumbnail animation', 'sv_media_gallery' ) ),

	         Field::make( 'checkbox', 'show_thumb_by_default', __( 'Sketches', 'sv_media_gallery' ) )
	              ->set_option_value( 'yes' )
	              ->set_default_value( 'yes' ) // По умолчанию эскизы включены
	              ->set_help_text( __( 'Show thumbnails', 'sv_media_gallery' ) ),

	         // По умолчанию эскизы выключены
	         Field::make( 'checkbox', 'share', __( 'Share buttons', 'sv_media_gallery' ) )
	              ->set_option_value( 'yes' )
	              ->set_help_text( __( 'Show share buttons', 'sv_media_gallery' ) ),

	         // По умолчанию кнопка загрузки изображения скрыта
	         Field::make( 'checkbox', 'download', __( 'Image upload button', 'sv_media_gallery' ) )
	              ->set_option_value( 'yes' )
	              ->set_help_text( __( 'Show Image Upload Button', 'sv_media_gallery' ) ),

	         Field::make( 'text', 'preload', __( 'Number of images', 'sv_media_gallery' ) )
	              ->set_attribute( 'type', 'number' )
	              ->set_attribute( 'max', 10 )      // Максимальное кол-во
	              ->set_attribute( 'min', 1 )       // Миниальное кол-во
	              ->set_default_value( 2 )          // По умолчанию 2 изображения
	              ->set_help_text( __( 'Number of preload images', 'sv_media_gallery' ) ),

         ])->set_context('side');
}
add_action( 'carbon_fields_register_fields', 'sv_gallery_options' );

/**
 * Настройки поо умолчанию
 * @see sv_gallery_options_default
 */
function sv_gallery_options_default() {
	Container::make( 'theme_options', 'sv-gallery-options', __( 'General settings', 'sv_media_gallery' ) )
		->set_page_parent( 'edit.php?post_type=sv_gallery' )
		->set_page_file( 'general-settings' )
		->add_fields([
			Field::make( 'text', 'container_width', __( 'Container width', 'sv_media_gallery' ) )
			     ->set_attribute( 'type', 'number' )
			     ->set_attribute( 'max', 100 )      // Максимальное значение
			     ->set_attribute( 'min', 1 )       // Миниальное значение
			     ->set_default_value( 100 )        // По умолчанию 100%
			     ->set_help_text( __( 'Common container width in percentage for css', 'sv_media_gallery' ) )->set_width(15),

			Field::make( 'text', 'image_size_width', __( 'Image Width', 'sv_media_gallery' ) )
			     ->set_attribute( 'type', 'number' )
			     ->set_attribute( 'max', 1000 )     // Максимальное значение
			     ->set_attribute( 'min', 1 )       // Миниальное значение
			     ->set_default_value( 184 )        // По умолчанию 184px
			     ->set_help_text( __( 'Thumbnail image width in pixels for php', 'sv_media_gallery' ) )->set_width(15),

			Field::make( 'text', 'image_size_height', __( 'Image height', 'sv_media_gallery' ) )
			     ->set_attribute( 'type', 'number' )
			     ->set_attribute( 'max', 1000 )     // Максимальное значение
			     ->set_attribute( 'min', 1 )       // Миниальное значение
			     ->set_default_value( 125 )        // По умолчанию 125px
			     ->set_help_text( __( 'Height of thumbnail image in pixels for php', 'sv_media_gallery' ) )->set_width(15),

			Field::make( 'text', 'preload', __( 'Number of images', 'sv_media_gallery' ) )
			     ->set_attribute( 'type', 'number' )
			     ->set_attribute( 'max', 10 )      // Максимальное кол-во
			     ->set_attribute( 'min', 1 )       // Миниальное кол-во
			     ->set_default_value( 2 )          // По умолчанию 2 изображения
			     ->set_help_text( __( 'Number of preload images', 'sv_media_gallery' ) )->set_width(15),

			Field::make( 'select', 'mode', __( 'Effect', 'sv_media_gallery' ) )
			     ->add_options(
				sv_gallery_field_mode()
			)->set_help_text( __( 'Image transition effect', 'sv_media_gallery' ) )->set_width(25),

			Field::make( 'checkbox', 'thumbnail', __( 'Miniatures', 'sv_media_gallery' ) )
			     ->set_option_value( 'yes' )
			     ->set_default_value( 'yes' ) // По умолчанию миниатюры для галереи включены
			     ->set_help_text( __( 'Enable gallery thumbnails', 'sv_media_gallery' ) )->set_width(30),

			Field::make( 'checkbox', 'animate_thumb', __( 'Animation', 'sv_media_gallery' ) )
			     ->set_option_value( 'yes' )
			     ->set_default_value( 'yes' ) // По умолчанию анимация миниатюр включена
			     ->set_help_text( __( 'Enable thumbnail animation', 'sv_media_gallery' ) )->set_width(30),

			Field::make( 'checkbox', 'show_thumb_by_default', __( 'Sketches', 'sv_media_gallery' ) )
			     ->set_option_value( 'yes' )
			     ->set_default_value( 'yes' ) // По умолчанию эскизы включены
			     ->set_help_text( __( 'Show thumbnails', 'sv_media_gallery' ) )->set_width(30),

			// По умолчанию эскизы выключены
			Field::make( 'checkbox', 'share', __( 'Share buttons', 'sv_media_gallery' ) )
			     ->set_option_value( 'yes' )
			     ->set_help_text( __( 'Show share buttons', 'sv_media_gallery' ) )->set_width(30),

			// По умолчанию кнопка загрузки изображения скрыта
			Field::make( 'checkbox', 'download', __( 'Image upload button', 'sv_media_gallery' ) )
			     ->set_option_value( 'yes' )
			     ->set_help_text( __( 'Show Image Upload Button', 'sv_media_gallery' ) )->set_width(30),
		]);
}
add_action( 'carbon_fields_register_fields', 'sv_gallery_options_default' );