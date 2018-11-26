<?php

// Image Shortcode
add_shortcode( 'blitz-img', 'blitz_img_shortcode' );
function blitz_img_shortcode ( $atts ) {
    
    $atts = shortcode_atts(
        array(
            'id' => '',
            'size' => 'full',
            'layout' => 'intrinsic',
        ),
        $atts
    );
    
    if ( $atts['id'] == '' ) return '';
    
    $output = '';
    
    if ( get_query_var('amp') ) {
        
        
        $layout = '';
        if ( $atts['layout'] != '' ) $layout = ' layout="'.$atts['layout'].'"';
        
        $img = wp_get_attachment_image_src( $atts['id'], $atts['size'] );
        $output .= '<amp-img src="'.$img[0].'" width="'.$img[1].'" height="'.$img[2].'"'.$layout.'></amp-img>';
        
    } else {
        
        $img = wp_get_attachment_image_src( $atts['id'], 'full' );
        $output .= '<img src="'.$img[0].'">';
    }
    
    return $output;
}


?>