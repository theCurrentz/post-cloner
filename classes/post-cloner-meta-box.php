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

    //return the id for redirection purposes
    return get_site_url() .'/wp-admin/post.php?post='.$duplicat_post_id.'&action=edit';
  }

  //adds meta box width duplication toggle
  public static function add() {
    add_meta_box(
      'post_duplicator_meta_box_1',
      'Post Duplication',
      [self::class, 'html'],
      'post',
      'side',
      'core'
    );
  }

  //evaluations clone state, if possible saves data and clones post
  public static function post_clone_execute($post_id)
  {
    $post_id = $_POST['post_id'];
    $reponse = array();

    $has_been_cloned = get_post_meta($post_id, 'has_been_cloned', true);

    if ( isset($_POST['post_duplicator_field']) )  {
      //update parent custom post meta boolean indicating that this post has been cloned
      update_post_meta($post_id, 'has_been_cloned', $_POST['post_duplicator_field']);
      $has_been_cloned = get_post_meta($post_id, 'has_been_cloned', true);
      if ($has_been_cloned == "true") {
        $duplicat_post_url = self::clone_post($post_id);
      }
      $response['message'] = "Cloned! Redirecting you to the clone...";
      $response['post_url'] = $duplicat_post_url;
    }
    else
    {
      $response['message'] = "Error Cloning!";
      $response['post_url'] = "";
    }
    return $response;
  }


  //meta box html
  public static function html($post)
  {
    //ajax clone script
    ?>
    <script type="text/javascript" >
    	jQuery(document).ready(function($) {

    		//execute post
        $('#post_duplicator_field').click( function()
          {
            jQuery.ajax({
              type: "POST",
              url: "/wp-admin/admin-ajax.php",
              data : {
                'action': "post_clone_execute",
                'post_duplicator_field': 'true',
                'post_id': '<?php echo $post->ID?>'
              },
              success: function(response) {
                console.log(response['message']);
                //redirect user to cloned post draft
                var postCloneURL = response['post_url'],
                    postClone = document.getElementById('postClone'),
                    postCloneBoxStyle = 'z-index: 9999; background: white; position: fixed; top: 45%; left: 50%; right: 50%; height: 100px; width: 200px; padding: 20px; display: flex; flex-direction: column; justify-content: space-evenly;  box-shadow: 0px 0px 50rem 50rem rgba(0,0,0,.5);',
                    postCloneStayHere = document.createElement('div');

                postCloneStayHere.setAttribute('class', 'button button-primary button-large');
                postCloneStayHere.innerHTML = 'Stay Here';
                postClone.setAttribute('style', postCloneBoxStyle);
                postClone.innerHTML = '<a href="'+postCloneURL+'" id="postCloneURL" style="display: block;" class="button button-primary button-large">Go To Clone</a>';
                postClone.appendChild(postCloneStayHere);
                console.log(postClone);

                postCloneStayHere.addEventListener('click',
                  function() {
                      postClone.setAttribute('style', 'display: none;');
                  }
                );
              }
          });
    	   });
        });
	</script>
  <?php
    echo '<div id="postClone"></div>';
    $value = get_post_meta($post->ID, 'has_been_cloned', true);
    if ($value == "true") { ?>
      <label for="post_duplicator_field">
        This post was previously cloned.
      </label>
    <?php } else { ?>
      <label for="post_duplicator_field">
        Clone with canonical ref
      </label>
      <?php
    } ?>
    <br><br>
      <input type="text" class="hidden" name="post_id" value="<?php echo $post->ID?>"/>
      <input class="button button-primary button-large" type="button" name="post_duplicator_field" id="post_duplicator_field" value="Clone"><br>
  <?php }

}


//invoke class and methods for adding meta boxes and PUBLISHING(not saving) And Updating posts
add_action('add_meta_boxes', ['Post_Cloner_Meta_Box', 'add']);
// add_action('publish_post', ['Post_Cloner_Meta_Box', 'save']);
// add_action('post_updated', ['Post_Cloner_Meta_Box', 'save']);
add_action( 'wp_ajax_post_clone_execute',  'post_clone_execute' );
function post_clone_execute()
{
  global $post;
  $response = Post_Cloner_Meta_Box::post_clone_execute($post->post_id);
  wp_send_json($response);
}
