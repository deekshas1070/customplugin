<?php
/**
* Plugin Name: Service Leads
* Plugin URI: https://techmind.co.in/
* Description: This is Service leads plugin which is used for leads generation.
* Version: 0.1
* Author: Techmind
* Author URI: https://techmind.co.in/
**/

if ( !defined( 'ABSPATH' ) ) exit;

/* Create Tables on Plugin Activation Start Here */
register_activation_hook( __FILE__, "activate_myplugin" );
register_deactivation_hook( __FILE__, "deactivate_myplugin" );


function activate_myplugin() {
	init_db_myplugin();
    init_custom_postType();
    init_custom_taxonomies();
    init_store_custom_service();
    init_edit_custom_service();
    init_delete_custom_service();
    add_metabox_post_sidebar();
    enable_sidebar_posts();
    init_store_custom_service_ques();
    init_delete_custom_service_ques();
    init_service_lead();
    init_menu_bar();
}

function deactivate_myplugin() {
    // global $wpdb, $table_prefix;
    // $service_table = $table_prefix.'services';
    // $question_table = $table_prefix.'service_questions';

    // $sql = "DROP TABLE IF EXISTS $service_table";
    // $wpdb->query($sql);

    // $sql1 = "DROP TABLE IF EXISTS $question_table";
    // $wpdb->query($sql1);
    
    delete_option("devnote_plugin_db_version");
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Custom_Table_Service_List_Table extends WP_List_Table
{
  
    function __construct()
    {
        global $status, $page;
         parent::__construct( [
			'singular' => __( 'Form', 'sp' ), 
			'plural'   => __( 'Forms', 'sp' ), 
			'ajax'     => false 

		]);
    }

 
//    function column_name($item){
//         $actions = array(
//             'edit' => sprintf('<a href="?page=persons_form&id=%s">%s</a>', $item['id'], __('Edit', 'custom_table_example')),
//             'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'custom_table_example')),
//         );
//       return sprintf('%s %s',
//             $item['id'],
            
//             $this->row_actions($actions)
//         );
//     }  

		
	function column_default($item,$column_name)
    {
        return $item[$column_name];
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" class="record_id"/>',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'id' => __('#ID', 'custom_service_table'),
            'first_name' => __('First Name', 'custom_service_table'),
            'last_name' => __('Last Name', 'custom_service_table'),
            'email' => __('Email', 'custom_service_table'),
            'phone_number' => __('Phone Number', 'custom_service_table'),
            'service_name' => __('Service Name', 'custom_service_table'),
            'service_question' => __('Service Question', 'custom_service_table'),
            'service_answers' => __('Service Answers', 'custom_service_table'),
        );
        return $columns;
    }

   function get_sortable_columns(){
        $sortable_columns = array(
            'first_name' => array('first_name', true),
            'last_name' => array('last_name', false),
            'email' => array('email', false),
            'phone_number' => array('phone_number', false),
            'service_name' => array('service_name', false),
            'service_question' => array('service_question', false),
            'service_answers' => array('service_answers', false),
        );
        return $sortable_columns;
    }

   function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

   function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_leads'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_leads'; // do not forget about tables prefix
        $per_page = 3; // constant, how much records will be shown per page

        $columns = $this->get_columns();
		
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

      
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

      
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

function init_menu_bar(){
   add_submenu_page('edit.php?post_type=service-leads-posts', 'Service Lead Enteries', 'Service Lead Enteries', "manage_options", 'service-lead-enteries', 'custom_table_service_lead_enteries', '');
}
add_action('admin_menu', 'init_menu_bar');

function custom_table_service_lead_enteries()
{
    global $wpdb;

    $table = new Custom_Table_Service_List_Table();
   ?>

    <form method="post">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
    <?php  $table->prepare_items();
    $table->search_box('Search Service lead Entry', 'search');
    $table->display(); ?>
</form>
<?php
   
}



function init_db_myplugin() {
    global $table_prefix, $wpdb;
	$servicesTable = $table_prefix . 'services';
	$services_questionTable = $table_prefix . 'service_questions';
	$services_leads = $table_prefix . 'service_leads';
	if( $wpdb->get_var( "show tables like '$servicesTable'" ) != $servicesTable ) {

		$sql = "CREATE TABLE `$servicesTable` (";
		$sql .= " `id` int(11) NOT NULL auto_increment, ";
        $sql .= " `tag_id` varchar(255) NOT NULL, ";
		$sql .= " `service_name` varchar(500) NOT NULL, ";
		$sql .= " `service_slug` varchar(500) NOT NULL, ";
		$sql .= " `service_description` LONGTEXT NOT NULL, ";
		$sql .= " PRIMARY KEY `service_id` (`id`) ";
		$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

        $sql1 = "CREATE TABLE `$services_questionTable` (";
		$sql1 .= " `id` int(11) NOT NULL auto_increment, ";
		$sql1 .= " `service_id` varchar(500) NOT NULL, ";
		$sql1 .= " `service_postid` varchar(255) NOT NULL, ";
		$sql1 .= " `question_title` varchar(500) NOT NULL, ";
		$sql1 .= " `answers` varchar(500) NOT NULL, ";
		$sql1 .= " PRIMARY KEY `question_id` (`id`) ";
		$sql1 .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		dbDelta( $sql1 );

        $sql2 = "CREATE TABLE `$services_leads` (";
		$sql2 .= " `id` int(11) NOT NULL auto_increment, ";
		$sql2 .= " `first_name` varchar(255) NOT NULL, ";
		$sql2 .= " `last_name` varchar(255) NOT NULL, ";
		$sql2 .= " `email` varchar(255) NOT NULL, ";
		$sql2 .= " `phone_number` varchar(255) NOT NULL, ";
		$sql2 .= " `service_name` varchar(255) NOT NULL, ";
		$sql2 .= " `service_question` LONGTEXT NOT NULL, ";
		$sql2 .= " `service_answers` LONGTEXT NOT NULL, ";
		$sql2 .= " PRIMARY KEY `lead_id` (`id`) ";
		$sql2 .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		dbDelta( $sql2 );
	}
}
/* Create Tables on Plugin Activation Ends Here */

function init_custom_postType() {
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Service Leads', 'Post Type General Name'),
        'singular_name'       => _x( 'Service Lead', 'Post Type Singular Name'),
        'menu_name'           => __( 'Service Leads'),
        'parent_item_colon'   => __( 'Parent Service Lead'),
        'view_item'           => __( 'View Service Lead Question'),
        'add_new_item'        => __( 'Add New Service Lead Question'),
        'add_new'             => __( 'Add New Question'),
        'all_items'           => __( 'All Service Questions'),
        'edit_item'           => __( 'Edit Service Leads'),
        'update_item'         => __( 'Update Service Leads'),
        'search_items'        => __( 'Search Service Leads'),
        'not_found'           => __( 'Not Found'),
        'not_found_in_trash'  => __( 'Not found in Trash'),
    );
        
    // Set other options for Custom Post Type
        
    $args = array(
        'label'               => __( 'service-leads-posts'),
        'description'         => __( 'Service Leads'),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        'taxonomies'          => array( 'services' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-clipboard',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,

    );
        
    register_post_type( 'service-leads-posts', $args );
}
add_action( 'init', 'init_custom_postType', 0 );

function init_custom_taxonomies() {
    register_taxonomy('services', 'service-leads-posts', array(
      // Hierarchical taxonomy (like categories)
      'hierarchical' => true,
      // This array of options controls the labels displayed in the WordPress Admin UI
      'labels' => array(
        'name' => _x( 'Services', 'taxonomy general name' ),
        'singular_name' => _x( 'Services', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Services' ),
        'all_items' => __( 'All Services' ),
        'parent_item' => __( 'Parent Service' ),
        'parent_item_colon' => __( 'Parent Service:' ),
        'edit_item' => __( 'Edit Service' ),
        'update_item' => __( 'Update Service' ),
        'add_new_item' => __( 'Add New Service' ),
        'new_item_name' => __( 'New Service Name' ),
        'menu_name' => __( 'Services' ),
      ),
      // Control the slugs used for this taxonomy
      'rewrite' => array(
        'slug' => 'services', // This controls the base slug that will display before each term
        'with_front' => false, // Don't display the category base before "/locations/"
        'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
      ),
    ));
  }
add_action( 'init', 'init_custom_taxonomies', 0 );


add_action( 'wp_enqueue_scripts', 'my_custom_script_load' );
function my_custom_script_load(){
    wp_enqueue_style('service-leads-style', plugins_url('public/css/style.css', __FILE__ ), '1.0.0' );
    wp_enqueue_script( 'my-custom-script', plugins_url('public/js/script.js', __FILE__ ), '', '', true);
}

function init_store_custom_service() {
    global $table_prefix, $wpdb;
	$servicesTable = $table_prefix . 'services';
    $service_slug = sanitize_title($_REQUEST['tag-name']);

    $term = get_term_by( 'name', $_REQUEST['tag-name'], 'services' ); 

    $wpdb->insert($servicesTable, array(
        'tag_id' => $term->term_id,
        'service_name' => $_REQUEST['tag-name'],
        'service_slug' => $service_slug,
        'service_description' => $_REQUEST['description'],
    ));
} 
add_action( 'create_services', 'init_store_custom_service', 10, 2 );

function init_edit_custom_service() {
    global $table_prefix, $wpdb;
	$servicesTable = $table_prefix . 'services';
    $serviceID = $_REQUEST['tag_ID'];

    $wpdb->update($servicesTable, array('service_name' => $_REQUEST['name'],'service_slug' => $_REQUEST['slug'],'service_description' => $_REQUEST['description']),array( 'tag_id' => $serviceID ));
} 
add_action( 'edited_services', 'init_edit_custom_service', 10, 2 );

function init_delete_custom_service() {
    global $table_prefix, $wpdb;
	$servicesTable = $table_prefix . 'services';

    $wpdb->delete( $servicesTable, array( 'tag_id' => $_REQUEST['tag_ID'] ) );
} 
add_action( 'delete_services', 'init_delete_custom_service', 10, 2 );


/* Add meta box field in cyustom post type code */
/*
add_action('admin_init','add_metabox_post_sidebar');
function add_metabox_post_sidebar()
{
    add_meta_box("Enable Sidebar", "Enable Sidebar", "enable_sidebar_posts", "service-leads-posts", "bottom", "high");
}

function enable_sidebar_posts(){
    global $post;
    ?>

    <label for="post_sidebar">Enable Sidebar:</label>
    <input type="checkbox" name="post_sidebar" id="post_sidebar">
    <p><em>( Check to enable sidebar. )</em></p>
    <?php
}
*/
/* Code ends Here */

add_action('post_updated', 'init_store_custom_service_ques');
function init_store_custom_service_ques(){
    if('service-leads-posts' === $_REQUEST['post_type']){

        global $table_prefix, $wpdb;
        $servicesqTable = $table_prefix . 'service_questions';

        $service_name = $_REQUEST['tax_input']['services'];
        $service_count = count($service_name);
        for($i =0; $i<= $service_count; $i++){
            if($service_name[$i] != 0){
            $serviceID = $service_name[$i];
            $serviceids = explode(",", $serviceID);
            }
        }
        $serviceIDs = implode(",", $serviceids);

        $post_ID = $_REQUEST['post_ID'];
        $quetion_title = $_REQUEST['post_title'];
        $answer_title = $_REQUEST['excerpt'];

        $checkIfExists = $wpdb->get_var("SELECT id FROM $servicesqTable WHERE service_postid = '$post_ID'");
        if($checkIfExists == NULL){
            $wpdb->insert($servicesqTable, array(
                'service_id' => $serviceIDs,
                'service_postid' => $post_ID,
                'question_title' => $quetion_title,
                'answers' => $answer_title,
            ));
        }else{
            $wpdb->update($servicesqTable, array('service_id' => $serviceIDs,'question_title' => $quetion_title,'answers' => $answer_title),array( 'service_postid' => $post_ID )); 
        }
    }
}

// add_action('trashed_post', 'init_delete_custom_service_ques' , 10);
// function init_delete_custom_service_ques(){
//     if('service-leads-posts' === $_REQUEST['post_type']){
//         global $wpdb;
//         $servicesqTable = $table_prefix . 'service_questions';
//         // print_r($_REQUEST); exit();
//         $wpdb->delete( $servicesqTable, array( 'service_postid' => $_REQUEST['post_ID'] ) );
//     }
// }


/* Create shortcode For Thank you page */

function init_service_lead() {
    include plugin_dir_path(__FILE__) . 'service-leads-quote.php';
}
add_shortcode('service-leads-code', 'init_service_lead');

/* Shortcode Ends here*/