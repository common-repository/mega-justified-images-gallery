<?php
/*
 * Plugin Name: Mega Justified Images Gallery
 * Plugin URI: http://www.megaobj.com/megajig/
 * Description: Lightweight and modern images gallery, slideshow. Forget about your hurt when align images showcase in a page. Do not need to crop image for fit with grid anymore.
 * Author: MegaObj
 * Version: 0.2
 * Author URI: http://megaobj.com/
 * Text Domain: megajig
 * Domain Path: languages
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

class MegaJig {

    protected $url;

    protected $path;

    protected $assets_url;

    protected $version;

    protected $gallery = null;

    public function __construct($version) {

        define('MGS', DIRECTORY_SEPARATOR);

        $this->version = $version;
        $this->url = plugin_dir_url(__FILE__);
        $this->path = plugin_dir_path(__FILE__);
        $this->assets_url = $this->url . 'assets/';

		register_activation_hook(__FILE__,array($this, 'activation'));

        add_shortcode('megajig', array($this, 'shortcode'));

        require_once($this->path . 'inc' . MGS . 'gallery.php');

        add_action ('admin_init', array($this, 'process_save'), 10);

        if(!is_admin()){

            add_action( 'wp_enqueue_scripts', array($this, 'frontend_enqueue'), 10 );

        }
        else{

            if ( ! class_exists( 'WP_List_Table' ) )
                require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

            add_action( 'admin_menu', array($this, 'menu_page') );

            add_action( 'admin_enqueue_scripts', array($this, 'backend_enqueue'), 10 );
            add_action('admin_footer', array($this, 'admin_footer'));
        }
    }

    public function frontend_enqueue() {

        wp_register_script('jquery-prettyPhoto', $this->assets_url . 'libs/prettyPhoto/js/jquery.prettyPhoto.js', array('jquery'), '3.1.6', true);

        wp_register_style('jquery-prettyPhoto', $this->assets_url.'libs/prettyPhoto/css/prettyPhoto.css', false, '3.1.6');

        wp_register_script('megajig-app', $this->assets_url . 'js/megajig.js', array('jquery'), $this->version, true);

        wp_register_style('megajig-app', $this->assets_url.'css/style.css', false, $this->version);

        wp_enqueue_style('megajig-app');

        wp_enqueue_script('megajig-app');

        wp_enqueue_style('jquery-prettyPhoto');

        wp_enqueue_script('jquery-prettyPhoto');

    }

    public function backend_enqueue() {

		wp_enqueue_script('wplink');
		wp_enqueue_script('wpdialogs');
		wp_enqueue_script('wpdialogs-popup');
		wp_enqueue_style('wp-jquery-ui-dialog');
		wp_enqueue_style('thickbox');

        wp_register_script('jquery-prettyPhoto', $this->assets_url . 'libs/prettyPhoto/js/jquery.prettyPhoto.js', array('jquery'), '3.1.6', true);

        wp_register_style('jquery-prettyPhoto', $this->assets_url.'libs/prettyPhoto/css/prettyPhoto.css', false, '3.1.6');

        wp_enqueue_style('jquery-prettyPhoto');

        wp_enqueue_script('jquery-prettyPhoto');

        wp_register_script('megajig-app', $this->assets_url . 'js/megajig.js', array('jquery'), $this->version, true);

        wp_register_style('megajig-app', $this->assets_url. 'css/style.css', false, $this->version);

        wp_enqueue_style('megajig-app');

        wp_enqueue_script('megajig-app');

        wp_register_style( 'megajig-admin', $this->assets_url . 'css/admin.css', false, $this->version );

        wp_register_script( 'megajig-admin', $this->assets_url . 'js/admin.js', array('jquery'), $this->version, true);

        wp_enqueue_style( 'megajig-admin' );

        wp_enqueue_script( 'megajig-admin' );



    }

    public function shortcode($atts) {

        global $wpdb;

        $id = $atts['id'];
        $gallery = new MegaJigGallery();
        $gallery->load($id);

        wp_add_inline_script( 'megajig-app', '
            jQuery(function() {
                jQuery(".megajig-' . $id . '").MegaJig(
                    '. $gallery->options .'
                );
            });
        ');
		ob_start();
        ?>
		<div class="megajig-<?php echo $id;?>">
        </div>
		<svg class="mj-greyscale" version="1.1" xmlns="http://www.w3.org/2000/svg">
    		<filter id="mj-greyscale">
			<feColorMatrix type="matrix" values="0.3333 0.3333 0.3333 0 0
                                          0.3333 0.3333 0.3333 0 0
                                          0.3333 0.3333 0.3333 0 0
                                          0      0      0      1 0"/>
	        </filter>
	  	</svg>
        <?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;

    }

    public function panel() {
        require_once( $this->path . MGS . 'admin.php' );
    }

    public function admin_footer(){
		if($_REQUEST['page'] == 'megajig'){
			echo '<script type="text/javascript">';
	        echo 'var megajig_settings = '.json_encode( (object) $this->settings() ).';';
	        echo '</script>';
		}

    }

    public function menu_page() {
        add_menu_page(
            __( 'Mega Jig Gallery', 'textdomain' ),
                'Mega Jjg Gallery',
                'manage_options',
                'megajig',
                array($this, 'router'),
                $this->url . 'assets/images/megajig_icon.png',
            90
        );
    }

    public function router() {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';
        $megajig_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;

        switch ($action) {


            case 'edit':

                $gallery = new MegaJigGallery();

                if($megajig_id >0)
                    $gallery->load($megajig_id);

                $js = '';

                if(count($gallery->attachments) > 0){
                    $js .= 'mj_admin.attachments = ' . json_encode($gallery->attachments).';';
                }

                $js .= 'mj_admin.current_options = '. $gallery->options .';';
                $js .= 'mj_admin.data = '. $gallery->settings.';';
				$js .= 'mj_admin.init();';

                wp_add_inline_script( 'megajig-admin', $js);



                // Add the color picker css file
                wp_enqueue_style( 'wp-color-picker' );

                if(function_exists( 'wp_enqueue_media' )){
                    wp_enqueue_media();
                }else{
                    wp_enqueue_style('thickbox');
                    wp_enqueue_script('media-upload');
                    wp_enqueue_script('thickbox');
                }

                require_once $this->path . 'inc' . MGS . 'fields.php';
                require_once $this->path . 'admin' . MGS . 'edit.php';

                break;
            default:
                require_once $this->path . 'inc' . MGS . 'list_galleries.php';
                $galleries = new MegaJig_Galleries_List_Table();
                $galleries->prepare_items();
                require_once $this->path . 'admin' . MGS . 'list.php';
                break;
        }

    }

    public function process_save(){
        global $wpdb;

		$tableName = $wpdb->prefix . 'megajig';
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

        if($page != 'megajig') return;


        $megajig_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$retrieved_nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';

		switch ($action) {
			case 'delete' :

				if (!wp_verify_nonce($retrieved_nonce, 'deletegallery_' . $megajig_id ) ) die( 'Failed security check' );

				$gallery = new MegaJigGallery();
				$gallery->load($megajig_id);
				$gallery->remove();
				wp_redirect( menu_page_url( 'megajig',false ) . '&action=list&msg=2' );
				exit;
			break;

			case 'duplicate':
				$gallery = new MegaJigGallery();
				if($megajig_id >0)
					$gallery->load($megajig_id);

				$data = array(
					'title' => 'Copy ' . $gallery->title,
					'source' => $gallery->source,
					'items' => $gallery->items,
					'settings' => $gallery->settings,
					'options' => $gallery->options,
					'created_at' => current_time('mysql', 1),
					'updated_at' => current_time('mysql', 1),
				);
				$wpdb->insert(
					$tableName,
					$data
				);
				$megajig_id  = $wpdb->insert_id;

				wp_redirect( menu_page_url( 'megajig',false ));
				exit;

			case 'save':

				$title = isset($_POST['gallery_title']) ? $_POST['gallery_title'] : 'New Gallery';
				$items = isset($_POST['items']) ? $_POST['items'] : '';
				$options = isset($_POST['options']) ? $_POST['options'] : array();
				$settings = isset($_POST['settings']) ? $_POST['settings'] : array();


				if(empty($megajig_id) || $megajig_id == 0){

					$data = array(
						'title' => $title,
						'source' => 'media',
						'items' => stripcslashes($items),
						'settings' => stripcslashes($settings),
						'options' => stripcslashes($options),
						'created_at' => current_time('mysql', 1),
						'updated_at' => current_time('mysql', 1),
					);
					$wpdb->insert(
						$tableName,
						$data
					);
					$megajig_id  = $wpdb->insert_id;

				}else{
					$data = array(
						'title' => $title,
						'source' => 'media',
						'items' => stripcslashes($items),
						'settings' => stripcslashes($settings),
						'options' => stripcslashes($options),
						'updated_at' => current_time('mysql', 1),
					);
					$wpdb->update(
						$tableName,
						$data,
						array('id' => $megajig_id)
					);
				}
				wp_redirect( menu_page_url( 'megajig',false ) . '&action=edit&msg=1&id=' . $megajig_id );
				exit;
				break;
		}



    }

    function settings () {
        $settings = array(
            'layout'    => array(
                'basic_setting' => array(
                    array(
                        'type' => 'text',
                        'label' => __('Row Min Height', 'megajig'),
                        'name' => 'min_height',
                        'value' => 200,
                        'unit' => 'px',
                        'description' => __('The min height of images on row. Gallery auto increase height if your row do not have enough width to fit with wrapper width, to ensure images look well with same ratio.', 'megajig'),
                        'live' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Gap Items', 'megajig'),
                        'name' => 'gap',
                        'value' => 5,
                        'unit' => 'px',
                        'description' => __('The spacing between images and wrapper. Default is 5 (px)', 'megajig'),
                        'live' => true
                    ),
                    array(
                        'type' => 'toggle',
                        'label' => __('Wrapper Gap', 'megajig'),
                        'name' => 'gap_wrapper',
                        'value' => 'yes',
                        'description' => __('Set gap spacing for left and right of wrapper. Default it is none.', 'megajig'),
                        'live' => true
                    ),
                ),
                'misc' => array(
                    array(
                        'type' => 'toggle',
                        'label' => __('Show Title', 'megajig'),
                        'name' => 'title',
                        'value' => 'yes',
                        'description' => __('Show title of image in content box when hover.', 'megajig'),
                        'live' => true
                    ),
                    array(
                        'type' => 'toggle',
                        'label' => __('Show Description', 'megajig'),
                        'name' => 'description',
                        'value' => 'no',
                        'description' => __('Show description of image in content box when hover.', 'megajig'),
                        'live' => true
                    )
                )
            ),
            'lightbox'  => array(
                array(
                    array(
                        'type' => 'toggle',
                        'label' => __('Enable', 'megajig'),
                        'name' => 'enable',
                        'value' => 'no',
                        'description' => __('Enable popup to view lager image when click on item.', 'megajig'),
                        'live' => true
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Theme','megajig'),
                        'name' => 'theme',
                        'value' => 'pp_default',
                        'description' => __('Layout style for popup image. There are some style here, we will add more.', 'megajig'),
                        'live' => true,
                        'options' => array(
                            'pp_default' => __('Default', 'megajig'),
                            'light_rounded' => __('Light Rounded','megajig'),
                            'dark_rounded' => __('Dark Rounded','megajig'),
                            'light_square' => __('Light Square','megajig'),
                            'dark_square' => __('Dark Square','megajig'),
                            'facebook' => __('Facebook','megajig')
                        )
                    ),
                    array(
                        'type' => 'toggle',
                        'label' => __('Show Title', 'megajig'),
                        'name' => 'show_title',
                        'value' => 'no',
                        'description' => __('Show title of image on top popup.', 'megajig'),
                        'live' => true
                    ),
                ),

                array(
                    array(
                        'type' => 'toggle',
                        'label' => __('Slideshow', 'megajig'),
                        'name' => 'slideshow',
                        'value' => 'no',
                        'description' => __('Enable slideshow view on popup.', 'megajig'),
                        'live' => true
                    ),

                    array(
                        'type' => 'text',
                        'label' => __('SlideShow Speed', 'megajig'),
                        'name' => 'slideshow_speed',
                        'value' => 5000,
                        'description' => __('The seconds between slide on slideshow. Default is 5000 (ms)', 'megajig'),
                        'live' => true
                    ),
					array(
                        'type' => 'select',
                        'label' => __('Animation Speed', 'megajig'),
                        'name' => 'animation_speed',
                        'value' => 'slow',
                        'description' => __('Speed to switch to other image on slide.', 'megajig'),
                        'live' => true,
                        'options' => array(
                            'fast' => __('Fast', 'megajig'),
                            'normal' => __('Normal', 'megajig'),
                            'slow' => __('Slow', 'megajig')
                        )
                    ),
                ),

            ),


			'style' => array(
				array(
					array(
						'type' => 'select',
                        'label' => __('Content Layout', 'megajig'),
                        'name' => 'content_layout',
                        'value' => 'flickr',
                        'description' => __('The layout of title and description when hover on image. Default is Flickr style. Some layout need to increase gap to see what happened.', 'megajig'),
                        'options' => array(
                            '' => __('Flickr', 'megajig'),
                            'google' => __('Google Images', 'megajig'),
                            'facebook' => __('Facebook', 'megajig'),
                            'overlay' => __('Overlay', 'megajig'),
                        ),
						'preset' => true,
						'selector' => '.megajig-viewer'
					),

				),
				array(
					array(
						'type' => 'select',
                        'label' => __('Wrapper', 'megajig'),
                        'name' => 'wrapper',
                        'value' => '',
                        'description' => __('The style of box wrapper images.', 'megajig'),
                        'options' => array(
                            '' => __('Default', 'megajig'),
                            'rounded' => __('Rounded', 'megajig'),
                            'bordered' => __('Bordered', 'megajig'),
                            'bordered-round' => __('Bordered Round', 'megajig'),
                            'shadow' => __('Shadow', 'megajig'),
                            'shadow-round' => __('Shadow Round', 'megajig'),
                            'album' => __('Album', 'megajig'),
                            'polaroids' => __('Polaroids', 'megajig'),
                        ),
						'live' => true,
						'preset' => true,
						'selector' => '.megajig-viewer'
					)
				)
			),
			'effect' => array(
				'hover' => array(
					array(
						'type' => 'select',
                        'label' => __('Box Hover', 'megajig'),
                        'name' => 'box',
                        'value' => 'flickr',
                        'description' => __('The effect on hover an wrapper image.', 'megajig'),
                        'options' => array(
                            '' => __('None', 'megajig'),
                            'zoom-1x' => __('Zoom 1.2', 'megajig'),
                            'zoom-2x' => __('Zoom 1.5', 'megajig'),
                            'rounded' => __('Rounded', 'megajig'),
                            'shadow' => __('Shadow', 'megajig'),
                        ),
						'preset' => true,
						'selector' => '.megajig-viewer'
					),
					array(
						'type' => 'select',
                        'label' => __('Image Effect', 'megajig'),
                        'name' => 'image',
                        'value' => '',
                        'description' => __('The effect for an image when mouse over on it.', 'megajig'),
                        'options' => array(
                            '' => __('None', 'megajig'),
                            'zoom-in' => __('Zoom In', 'megajig'),
                            'black-white' => __('Black & White', 'megajig'),

                        ),
						'preset' => true,
						'selector' => '.megajig-viewer'
					)
				)

			),
        );

        return $settings;
    }

	function activation(){
		global $wpdb;
		$db_name = $wpdb->prefix . 'megajig';

		if($wpdb->get_var("show tables like '$db_name'") != $db_name)
		{
			$sql = "CREATE TABLE " . $db_name . " (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			    `title` varchar(255) DEFAULT NULL,
			    `source` varchar(11) NOT NULL DEFAULT '',
			    `options` text,
			    `created_at` datetime DEFAULT NULL,
			    `updated_at` datetime DEFAULT NULL,
			    `items` text,
			    `settings` text,
			    PRIMARY KEY (`id`)
			);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}

		$count = $wpdb->get_var(  "SELECT COUNT(id) FROM ".$db_name  );
	    if (!$count)
	    {
			//insert sample data
			$values = array(
				'title' => 'Sample Gallery',
				'source' => 'media',
				'options' => '{"min_height":200,"gap":5,"gap_wrapper":true,"last_row":true,"lightbox":{"enable":true,"theme":"facebook","show_title":true,"slideshow":false,"slideshow_speed":5000,"animation_speed":"fast","social_tools":false},"title":true,"description":true}',
				'items' => '[{"uploading":false,"id":1742,"title":"02","url":"http://works.dev/wp-content/uploads/2017/05/02.jpg","description":"","height":2000,"width":1333},{"uploading":false,"id":1749,"title":"10","url":"http://works.dev/wp-content/uploads/2017/05/10.jpg","description":"","height":2936,"width":2048},{"uploading":false,"id":1743,"title":"04","url":"http://works.dev/wp-content/uploads/2017/05/04.jpg","description":"","height":2304,"width":3072},{"uploading":false,"id":1744,"title":"05","url":"http://works.dev/wp-content/uploads/2017/05/05.jpg","description":"","height":2400,"width":3600},{"uploading":false,"id":1745,"title":"06","url":"http://works.dev/wp-content/uploads/2017/05/06.jpg","description":"","height":1800,"width":3286},{"uploading":false,"id":1748,"title":"09","url":"http://works.dev/wp-content/uploads/2017/05/09.jpg","description":"","height":2048,"width":3072},{"uploading":false,"id":1750,"title":"OLYMPUS DIGITAL CAMERA","url":"http://works.dev/wp-content/uploads/2017/05/11.jpg","description":"","height":1920,"width":2560},{"uploading":false,"id":1752,"title":"13","url":"http://works.dev/wp-content/uploads/2017/05/13.jpg","description":"","height":1368,"width":1920},{"uploading":false,"id":1755,"title":"20","url":"http://works.dev/wp-content/uploads/2017/05/20.jpg","description":"","height":2591,"width":3902},{"uploading":false,"id":1756,"title":"21","url":"http://works.dev/wp-content/uploads/2017/05/21.jpg","description":"","height":1800,"width":2717},{"uploading":false,"id":1757,"title":"22","url":"http://works.dev/wp-content/uploads/2017/05/22.jpg","description":"","height":2210,"width":4298},{"uploading":false,"id":1758,"title":"23","url":"http://works.dev/wp-content/uploads/2017/05/23.jpg","description":"","height":1900,"width":2533},{"uploading":false,"id":1764,"title":"KONICA MINOLTA DIGITAL CAMERA","url":"http://works.dev/wp-content/uploads/2017/05/14.jpg","description":"Great View in sahara","height":1200,"width":1600}]',
				'settings' => '{"layout":{"min_height":200,"gap":"5","gap_wrapper":"yes","title":"yes","description":"yes"},"lightbox":{"enable":"yes","theme":"facebook","show_title":"yes","slideshow":"no","slideshow_speed":5000,"animation_speed":"fast"}}',
				'created_at' => current_time('mysql', 1),
				'updated_at' => current_time('mysql', 1),
			);
			$wpdb->insert( $db_name, $values );
		}
	}

}

$megajig = new MegaJig('0.2');
