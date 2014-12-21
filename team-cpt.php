<?php
/**
 * Plugin Name: Genesis Team CPT 
 * Plugin URI: https://llama-press.com
 * Description: Use this plugin to add a Team CPT to be used with the "team" sortcode or a LlamaPress team page template,
 *              this plugin can only be used with the Genesis framework.
 * Version: 1.0
 * Author: LlamaPress
 * Author URI: https://llama-press.com
 * License: GPL2
 */

/*  Copyright 2014  LlamaPress LTD  (email : info@llama-press.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//include plugins
include( plugin_dir_path( __FILE__ ) . 'inc/plugins/plugins.php');

/**
 * This class creates a custom post type lp-team, this post type allows the user to create 
 * team member profiles to display in the Team page template.
 *
 * @since 1.0
 * @link https://llama-press.com
 */
class lpTeam {
    /**
    * Initiate functions.
    *
    * @since 1.0
    * @link https://llama-press.com
    */
    public function __construct( ){
        
        /** Create team custom post type */
        add_action( 'genesis_init', array( $this, 'team_post_type' ) );
        
        /** Register department Taxonomy */
        add_action( 'genesis_init', array( $this, 'create_team_tax' ) );
        
        /* Add team_meta_boxes */
        add_action( 'do_meta_boxes', array( $this, 'team_meta_boxes' ) );
        
        /* initiate save_team_data function  */
        add_action('save_post', array( $this, 'save_team_data' ) );
        
        /* Remove permalink section from team members edit post screen  */
        add_action('admin_print_styles-post.php', array( $this, 'posttype_admin_css' ) ); 
        
        /* Creates team featured image for archive grid */
        add_image_size( 'lp-team', 190, 190, TRUE );
        
        /* create text domain */
        load_plugin_textdomain( 'lp', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

    }

    /**
    * Registeres the custom post type lp-team.
    * 
    * This CTA is used to create team member profiles to populate the Team page template.
    *
    * @since 1.0
    * @link https://llama-press.com
    */
    public function team_post_type() {
        register_post_type( 'lp-team',
            array(
                'labels' => array(
                    'name' => __( 'Team', 'lp' ),
                    'singular_name' => __( 'Team member', 'lp' ),
                    'all_items' => __( 'All Team members', 'lp' ),
                    'add_new' => _x( 'Add new Team member', 'Team member', 'lp' ),
                    'add_new_item' => __( 'Add new Team member', 'lp' ),
                    'edit_item' => __( 'Edit Team member', 'lp' ),
                    'new_item' => __( 'New Team member', 'lp' ),
                    'view_item' => __( 'View Team member', 'lp' ),
                    'search_items' => __( 'Search Team members', 'lp' ),
                    'not_found' =>  __( 'No Team members found', 'lp' ),
                    'not_found_in_trash' => __( 'No Team members found in trash', 'lp' ), 
                    'parent_item_colon' => ''
                ),
                'exclude_from_search' => true,
                'has_archive' => true,
                'hierarchical' => true,
                'taxonomies'   => array( 'lp-department' ),
                'public' => true,
                'menu_icon' => 'dashicons-groups',
                'rewrite' => array( 'slug' => 'team' ),
                'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
                'query_var'           => false,
            )
        );
        flush_rewrite_rules(); 
    }

    /**
    * Registeres the custom meta box.
    * 
    * This custom meta box allows the user to select what department(s) the team member belongs to.
    *
    * @since 1.0
    * @link https://llama-press.com
    */
    public function create_team_tax() {
        register_taxonomy(
            'lp-department',
            'lp-team',
            array(
                'label' => __( 'Departments', 'lp' ),
                'hierarchical' => false,
                'show_admin_column' => true,
                'has_archive' => false,
                'query_var'           => false,
                'labels' => array('name' => _x( 'Departments', 'taxonomy general name', 'lp' ),
                                  'singular_name' => _x( 'Department', 'taxonomy singular name', 'lp' ),
                                  'search_items' => __( 'Search Departments', 'lp' ),
                                  'popular_items'              => __( 'Largets Departments', 'lp' ),
                                  'all_items'                  => __( 'All Departments', 'lp' ),
                                  'parent_item'                => null,
                                  'parent_item_colon'          => null,
                                  'edit_item'                  => __( 'Edit Department', 'lp' ),
                                  'update_item'                => __( 'Update Department', 'lp' ),
                                  'add_new_item'               => __( 'Add New Department', 'lp' ),
                                  'new_item_name'              => __( 'New Department', 'lp' ),
                                  'separate_items_with_commas' => __( 'Separate departments with commas', 'lp' ),
                                  'add_or_remove_items'        => __( 'Add or remove departments', 'lp' ),
                                  'choose_from_most_used'      => __( 'Choose from the most common departments', 'lp' ),
                                  'not_found'                  => __( 'No departments found.', 'lp' ),
                                  'menu_name'                  => __( 'Departments', 'lp' ),)
            )
        );
    }
    
    /**
    * Registeres the custom meta box.
    * 
    * This custom meta box is for the team member position, LinkedIn and Google+ profile URLs.
    *
    * @since 1.0
    * @link https://llama-press.com
    */
    public function team_meta_boxes() {
        add_meta_box(   'team-details', __( 'Team details', 'lp' ),  array( $this, 'team_metabox' ), 'lp-team', 'normal', 'high');
    }
 
    /**
    * Creates custom meta box HTML.
    *
    * @since 1.0
    * @link https://llama-press.com
    * @param array $post
    * @return mixed HTML.
    */
    public function team_metabox($post) {
        // get the custom meta values
        $details = get_post_meta($post->ID, 'team-details');

        //crete HTML and add custom values if they are set
        ?>
            <input type="hidden" name="team_noncename" id="team_noncename" value="<?php echo wp_create_nonce( 'team'.$post->ID );?>" />

            <label for="team-details[]" class="row-title"><?php _e( 'Position', 'lp' )?></label><br/>
            <input name="team-details[]" type="text" value="<?php  echo $details[0][0]; ?>" /><br/>

            <label for="team-details[]" class="row-title"><?php _e( 'Google+ profile link', 'lp' )?></label><br/>
            <input name="team-details[]" type="url" value="<?php  echo $details[0][1]; ?>" /><br/>

            <label for="team-details[]" class="row-title"><?php _e( 'LinkedIn profile link', 'lp' )?></label><br/>
            <input name="team-details[]" type="url" value="<?php  echo $details[0][2]; ?>" /><br/>

     <?php
    }

    /**
    * Saves the data from the custom meta box when the post is updated.
    * 
    * @see team_post_type()
    * @since 1.0
    * @link https://llama-press.com
    * @param int $post_id Id of the post.
    * @return mixed
    */
    public function save_team_data($post_id) {  
        // verify this came from the our screen and with proper authorization.
        if ( !wp_verify_nonce( $_POST['team_noncename'], 'team'.$post_id )) {
            return $post_id;
        }

        // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            return $post_id;

        // Check permissions
        if ( !current_user_can( 'edit_post', $post_id ) )
            return $post_id;

        // We're authenticated: we need to find and save the data   
        $post = get_post($post_id);
        if ($post->post_type == 'lp-team') { 
            if(isset($_POST['team-details'])){
                $custom = $_POST['team-details'];
                $old_meta = get_post_meta($post->ID, 'team-details', true);
                // Update post meta
                if(!empty($old_meta)){
                    update_post_meta($post->ID, 'team-details', $custom);
                } else {
                    add_post_meta($post->ID, 'team-details', $custom, true);
                }
            }

        }   

        return $post_id;
    }
    
    /**
    * Remove permalink.
    * 
    * We dont need to display the permalink or the view post link on the edit screen so this function removes it.
    * 
    * @since 1.0
    * @link https://llama-press.com
    */
    public function posttype_admin_css() {
        global $post_type;
        if($post_type == 'lp-team') {
            echo '<style type="text/css">#edit-slug-box, #view-post-btn, #post-preview, .updated #edit-slug-box, .preview{ display: none !important; }</style>';
        }
    }
    
}

$team = new lpTeam();

?>