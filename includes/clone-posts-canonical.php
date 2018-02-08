<?php
/* Functions to clone posts, produces a canonical reference link, and create meta box functionality to trigger duplication
* clones a post & its meta data, stores new meta data to be used for reference, resets category data
* @param  [int] $post_id The Post you want to clone
* No return
*/


//generate new canonical reference to the original post
function clone_post_canonical_link($post_id) {
  if (!is_single())
    return;
  //store canonical reference
  $canonical_reference = get_post_meta($post_id, 'canonical_reference', true);
  //if canonical reference exists and is not empty
  if ($canonical_reference && $canonical_reference != '')
  {
    //if yoast is already generating a canonical url, disable the filter
    if ( function_exists( 'wpseo_canonical' ) ) {
      add_filter( 'wpseo_canonical', '__return_false' );
    }
    //get the post permalink of the canonical reference
    $canonical_url = get_post_permalink($canonical_reference);
    //echo that statement
    echo '<link rel="canonical" href="'. $canonical_url .'" />';
  }
}
