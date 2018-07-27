<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// Based on Anna's gist https://gist.github.com/annalinneajohansson/5290405
// http://codex.wordpress.org/Settings_API
// https://knowledge.parcours-performance.com/page-de-reglage-extension-wordpress/


// Based on Anna's gist https://gist.github.com/annalinneajohansson/5290405
// http://codex.wordpress.org/Settings_API



include_once('fields.php') ;

include_once('layout.php') ;


/**********************************************************************
* DEBUG ?
***********************************************************************/

define('ENABLE_DEBUG', false); // if true, the script will echo debug data

/**********************************************************************

* to set the title of the setting page see -- wp_tac_options_page()
* to set the sections see -- wp_tac_settings_sections_val()
* to set the fields see -- wp_tac_settings_fields_val()

**********************************************************************/

// create the settings page and it's menu
add_action( 'admin_menu', 'wp_tac_admin_menu' );
// set the content of the admin page
add_action( 'admin_init', 'wp_tac_sections_admin_init' );
add_action( 'admin_init', 'wp_tac_param_admin_init' );
// set the content of the admin page
add_action('wp_enqueue_scripts', 'wp_tac_front_init',PHP_INT_MAX);


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
	clear_cache_oembed();

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

function wp_tac_sections_admin_init() {

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
function clear_cache_oembed() {
	global $wpdb;
	if($wpdb->query("SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '%_oembed_%'")){
		$delres = $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%_oembed_%'");
		
		if($delres){
			function sample_admin_notice__success() {
				$class = 'notice notice-success is-dismissible';
				$message = __( 'Done!', 'wp-tac' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
			}
			add_action( 'admin_notices', 'sample_admin_notice__success' );

		}else{
			function sample_admin_notice__error() {
				$class = 'notice notice-error';
				$message = __( 'Irks! An error has occurred.', 'wp-tac' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
			}
			add_action( 'admin_notices', 'sample_admin_notice__error' );

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

		tarteaucitron.init({'.
			$paramsjs.'
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
			$tac_user = "";
			$tac_more = "";

			if ($enqueue = $value['enqueue']) {
				wp_dequeue_script( $enqueue );
			}

			if (isset($value['job']) && $job = $value['job']) {

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
									$wp_embed->arr_optionnal[$job] = $arr_optionnal;
									$wp_embed->arr_search[$job] = $arr_search;

									function wrap_youtube_with_div($html, $url, $attr) {
										global $wp_embed;
										$job='youtube';
										$pattern = $wp_embed->arr_search[$job]['ereg'];
										$subject = $wp_embed->arr_search[$job]['replace'];

										preg_match($pattern, $html, $matches);



										//Match 1
										//Full match	0-177	`<iframe width="800" height="450" src="https://www.youtube.com///embed/_Iq3hW6DASw?feature=oembed&rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`
										//Group 1.	15-18	`800`
										//Group 2.	28-31	`450`
										//Group 3.	38-79	`https://www.youtube.com/embed/_Iq3hW6DASw`
										//Group 4.	80-150	`feature=oembed&rel=0" frameborder="0" allow="autoplay; encrypted-media`
										//Group 5.	151-177	` allowfullscreen></iframe>`


										if(empty($matches[3])) {
											return $html;
										}

										$video_id		= basename($matches[3]);

										$width			= $matches[1] ;
										$height			= $matches[2] ;

										$theme			= empty($wp_embed->arr_optionnal['theme']) ? "0":"1" ;
										$rel			= empty($wp_embed->arr_optionnal['rel']) ? "0":"1" ;
										$controls		= empty($wp_embed->arr_optionnal['controls']) ? "0":"1" ;
										$showinfo		= empty($wp_embed->arr_optionnal['showinfo']) ? "0":"1" ;
										$autoplay		= empty($wp_embed->arr_optionnal['autoplay']) ? "0":"1" ;

										$search	= array('%video_id', '%width', '%height', '%theme', '%rel', '%controls', '%showinfo', '%autoplay');
										$replace = array( $video_id,	$width,	$height,	$theme,	$rel,	$controls,	$showinfo,	$autoplay);

										$html = str_replace($search, $replace, $subject);

										return $html;
									}
									add_filter('embed_oembed_html', 'wrap_youtube_with_div', 100, 3);
									add_filter('oembed_result', 'wrap_youtube_with_div', 100, 3);
								}
								else if($job == 'vimeo'){
									global $wp_embed;
									$wp_embed->arr_optionnal[$job] = $arr_optionnal;
									$wp_embed->arr_search[$job] = $arr_search;

									function wrap_vimeo_with_div($html, $url, $attr) {
										global $wp_embed;
										$job='vimeo';

										$pattern = $wp_embed->arr_search[$job]['ereg'];
										$subject = $wp_embed->arr_search[$job]['replace'];

										preg_match($pattern, $html, $matches);

										if(empty($matches[1])) {
											return $html;
										}

										$video_id		= basename($matches[1]);

										// bug VC lecteur video
										// 100% ecrit 1060
										//$width			= $matches[2] ;
										//$height			= $matches[3] ;
										$width			= '100%' ;
										$height			= '100%' ;

										$search	= array('%video_id', '%width', '%height');
										$replace = array( $video_id,	$width,	$height);

										$html = str_replace($search, $replace, $subject);
										return $html;

									}
									add_filter('embed_oembed_html', 'wrap_vimeo_with_div', 100, 3);
									add_filter('oembed_result', 'wrap_vimeo_with_div', 100, 3);
								}

									//if($job=='googlemaps'){
									//	global $post_map;
									//	$post_map->arr_optionnal = $arr_optionnal;
									//	$post_map->arr_search = $arr_search;

									//	function multiple_string_replacements ( $content ) {
									//		global $post_map;

									//		$pattern = $post_map->arr_search['ereg'];
									//		$subject = $post_map->arr_search['replace'];

									//		preg_match($pattern, $content, $matches);

									//		$all			 = $matches[0] ;
									//		$height		 = $matches[1] ;
									//		$style			= $matches[2] ;
									//		$div_map_ovrlay = $matches[3] ;
									//		$div_google_map = $matches[4] ;

									//		$zoom			= $wp_embed->arr_optionnal['zoom'] ;
									//		$latitude		= $wp_embed->arr_optionnal['latitude'] ;
									//		$longitude	 = $wp_embed->arr_optionnal['longitude'] ;
									//		$widthpx		= $wp_embed->arr_optionnal['widthpx'] ."px";
									//		$heightpx		= $height."px" ;

									//		$search	= array('$zoom', '$latitude', '$longitude', '$widthpx', '$heightpx');
									//		$replace = array( $zoom,	$latitude,	$longitude,	$widthpx,	$heightpx);
									//		$tag_canvas = str_replace($search, $replace, $subject);
									//	//echo __FILE__, ":",__LINE__,"","<pre>", print_r( $matches ,1),"</pre>";
									//		//	supprime les ancien tag et repasse par le nouveau
									//		//	$search	= array( $div_map_ovrlay,	$div_google_map);
									//		//	$replace = array( $tag_canvas,	'');
									//		//	$search	= array( $div_google_map);
									//		//	$replace = array( $tag_canvas);
									//		//	$content = str_replace($search, $replace, $content);

									//		return $content ;
									//	}

									//	//add_filter( 'the_post', 'multiple_string_replacements', 10, 1 );
									//	add_filter( 'after_google_map', 'multiple_string_replacements', 10, 1 );

									//}

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

			$value['more'] = json_decode($value['more']);
			$UA_more = $value['more'][0];

			// anonymize Ip
			if(isset($value['more']) && $value['status'] && $arr_params['anonymizeIp']){
				$tac_more = '
					tarteaucitron.user.'.$UA_more.' = function () { 
						'.$value['more'][1].'
					};
				';
			}

			 wp_add_inline_script('wp_tac', '

					'.$tac_user.'
					(tarteaucitron.job = tarteaucitron.job || []).push(\''.$job.'\');
					'.$tac_more.'
					

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
	$value 	= esc_attr( $settings[$field]['status'] );
	$user 	= esc_attr( $settings[$field]['user'] );
	$more	= (esc_attr( $settings[$field]['more'] ))? $settings[$field]['more']  : json_encode($arguments['more']);


	$html = '<label class="switch" for="checkbox_tac['.$field.'][status]">';
	$html .= '<input type="checkbox" id="checkbox_tac['.$field.'][status]" name="wp_tac-plugin-settings['.$field.'][status]" 	value="1" ' ;
	$html .= checked( 1, $value, false ) . '/>';
	$html .= '<span class="slider round"></span>';
	$html .= '<input type="text" id="text_tac['.$field.'][marqueur]" 	name="wp_tac-plugin-settings['.$field.'][marqueur]" 	value="'.base64_encode ( $marqueur ) .'" />' ;
	$html .= '<input type="text" id="text_tac['.$field.'][job]" 		name="wp_tac-plugin-settings['.$field.'][job]" 			value="'.$job.'" />';
	$html .= '<input type="text" id="text_tac['.$field.'][enqueue]" 	name="wp_tac-plugin-settings['.$field.'][enqueue]" 		value="'. $enqueue .'" />';

	$html .= '</label>';
	
	$html .= '<span class="user">';
	if($jobuser){
		$html .= '<input type="hidden" id="checkbox_tac['.$field.'][jobuser]" 	name="wp_tac-plugin-settings['.$field.'][jobuser]" 	value="'. $jobuser .'" />';
		$html .= '<input type="text" id="text_tac['.$field.'][user]" 			name="wp_tac-plugin-settings['.$field.'][user]"  	value="'. $user .'" placeholder="'.$placeholder.'" />';
	}
	if($jobuser && $more !="null"){
		$html .= '<textarea id="text_tac['.$field.'][more]" name="wp_tac-plugin-settings['.$field.'][more]"  rows="2" cols="50" type="textarea">'. $more .'</textarea>';
		//$html .= '<input type="text" id="text_tac['.$field.'][more]" 			name="wp_tac-plugin-settings['.$field.'][more]" 	value="'. $more .'" />';
	}
	$html .= '</span>';

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


