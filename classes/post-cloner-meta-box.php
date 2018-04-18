<?php
abstract class Post_Cloner_Meta_Box
{
  //clones a post & its meta data
  public static function clone_post($post_id) {
      $title = get_the_title($post_id);
      $parent_post = get_post($post_id);
      $post = array(
        'post_title' => $title,
        'post_content' => $parent_post->post_content,
        'post_status' => 'Draft',
        'post_type' => $parent_post->post_type,
        'post_author' => $parent_post->post_author,
        'post_date' => $parent_post->post_date,
        'post_excerpt' => $parent_post->post_excerpt
      );

      //insert a new post into the database
      $duplicat_post_id = wp_insert_post($post);

      //deep copy all custom post meta data
      $custom_fields = get_post_custom($post_id);
      foreach ($custom_fields as $key => $values) {
          foreach($values as $value) {
            add_post_meta($duplicat_post_id, $key, $value);
          }
      }

      //update cloned post with a custom post meta field that stores it's original counterpart's ID, to be used as a canonical reference
      update_post_meta($duplicat_post_id, 'canonical_reference', $post_id);

      //reset and set the categories for the cloned post
      wp_set_post_categories($duplicat_post_id, array(1290), false);
    return;
  }

  //adds meta box width duplication toggle
  public static function add() {
    add_meta_box(
      'post_duplicator_meta_box_1',
      'Post Duplication',
      [self::class, 'html'],
      'post',
      'side',
'     core'
    );
  }

  //evaluations clone state, if possible saves data and clones post
  public static function save($post_id)
  {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

    if( !current_user_can( 'edit_post', $post_id ))
    return;

    if( !current_user_can( 'publish_post', $post_id ))
    return;

    $has_been_cloned = get_post_meta($post_id, 'has_been_cloned', true);
    if ($has_been_cloned == "true") {
      return;
    }

    if ( isset($_POST['post_duplicator_field']) && $_POST['post_duplicator_field'] == 'true' )  {
      //update parent custom post meta boolean indicating that this post has been cloned
      update_post_meta($post_id, 'has_been_cloned', $_POST['post_duplicator_field']);
      $has_been_cloned = get_post_meta($post_id, 'has_been_cloned', true);
      if ($has_been_cloned == "true") {
        self::clone_post($post_id);
        return;
      }
    }
  }


  //meta box html
  public static function html($post)
  {
    $value = get_post_meta($post->ID, 'has_been_cloned', true);
    if ($value == "true") { ?>
      <label for="post_duplicator_field">
        This post has already been cloned, but can be cloned again.
      </label>
    <?php } else { ?>
      <label for="post_duplicator_field">
        Check this box to clone this post with a canonical link.
      </label>
      <?php
    } ?>
      <input type="checkbox" name="post_duplicator_field" id="post_duplicator_field" value="true" <?php if(get_post_meta($post->ID, 'has_been_cloned', true) == "true") { echo 'checked disabled'; } ?>> clone<br>
  <?php }

}


//invoke class and methods for adding meta boxes and PUBLISHING(not saving) And Updating posts
add_action('add_meta_boxes', ['Post_Cloner_Meta_Box', 'add']);
add_action('publish_post', ['Post_Cloner_Meta_Box', 'save']);
add_action('post_updated', ['Post_Cloner_Meta_Box', 'save']);
