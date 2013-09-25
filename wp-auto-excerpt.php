<?php
/*
Plugin Name: WP Auto Excerpt
Description: Allows you to make your (custom) post types auto-fill the excerpt content on save post.
Version: 1.0
Author: TCBarrett
Author URI: http://www.tcbrrett.com
Min WP Version: 3
*/

/**
 * Fetch the excerpt as WordPress would.
 */
function tcb_get_post_excerpt( $post ){
	return apply_filters( 'get_the_excerpt', $post->post_content );
}

/**
 * Save the excerpt to database
 */
function tcb_set_post_excerpt( $post ){
  remove_action( 'save_post', 'tcb_save_post_auto_excerpt' );
  $excerpt = tcb_get_post_excerpt( $post );
  wp_update_post( array('ID'=>$post->ID, 'post_excerpt'=>$excerpt) );
  add_action( 'save_post', 'tcb_save_post_auto_excerpt', 10, 2 );
}

/**
 * Hook into save_post to trigger storing the excerpt
 */
add_action( 'save_post', 'tcb_save_post_auto_excerpt', 10, 2 );
function tcb_save_post_auto_excerpt( $post_id, $post ){
  $post_type_ob = get_post_type_object( $post->post_type );
  if ( empty($post_type_ob->extras) )
    return $post_id;

  $extras = $post_type_ob->extras;
  if ( !$extras['auto_excerpt'] )
    return $post_id;

  if ( !current_user_can( 'edit_post', $post_id ) )
    return $post_id;

  if ( post_password_required( $post_id ) )
    return $post_id;

  $newpost = get_post( $post_id );

  if ( empty( $newpost->post_excerpt ) && !empty( $newpost->post_content ) ):
    tcb_set_post_excerpt( $newpost );
    return $post_id;
  endif;

  if( post_type_supports( $post->post_type, 'excerpt' ) )
    return $post_id;

  tcb_set_post_excerpt( $newpost );

  return $post_id;
}
