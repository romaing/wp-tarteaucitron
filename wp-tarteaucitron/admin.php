<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// Based on Anna's gist https://gist.github.com/annalinneajohansson/5290405
// http://codex.wordpress.org/Settings_API
// https://knowledge.parcours-performance.com/page-de-reglage-extension-wordpress/


// Based on Anna's gist https://gist.github.com/annalinneajohansson/5290405
// http://codex.wordpress.org/Settings_API


/**********************************************************************
* DEBUG ?
***********************************************************************/

define('ENABLE_DEBUG', true); // if true, the script will echo debug data

/**********************************************************************

* to set the title of the setting page see -- wp_tac_options_page()
* to set the sections see -- wp_tac_settings_sections_val()
* to set the fields see -- wp_tac_settings_fields_val()

**********************************************************************/

// create the settings page and it's menu
add_action( 'admin_menu', 'wp_tac_admin_menu' );
// set the content of the admin page
add_action( 'admin_init', 'wp_tac_admin_init' );
add_action( 'admin_init', 'wp_tac_param_admin_init' );
// set the content of the admin page
add_action('wp_enqueue_scripts', 'wp_tac_front_init',PHP_INT_MAX);

//add_action( 'shutdown', function(){
//	foreach( $GLOBALS['wp_actions'] as $action	=> $count )
//		printf( '%s (%d) <br/>' . PHP_EOL, $action, $count );
//
//});

function wp_tac_admin_menu() {
	
	add_options_page(
		__('Options de WP tarte au citron', 'wp-tac' ),				// page title (H1)
		__('WP tarte au citron', 'wp-tac' ),						// menu title
		'manage_options',											// required capability
		'wp_tac-plugin',											// menu slug (unique ID)
		'wp_tac_options_page'										// callback function
	);
}

function wp_tac_param_admin_init() {

	register_setting( 'my-settings-group', 'wp_tac-plugin-params' ) ;

	// add_settings_section
	$set_sections = wp_tac_settings_params_val();
	foreach( $set_sections as $section ) {
		
		add_settings_section(
			$section[ 'section_name' ],
			$section[ 'section_title' ] ,
			$section[ 'section_callbk' ],
			$section[ 'menu_slug' ]
		);
	}

	// add the fields
	$set_fields = wp_tac_settings_param_fields_val();
	foreach ( $set_fields as $section_field ) {
		foreach( $section_field as $field ){

			add_settings_field(
				$field['field_id'],
				$field['label'],
				$field['field_callbk'],
				$field['menu_slug'],
				$field['section_name'],
				$field
			);
		}
	}
}

function wp_tac_admin_init() {

	register_setting( 'my-settings-group', 'wp_tac-plugin-settings' ) ;

	// add_settings_section
	$set_sections = wp_tac_settings_sections_val() ;
	foreach( $set_sections as $section ) {
		
		add_settings_section(
			$section['section_name'],
			$section['section_title'] ,
			$section['section_callbk'],
			$section['menu_slug']
		);
		
	}

	// add the fields
	$set_fields = wp_tac_settings_fields_val() ;
	foreach ( $set_fields as $section_field ) {

		foreach( $section_field as $field ){

			add_settings_field(
				$field['field_id'],
				$field['label'],
				$field['field_callbk'],
				$field['menu_slug'],
				$field['section_name'],
				$field
			);
		}

	}
}

function get_QodeOptions($param, $default=false) {
	$qode_options_proya = (array) get_option( 'qode_options_proya' );
	return !empty($qode_options_proya[$param])? $qode_options_proya[$param] : $default;
}

function wp_tac_front_init() {
	$params = array();
	$ret = wp_tac_settings_param_fields_val();
	foreach ( $ret  as $section_id	=> $section_val) {
		foreach ( $section_val  as $field_id	=> $value) {
			$params[$value['param_name']] = $value['default'];
		}
	}

	$arr_params = (array) get_option( 'wp_tac-plugin-params' );
	foreach ($arr_params as $field_id	=> $value) {
		$params = array_merge ( $params, (array) $value );
	}

	$paramsjs = "";
	foreach ($params as $key	=> $value) {
		if ( $value=="0" || $value=="1" ){
			$value = ($value===false)? 'false' : 'true' ;
			$paramsjs .= '"'.$key.'" : '. $value.',' . "\n";
		}else{
			$paramsjs .= '"'.$key.'" : "'.$value.'",' . "\n";
		}
	}

	wp_add_inline_script('wp_tac', '
		tarteaucitron.init({
			'.$paramsjs.'
		});
		tarteaucitron.user.gtagUa="UA-XXXX";
	');

	$gajsUa = get_QodeOptions('google_analytics_code');

	$default_settings = array(
		'field-1-1'	=> array(
				'marqueur'	=> "",
				'job'	=> "jsapi",
				'enqueue'	=> "",
			),
		'field-1-2'	=> array(
				'marqueur'	=> "",
				'job'	=> "googlemaps",
				'enqueue'	=> "google_map_api",
				'jobuser'	=> "key",
				'user'	=> "",
			),
		'field-3-10'	=> array(
				'marqueur'	=> "",
				'job'	=> "analytics",
				'enqueue'	=> "",
				'jobuser'	=> "analyticsUa",
				'user'	=> "",
			),
		'field-7-6'	=> array(
				'status'	=> "1",
				'marqueur'	=> "",
				'job'	=> "youtube",
				'enqueue'	=> "",
			),
		);

	if($gajsUa) {
		$default_gajsUa = array(
			'field-3-8'	=> array(
					'status'	=> "1",
					'marqueur'	=> "",
					'job'	=> "gajs",
					'enqueue'	=> "",
					'jobuser'	=> "gajsUa",
					'user'	=> $gajsUa,
			),
		);
		$default_settings = array_merge ( $default_settings, $default_gajsUa );
	}
	

	$settings = (array) get_option( 'wp_tac-plugin-settings' , $default_settings);

	foreach ($settings as $field_id	=> $value) {
		if (isset($value['status']) && $status = $value['status']) {
			$marqueur = $value['marqueur'];

			if ($enqueue = $value['enqueue']) {
				wp_dequeue_script( $enqueue );
			}
			if (isset($value['job']) && $job = $value['job']) {
				$tac_user = "";

				$set_fields = wp_tac_settings_fields_val() ;
				foreach ($set_fields as $section	=> $datasection) {
					foreach ($datasection as $key	=> $valueparam) {
						if( $valueparam['field_id'] == $field_id ){
							
							if(isset($valueparam['optionnal'])){
								$arr_optionnal = $valueparam['optionnal'];
							}
							if(isset($valueparam['search']) && $arr_search = $valueparam['search']){

								if($job=='youtube'){
									global $wp_embed;
									$wp_embed->arr_optionnal = $arr_optionnal;
									$wp_embed->arr_search = $arr_search;

									function wrap_embed_with_div($html, $url, $attr) {
										global $wp_embed;

										$pattern = $wp_embed->arr_search['ereg'];
										$subject = $wp_embed->arr_search['replace'];

										preg_match($pattern, $html, $matches);

										$video_id		= basename($matches[3]);

										$width			= $matches[1] ;
										$height			= $matches[2] ;

										$theme			= $wp_embed->arr_optionnal['theme'] ;
										$rel			= $wp_embed->arr_optionnal['rel'] ;
										$controls		= $wp_embed->arr_optionnal['controls'] ;
										$showinfo		= $wp_embed->arr_optionnal['showinfo'] ;
										$autoplay		= $wp_embed->arr_optionnal['autoplay'] ;

										$search	= array('$video_id', '$width', '$height', '$theme', '$rel', '$controls', '$showinfo', '$autoplay');
										$replace = array( $video_id,	$width,	$height,	$theme,	$rel,	$controls,	$showinfo,	$autoplay);

										$html = str_replace($search, $replace, $subject);
										return $html;

									}
									add_filter('embed_oembed_html', 'wrap_embed_with_div', 100, 3);
									add_filter('oembed_result', 'wrap_embed_with_div', 100, 3);
								}
//									if($job=='googlemaps'){
//										global $post_map;
//										$post_map->arr_optionnal = $arr_optionnal;
//										$post_map->arr_search = $arr_search;
//
//										function multiple_string_replacements ( $content ) {
//											global $post_map;
//
//											$pattern = $post_map->arr_search['ereg'];
//											$subject = $post_map->arr_search['replace'];
//
//											preg_match($pattern, $content, $matches);
//
//											$all			 = $matches[0] ;
//											$height		 = $matches[1] ;
//											$style			= $matches[2] ;
//											$div_map_ovrlay = $matches[3] ;
//											$div_google_map = $matches[4] ;
//
//											$zoom			= $wp_embed->arr_optionnal['zoom'] ;
//											$latitude		= $wp_embed->arr_optionnal['latitude'] ;
//											$longitude	 = $wp_embed->arr_optionnal['longitude'] ;
//											$widthpx		= $wp_embed->arr_optionnal['widthpx'] ."px";
//											$heightpx		= $height."px" ;
//
//											$search	= array('$zoom', '$latitude', '$longitude', '$widthpx', '$heightpx');
//											$replace = array( $zoom,	$latitude,	$longitude,	$widthpx,	$heightpx);
//											$tag_canvas = str_replace($search, $replace, $subject);
//										//echo __FILE__, ":",__LINE__,"","<pre>", print_r( $matches ,1),"</pre>";
//											//	supprime les ancien tag et repasse par le nouveau
//											//	$search	= array( $div_map_ovrlay,	$div_google_map);
//											//	$replace = array( $tag_canvas,	'');
//											//	$search	= array( $div_google_map);
//											//	$replace = array( $tag_canvas);
//											//	$content = str_replace($search, $replace, $content);
//
//
//											return $content ;
//										}

										//add_filter( 'the_post', 'multiple_string_replacements', 10, 1 );
//										add_filter( 'after_google_map', 'multiple_string_replacements', 10, 1 );

//									}

							}
							break 2;
						}
					}
				}
			}

			if(isset($value['jobuser']) && $jobuser = $value['jobuser']){
				$account_id = $value['user'];
				$tac_user = "tarteaucitron.user.".$jobuser." = '".$account_id."' ; ";
			}

			 wp_add_inline_script('wp_tac', '
				'.$tac_user.'
				(tarteaucitron.job = tarteaucitron.job || []).push(\''.$job.'\');
			');
			}
	}
}


/**********************************************************************

* The actual page

**********************************************************************/
function wp_tac_options_page() {
?>
 <div class="wrap">
	 <h2><?php _e('WP tarte au citron Options', 'wp-tac'); ?></h2>
	 <form action="options.php" method="POST">
		<?php settings_fields('my-settings-group'); ?>
		<?php do_settings_sections('wp_tac-plugin'); ?>
		<?php submit_button(); ?>
		<?php echo '<div class="button resetparam ">'.__('Reset parameter', 'wp-tac' ).'</div>'; ?>
	 </form>
 </div>
<?php }


/**********************************************************************

* Field callback

**********************************************************************/

function wp_tac_settings_field_callback( $arguments ) {

	$field			= $arguments['field_id'] ;
	$marqueur		= $arguments['marqueur'] ;
	$enqueue		= $arguments['enqueue'] ;
	$job			= $arguments['job'] ;
	$placeholder	= $arguments['placeholder'] ;
	$jobuser		= $arguments['jobuser'] ;
	$description	= $arguments['description'];

	$settings = (array) get_option( 'wp_tac-plugin-settings' );
	$value = esc_attr( $settings[$field]['status'] );
	$user = esc_attr( $settings[$field]['user'] );

	$html = '<label class="switch" for="checkbox_tac['.$field.'][status]">';
	$html .= '<input type="checkbox" id="checkbox_tac['.$field.'][status]" name="wp_tac-plugin-settings['.$field.'][status]" value="1" ' ;
	$html .= checked( 1, $value, false ) . '/>';
	$html .= '<span class="slider round"></span>';
	$html .= '<input type="text" id="checkbox_tac['.$field.'][marqueur]" name="wp_tac-plugin-settings['.$field.'][marqueur]" value="'.base64_encode ( $marqueur ) .'" />' ;
	$html .= '<input type="text" id="checkbox_tac['.$field.'][job]" name="wp_tac-plugin-settings['.$field.'][job]" value="'.$job.'" />';
	$html .= '<input type="text" id="checkbox_tac['.$field.'][enqueue]" name="wp_tac-plugin-settings['.$field.'][enqueue]" value="'. $enqueue .'" />';

	$html .= '</label>';
	if($jobuser){
		$html .= '<span class="user">';
		$html .= '<input type="hidden" id="checkbox_tac['.$field.'][jobuser]" name="wp_tac-plugin-settings['.$field.'][jobuser]" value="'. $jobuser .'" />';
		$html .= '<input placeholder="'.$placeholder.'" type="text" id="text_tac['.$field.'][user]" name="wp_tac-plugin-settings['.$field.'][user]" value="'. $user .'" />';
		$html .= '</span>';
	}
	$html .= '<br><span class="description">'.$description.'</span>';
	echo $html;
}

function wp_tac_settings_param_field_callback ( $arguments, $style="round") {

	$field			= $arguments['field_id'] ;
	$default		= $arguments['default'] ;
	$placeholder	= $arguments['placeholder'] ;
	$type			= $arguments['type'] ;
	$param_name		= $arguments['param_name'] ;

	$settings	= (array) get_option( 'wp_tac-plugin-params' );
	$value		= (esc_attr( $settings[$field][$param_name] ))?esc_attr( $settings[$field][$param_name] ) : $default;
	$param		= (esc_attr( $settings[$field]['param'] ))?esc_attr( $settings[$field]['param'] ) : $default;

	if($type == 'checkbox'){
		$html  = '<label class="switch" for="checkbox_tac['.$field.']['.$param_name.']">';
		$html .= '<input class="param" data-default="'.$default.'" type="checkbox" id="checkbox_tac['.$field.']['.$param_name.']" name="wp_tac-plugin-params['.$field.']['.$param_name.']" value="1" ' ;
		$html .= checked( $value , true , false ) . '/>';
		$html .= '<span class="slider '.$style.'"></span>';

	}elseif($type == 'select'){
		$options = $arguments['options'] ;
		$html  = '<label for="select_tac['.$field.']['.$param_name.']">';
		$html .= '<select class="param" data-default='.$default.' id="select_tac['.$field.'][status]" name="wp_tac-plugin-params['.$field.']['.$param_name.']" > ' ;

		foreach ($options as $key	=> $option) {
			$html .= '<option value="'.$option.'" ' ;
			$html .= selected( $options[$key], $value , false) . '/>'.$option.'</option>';
		}
		 $html .= '</select>';
	}else{
		$html  = '<span class="param">';
		$html .= '<input class="param" data-default='.$default.' placeholder="'.$placeholder.'" type="text" id="text_tac['.$field.']['.$param_name.']" name="wp_tac-plugin-params['.$field.']['.$param_name.']" value="'. $param .'" />';
		$html .= '</span>';
	}
	echo $html;
}

/**********************************************************************

* THE FIELDS

**********************************************************************/
function wp_tac_settings_fields_val() {

	$gaUa = get_QodeOptions('google_analytics_code');

	$section_1_fields = array (
		array(
			'field_id'		=> 'field-1-1',
			'label'			=> __( 'Google jsapi', 'wp-tac' ),
			'field_callbk'	=> 'wp_tac_settings_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-1',
			'helper'		=> __( 'help 1', 'wp-tac' ),
			'description'	=> __( 'description <a href="#">test</a>', 'wp-tac' ),
			'default'		=> '',
			'job'			=> 'jsapi',
			'enqueue'		=> '',
			'marqueur'		=> '',
			'jobuser'		=> '',
			'placeholder'	=> '',
		),
		array(
			'field_id'		=> 'field-1-2',
			'label'			=> __( 'Google Maps', 'wp-tac' ),
			'field_callbk'	=> 'wp_tac_settings_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-1',
			'helper'		=> __( 'help 1', 'wp-tac' ),
			'description'	=> __( '', 'wp-tac' ),
			'default'		=> '',
			'job'			=> 'googlemaps',
			'enqueue'		=> 'google_map_api',
			'marqueur'		=> '',
			'jobuser'		=> 'key',
			'placeholder'	=> 'key api',
//			'search'		=> array(
//				'ereg'			=> '~data-height="(.*)" style="(.*)" .*\s*.*(<div .*><\/div>).*\s*.*(<div .*><\/div>)~',
//				'replace'		=> '<div class="googlemaps-canvas google_map" id="map_canvas" zoom="$zoom" latitude="$latitude" longitude="$longitude" style="width: $widthpx; height: $heightpx;"></div>',
//			),
//			'optionnal'		=> array(
//				'zoom'		=> 'dark', /* zoom (int) */
//				'latitude'	=> '', /* zoom (int) */
//				'longitude'	=> '', /* zoom (int) */
//				'widthpx'	=> '', /* zoom (int) */
//				'heightpx'	=> '', /* zoom (int) */
//			),
		),
//		array(
//			'field_id'		=> 'field-1-3',
//			'label'			=> __( 'Google Tag Manager', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'	 	=> 'wp_tac-plugin',
//			'section_name'	=> 'section-1',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-1-4',
//			'label'			=> __( 'Timeline JS', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'	 	=> 'wp_tac-plugin',
//			'section_name'	=> 'section-1',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'	 	=> 'field-1-5',
//			'label'		 	=> __( 'Typekit (adobe)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-1',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
	);
	$section_2_fields = array (
//		array(
//			'field_id'		=> 'field-2-1',
//			'label'			=> __( 'Disqus', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-2',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-2-2',
//			'label'			=> __( 'Facebook (commentaire)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-2',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
	);
	$section_3_fields = array (
//		array(
//			'field_id'		=> 'field-3-1',
//			'label'			=> __( 'Alexa', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-2',
//			'label'			=> __( 'Clicky', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-3',
//			'label'			=> __( 'Crazy Egg', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-4',
//			'label'			=> __( 'eTracker', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-6',
//			'label'			=> __( 'FERank', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-7',
//			'label'			=> __( 'Get+', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
		array(
			'field_id'		=> 'field-3-8',
			'label'			=> __( 'Google Analytics (ga.js)', 'wp-tac' ),
			'field_callbk'	=> 'wp_tac_settings_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-3',
			'helper'		=> __( 'help 1', 'wp-tac' ),
			'description'	=> __( '', 'wp-tac' ),
			'default'		=> '',
			'job'			=> 'gajs',
			'enqueue'		=> '',
			'marqueur'		=> '',
			'jobuser'		=> 'gajsUa',
			'placeholder'	=> $gaUa,
			'optionnal'		=> 'gajsMore', /* add here your optionnal ga.push() */
		),
//		array(
//			'field_id'		=> 'field-3-9',
//			'label'			=> __( 'Google Analytics (gtag.js)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//			'job'			=> 'gtag',
//			'enqueue'		=> '',
//			'marqueur'		=> '',
//			'jobuser'		=> 'gtagUa',
//			'placeholder'	=> $gaUa,
//			'optionnal'		=> 'gtagMore', /* add here your optionnal ga.push() */
//		),
		array(
			'field_id'		=> 'field-3-10',
			'label'			=> __( 'Google Analytics (universal)', 'wp-tac' ),
			'field_callbk'	=> 'wp_tac_settings_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-3',
			'helper'		=> __( 'help 1', 'wp-tac' ),
			'description'	=> __( '', 'wp-tac' ),
			'default'		=> '',
			'job'			=> 'analytics',
			'enqueue'		=> '',
			'marqueur'		=> '',
			'jobuser'		=> 'analyticsUa',
			'placeholder'	=> $gaUa,
			'optionnal'		=> 'analyticsMore', /* add here your optionnal ga.push() */
		),
//		array(
//			'field_id'		=> 'field-3-11',
//			'label'			=> __( 'Microsoft Campaign Analytics', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-12',
//			'label'			=> __( 'StatCounter', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-13',
//			'label'			=> __( 'VisualRevenue', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-14',
//			'label'			=> __( 'Wysistat', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-3-15',
//			'label'			=> __( 'Xiti', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-3',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
	);
	$section_4_fields = array (
//		array(
//			'field_id'		=> 'field-4-1',
//			'label'			=> __( 'Amazon', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-2',
//			'label'			=> __( 'Clicmanager', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-3',
//			'label'			=> __( 'Criteo', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-4',
//			'label'			=> __( 'Dating Affiliation', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-5',
//			'label'			=> __( 'Dating Affiliation (popup)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-6',
//			'label'			=> __( 'FERank (pub)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-7',
//			'label'			=> __( 'Google Adsense', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-8',
//			'label'			=> __( 'Google Adsense Search (form)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-9',
//			'label'			=> __( 'Google Adsense Search (result)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-10',
//			'label'			=> __( 'Google Adwords (conversion)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-11',
//			'label'			=> __( 'Google Adwords (remarketing)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-12',
//			'label'			=> __( 'Prelinker', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-13',
//			'label'			=> __( 'Pubdirecte', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-14',
//			'label'			=> __( 'ShareASale', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-15',
//			'label'			=> __( 'Twenga', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-4-16',
//			'label'			=> __( 'vShop', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-4',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
	);
	$section_5_fields = array (
//		array(
//			'field_id'		=> 'field-5-1',
//			'label'			=> __( 'AddThis', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-2',
//			'label'			=> __( 'AddToAny (feed)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-3',
//			'label'			=> __( 'AddToAny (share)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-4',
//			'label'			=> __( 'eKomi', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-5',
//			'label'			=> __( 'Facebook', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-6',
//			'label'			=> __( 'Facebook (like box)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-7',
//			'label'			=> __( 'Facebook Pixel', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-8',
//			'label'			=> __( 'Google+', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-9',
//			'label'			=> __( 'Google+ (badge)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-10',
//			'label'			=> __( 'Linkedin', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-11',
//			'label'			=> __( 'Pinterest', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-12',
//			'label'			=> __( 'Shareaholic', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-13',
//			'label'			=> __( 'ShareThis', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-14',
//			'label'			=> __( 'Twitter', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-15',
//			'label'			=> __( 'Twitter (cards)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-5-16',
//			'label'			=> __( 'Twitter (timelines)', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-5',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
	);
	$section_6_fields = array (
//		array(
//			'field_id'		=> 'field-6-1',
//			'label'			=> __( 'PureChat', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-6',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-6-2',
//			'label'			=> __( 'UserVoice', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-6',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-6-3',
//			'label'			=> __( 'Zopim', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-6',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//			'job'			=> 'zopim',
//			'enqueue'		=> '',
//			'marqueur'		=> '',
//			'jobuser'		=> 'zopimID',
//			'placeholder'	=> 'zopim ID',
//		),
	);
	$section_7_fields = array (
//		array(
//			'field_id'		=> 'field-7-1',
//			'label'			=> __( 'Calaméo', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-7',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-7-2',
//			'label'			=> __( 'Dailymotion', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-7',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-7-3',
//			'label'			=> __( 'Prezi', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-7',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
//		array(
//			'field_id'		=> 'field-7-4',
//			'label'			=> __( 'SlideShare', 'wp-tac' ),
//			'field_callbk'	=> 'wp_tac_settings_field_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//			'section_name'	=> 'section-7',
//			'helper'		=> __( 'help 1', 'wp-tac' ),
//			'description'	=> __( '', 'wp-tac' ),
//			'default'		=> '',
//		),
		array(
			'field_id'		=> 'field-7-5',
			'label'			=> __( 'Vimeo', 'wp-tac' ),
			'field_callbk'	=> 'wp_tac_settings_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-7',
			'helper'		=> __( 'help 1', 'wp-tac' ),
			'description'	=> __( '', 'wp-tac' ),
			'default'		=> '',
			'job'			=> 'vimeo',
			//'enqueue'		=> array( 'vimeo', 'mediaelement-vimeo' ),
			'enqueue'		=> 'mediaelement-vimeo',
			'marqueur'		=> '',
			'jobuser'		=> '',
			'placeholder'	=> 'ID Vidéo',
		),
		array(
			'field_id'		=> 'field-7-6',
			'label'			=> __( 'Youtube', 'wp-tac' ),
			'field_callbk'	=> 'wp_tac_settings_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-7',
			'helper'		=> __( 'help 1', 'wp-tac' ),
			'description'	=> __( '', 'wp-tac' ),
			'default'		=> '',
			'job'			=> 'youtube',
			'enqueue'		=> '',
			'marqueur'		=> '',
			'search'		=> array(
				'ereg'			=> '~.* width="(.*)".*height="(.*)".*src="(.*)\?~',
				'replace'		=> '<div class="youtube_player" videoID="$video_id" width="$width" height="$height" theme="$theme" rel="$rel" controls="$controls" showinfo="$showinfo" autoplay="$autoplay"></div>',
			),
			'optionnal'		=> array(
				'theme'			=> 'dark',	/* theme (dark | light) */
				'rel'			=> '0',		/* rel (1 | 0) */
				'controls'		=> '1',		/* controls (1 | 0) */
				'showinfo'		=> '1',		/* howinfo (1 | 0) */
				'autoplay'		=> '0',		/* autoplay (1 | 0) */
			),
			'jobuser'		=> '',
			'placeholder'	=> 'ID Vidéo',
		),
	);
	$section_fields = array(
		'section-1'	=> $section_1_fields,
		'section-2'	=> $section_2_fields,
		'section-3'	=> $section_3_fields,
		'section-4'	=> $section_4_fields,
		'section-5'	=> $section_5_fields,
		'section-6'	=> $section_6_fields,
		'section-7'	=> $section_7_fields,
	);

	return $section_fields ;
}
/**********************************************************************

* THE SECTIONS

**********************************************************************/

function wp_tac_settings_sections_val() {

	$sections = array(
		array(
			'section_name'	=> 'section-1',
			'section_title'	=> __( 'APIs', 'wp-tac' ),
			'section_callbk'=> 'wp_tac_settings_section_callback',
			'menu_slug'		=> 'wp_tac-plugin',
		),
//		array(
//			'section_name'	=> 'section-2',
//			'section_title'	=> __( 'Commentaire', 'wp-tac' ),
//			'section_callbk'	=> 'wp_tac_settings_section_callback' ,
//			'menu_slug'		=> 'wp_tac-plugin'
//		),
		array(
			'section_name'	=> 'section-3',
			'section_title'	=> __( 'Mesure d\'audience', 'wp-tac' ),
			'section_callbk'=> 'wp_tac_settings_section_callback',
			'menu_slug'		=> 'wp_tac-plugin',
		),
//		array(
//			'section_name'	=> 'section-4',
//			'section_title'	=> __( 'Régie publicitaire', 'wp-tac' ),
//			'section_callbk'	=> 'wp_tac_settings_section_callback' ,
//			'menu_slug'		=> 'wp_tac-plugin'
//		),
//		array(
//			'section_name'	=> 'section-5',
//			'section_title'	=> __( 'Réseaux sociaux', 'wp-tac' ),
//			'section_callbk'	=> 'wp_tac_settings_section_callback',
//			'menu_slug'		=> 'wp_tac-plugin',
//		),
//		array(
//			'section_name'	=> 'section-6',
//			'section_title'	=> __( 'Support', 'wp-tac' ),
//			'section_callbk'	=> 'wp_tac_settings_section_callback' ,
//			'menu_slug'		=> 'wp_tac-plugin'
//		),
		array(
			'section_name'	=> 'section-7',
			'section_title'	=> __( 'Vidéo', 'wp-tac' ),
			'section_callbk'=> 'wp_tac_settings_section_callback',
			'menu_slug'		=> 'wp_tac-plugin',
		),
	);
	
	return $sections ;
	
}

function wp_tac_settings_section_callback( $args ) {
	
	$sect_descr = array(

		'section-1'	=> __( 'Exemple de description <a href="#">test </a>', 'wp_tac-presentation' ),
		'section-2'	=> __( '', 'wp_tac-presentation' ),
		'section-3'	=> __( '', 'wp_tac-presentation' ),
		'section-4'	=> __( '', 'wp_tac-presentation' ),
		'section-5'	=> __( '', 'wp_tac-presentation' ),
		'section-6'	=> __( '', 'wp_tac-presentation' ),
		'section-7'	=> __( '', 'wp_tac-presentation' ),
	);

	$description = $sect_descr[ $args['id'] ] ;
	printf( '<span class="section-description">%s</span>', $description );

}

/**********************************************************************

* PARAM

**********************************************************************/


function wp_tac_settings_params_val() {
	$sections = array(
		array(
			'section_name'	=> 'section-p-1',
			'section_title'	=> __( 'Parameter', 'wp-tac' ),
			'section_callbk'=> 'wp_tac_settings_param_callback',
			'menu_slug'		=> 'wp_tac-plugin',
		),
	);
	return $sections ;
}

function wp_tac_settings_param_callback( $args ) {
	
	$sect_descr = array(
		'section-p-1'		=> __( 'Exemple de description <a href="#">test </a>', 'wp_tac-presentation' ),
	);

	$description = $sect_descr[ $args['id'] ] ;
	printf( '<span class="section-param">%s</span>', $description );

}

function wp_tac_settings_param_fields_val() {

	$section_1_fields = array (
		array( /* Ouverture automatique du panel avec le hashtag */
			'field_id'		=> 'field-1-1',
			'param_name'	=> 'hashtag', 
			'label'			=> __( 'hashtag', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> '#tarteaucitron',
		),
		array( /* d�sactiver le consentement implicite (en naviguant) ? */
			'field_id'		=> 'field-1-2',
			'param_name'	=> 'highPrivacy', 
			'label'			=> __( 'highPrivacy', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> false,
			'type'			=> 'checkbox',
		),
		array( /* le bandeau doit �tre en haut (top) ou en bas (bottom) ? */
			'field_id'		=> 'field-1-3',
			'param_name'	=> 'orientation', 
			'label'			=> __( 'orientation', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> 'bottom',
			'type'			=> 'select',
			'options'			=> array('top','bottom'),
		),
		array( /* Afficher un message si un adblocker est d�tect� */
			'field_id'		=> 'field-1-4',
			'param_name'	=> 'adblocker', 
			'label'			=> __( 'adblocker', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> false,
			'type'			=> 'checkbox',
		),
		array( /* afficher le petit bandeau en bas � droite ? */
			'field_id'		=> 'field-1-5',
			'param_name'	=> 'showAlertSmall', 
			'label'			=> __( 'showAlertSmall', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> false,
			'type'			=> 'checkbox',
		),
		array( /* Afficher la liste des cookies install�s ? */
			'field_id'		=> 'field-1-6',
			'param_name'	=> 'cookieslist', 
			'label'			=> __( 'cookieslist', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> true,
			'type'			=> 'checkbox',
		),
		array( /* supprimer le lien vers la source ? */
			'field_id'		=> 'field-1-7',
			'param_name'	=> 'removeCredit', 
			'label'			=> __( 'removeCredit', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> true,
			'type'			=> 'checkbox',
		),
		array( /* supprimer le lien vers la source ? */
			'field_id'		=> 'field-1-8',
			'param_name'	=> 'bypass', 
			'label'			=> __( 'bypass for visitor is not in the EU.', 'wp-tac' ), 
			'field_callbk'	=> 'wp_tac_settings_param_field_callback',
			'menu_slug'		=> 'wp_tac-plugin',
			'section_name'	=> 'section-p-1',
			'default'		=> false,
			'type'			=> 'checkbox',
		),
	);
	$section_fields = array(
		'section-p-1'	=> $section_1_fields,
	);

	return $section_fields ;
}
