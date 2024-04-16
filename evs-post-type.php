<?php
/*
* Plugin Name: Blue Post Type
* Plugin URI: https://bluesol.io
* Description: This plugin is for add the custom post type in admin panel. So the User can subscribe the youtube channel just by one click.
* Version: 1.0
* Author: Abdul Raouf
* Author URI: https://bluesol.io
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain: blue_post_type
* Domain Path: /languages
*
*/
if (!defined('ABSPATH')){
    exit;
}

function evs_custom_post_product(){
    $lable = array(
        'name' => _x('Products', 'post type general name.'),
        'singular_name' => _x('Product', 'post singular name.'),
        'add_new' => _x('Add Product','book'),
        'add_new_item' => __('Add New Product'),
        'edit_item' => __('Edit product'),
        'new_item' => __('New Product'),
        'all_item' => __('All Product'),
        'view_item' => __('view Product'),
        'search_items' => __('Search Products'),
        'not_found' => __('No items founds'),
        'not_found_in_trash' => __('No Product found in trash'),
        'menu_name' => 'Product'
    );
    $arg = array(
        'labels' => $lable,
        'description' => 'Holds specific data related to products.',
        'public' => true,
        'menu_position' => 8,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'has_archive' => true
    );
    register_post_type('product', $arg);
}
add_action('init', 'evs_custom_post_product');

// Taxonomy like categories etc.

function evs_taxonomies_product(){
    $labels = array(
        'name' => _x('Product Categories', 'taxonomy general name'),
        'singular_name' => _x('Product Category', 'taxonomy single name'),
        'search_items' => __('Search Products Categories'),
        'all_items' => __('All Product Categories'),
        'parent_item' => __('Parent Product Category'),
        'parent_item_colon' => __('Parent Product Category'),
        'edit_item' => __('Edit Product Category'),
        'update_item' => __('Update Parent Category'),
        'add_new_item' => __('Add New Product Category'),
        'new_item_name' => __('New Product Category'),
        'menu_name' => __('Product Categories')
    );
    $arg = array(
        'labels' => $labels,
        'hierarchical' => true,
    );
    register_taxonomy('product_category', 'product', $arg);
}
add_action('init', 'evs_taxonomies_product', 0);

//custome field in post.

add_action('add_meta_boxes', 'product_price_box');
function product_price_box(){
    add_meta_box(
        'product_price_box',
        __('Product Price', 'evs_post_type'),
        'product_price_box_content',
        'product',
        'normal',
        'high'
    );
}

function product_price_box_content($post){
    wp_nonce_field(plugin_basename(__FILE__), 'product_price_box_content_nonce');
    $product_price = get_post_meta($post->ID, 'product_price', true);
    echo '<label for="product_price">Price</label>
    <input type="text" id="product_price" name="product_price" value="'.$product_price.'" placeholder="Enter Price"/>';
    
}

add_action('save_post', 'product_price_box_save');
function product_price_box_save($post_id){
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
    if(!wp_verify_nonce($_POST['produce_price_box_content_nonce'], plugin_basename(__FILE__)))
    return;
    if('page' == $_POST['post_type']){
        if(!current_user_can('edit_page', $post_id))
        return;
    }else{
        if(!current_user_can('edit_post', $post_id))
        return;
    }
    $product_price = $_POST['product_price'];
    update_post_meta($post_id, 'product_price', $product_price);
}

function show_products(){
    ob_start();
    $args = array(
        'post_type' => 'product',
    );
    $products = new WP_Query($args);
    if($products->have_posts()){
        while($products->have_posts()){
            $products->the_post();
            $image = wp_get_attachment_image_src(get_post_thumbnail_id(),'full');
        ?>
        <div class="row my-5">
        <div class="col-lg-4 py-3">
          <div class="card-blog">
            <div class="header">
              <div class="post-thumb">
                <img src="<?php echo $image[0]; ?>" alt="image">
              </div>
            </div>
            <div class="body">
              <h5 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
              <div class="text"><?php the_excerpt(); ?></div>
              <div class="post-date">Posted on <a href="#"><?php echo get_the_date(); ?></a></div>
            </div>
          </div>
        </div>
        </div>


<?php
        }
    }
    else{
        echo 'No products are available.';
    }
    return ob_get_clean();
}
add_shortcode('product_list', 'show_products');