<?php
/* Functions to clone posts, produces a canonical reference link, and create meta box functionality to trigger duplication
* clones a post & its meta data, stores new meta data to be used for reference, resets category data
* @param
* No return
*/

//generate new canonical reference to the original post
function clone_post_canonical_link() {
  if (!is_single())
    return;
  //call globals to verify which paginaton
  global $page, $pages, $numpages, $multipage;
  //store canonical reference
  $canonical_reference = get_post_meta( get_the_ID(), 'canonical_reference', true);
  //if canonical reference exists and is not empty
  if (!empty($canonical_reference))
  {
    //get the post permalink of the canonical reference
    $canonical_url = get_permalink($canonical_reference);
    if ($page > 1) {
      $canonical_url = $canonical_url . $page . '/';
    }
    //echo that statement
    echo '<link rel="canonical" href="'. $canonical_url .'" />';
  }
}
add_action('wp_head', 'clone_post_canonical_link', 4);


//method to remove yoast & default canonical links
function remove_canonical() {
  //store canonical reference
  $canonical_reference = get_post_meta( get_the_ID(), 'canonical_reference', true);
  //if canonical reference exists and is not empty
  if (!empty($canonical_reference))
  {
    //if yoast is already generating a canonical url, disable the filter
    add_filter( 'wpseo_canonical', '__return_false', 10);
    //disable default canonical tag insertion
    remove_action('wp_head', 'rel_canonical');
    //disable canonical function (idrop only)
    remove_action('wp_head', 'get_canonical');
  }
}
add_action( 'wpseo_head', 'remove_canonical', 4);
