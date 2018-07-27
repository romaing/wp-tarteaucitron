<?php

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

		'section-1'	=> __( '', 'wp_tac-presentation' ),
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
		'section-p-1'		=> __( '', 'wp_tac-presentation' ),
	);

	$description = $sect_descr[ $args['id'] ] ;
	printf( '<span class="section-param">%s</span>', $description );

}




