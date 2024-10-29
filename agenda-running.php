<?php
/*
Plugin Name: Epingle Un Dossard
Description: Ce plugin gère votre calendrier de course, les résultats et vos RP.
Author: Jean-Marc MALECOT
Author URI: http://www.iazone.fr
Version: 1.8
*/
$list_type = array("5k"=>"5Km","10k"=>"10Km","15k"=>"15km","semimarathon"=>"Semi-Marathon","marathon"=>"Marathon","100k"=>"100Km","24h"=>"24h","cross"=>"Cross","trailurbain"=>"Trail Urbain", "trail"=>"Trail Nature","autre"=>"Autre");

class AgendaRunning {

	public function __construct(){
		//add_action( 'plugins_loaded', array( $this, 'load_textdomain') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script_style' ) );
		add_action( 'init', array( $this, 'event_post_type'), 0 );
		add_action('add_meta_boxes', array( $this, 'event_info_metabox') );
		add_action( 'save_post', array( $this, 'save_event_info' ) );

		add_filter( 'manage_edit-agenda-running_columns', array( $this, 'custom_columns_head'), 10 );
		add_action( 'manage_agenda-running_posts_custom_column', array( $this, 'custom_columns_content'), 10, 2 );
		add_filter( 'the_content', array( $this, 'AgendaRunning_single_content') );


		add_filter("manage_edit-agenda-running_sortable_columns", array( $this, "agenda_running_sortable_columns"), 10 );
		add_filter("request", array( $this, "event_column_orderby"), 10 );


		// Include required files
		$this->includes();
	}	


	// Including the widget
	public function includes(){
		include_once ( 'widget-agenda-running.php' );
	}

	public function load_textdomain(){
  		//load_plugin_textdomain( 'agenda-running', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueueing scripts and styles in the admin
	 * @param  int $hook Current page hook
	 */
	public function admin_script_style( $hook ) {
		global $post_type;

		if ( ( 'post.php' == $hook || 'post-new.php' == $hook ) && ( 'agenda-running' == $post_type ) ) {

			wp_enqueue_script( 'agenda-running', plugins_url('/js/upcoming-script.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), false, true );
			wp_enqueue_style('jquery-ui-calendar', plugins_url('css/jquery-ui-1.10.4.custom.min.css', __FILE__));
		}
	}

	// Register Custom Post Type
	public function event_post_type() {

		$labels = array(
			'name'                => _x( 'AgendaRunning', 'Post Type General Name', 'agenda-running' ),
			'singular_name'       => _x( 'AgendaRunning', 'Post Type Singular Name', 'agenda-running' ),
			'menu_name'           => __( 'AgendaRunning', 'agenda-running' ),
			'all_items'           => __( 'Toutes les dates', 'agenda-running' ),
			'view_item'           => __( 'Voir la date', 'agenda-running' ),
			'add_new_item'        => __( 'Ajouter une nouvelle date', 'agenda-running' ),
			'add_new'             => __( 'Ajouter une date', 'agenda-running' ),
			'edit_item'           => __( 'Modifier une date', 'agenda-running' ),
			'update_item'         => __( 'Mettre à jour une date', 'agenda-running' ),
			'search_items'        => __( 'Rechercher', 'agenda-running' ),
			'not_found'           => __( 'Aucun élément trouvé', 'agenda-running' ),
			'not_found_in_trash'  => __( 'Non trouvée dans la corbeille', 'agenda-running' ),
		);
		$args = array(
			'label'               => __( 'AgendaRunning', 'agenda-running' ),
			'description'         => __( 'Liste des prochaines dates', 'agenda-running' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'agenda-running', $args );

	}

	//Adding metabox for event information
	public function event_info_metabox() {
		add_meta_box( 'event-info-metabox', __( 'Informations sur la date', 'agenda-running' ), array( $this, 'render_event_info_metabox'), 'agenda-running','side', 'core' );
	}

	/**
	 * Rendering the metabox for event information
	 * @param  object $post The post object
	 */
	public function render_event_info_metabox( $post ) {
		//generate a nonce field
		global $list_type;
		wp_nonce_field( basename( __FILE__ ), 'event-info-nonce' );

		//get previously saved meta values (if any)
		$event_date = get_post_meta( $post->ID, 'event-date', true );
		$event_lieu = get_post_meta( $post->ID, 'event-lieu', true );
		$event_url = get_post_meta( $post->ID, 'event-url', true );
		$event_chrono = get_post_meta( $post->ID, 'event-chrono', true );
		$event_distance = get_post_meta( $post->ID, 'event-distance', true );
		$event_type = get_post_meta( $post->ID, 'event-type', true );
		$event_place = get_post_meta( $post->ID, 'event-place', true );

		?>
		<p> 
			<label for="event-date"><?php _e( 'Date de début:', 'agenda-running' ); ?></label>
			<input type="text" id="event-date" name="event-date" class="widefat event-date-input" value="<?php if($event_date) echo date( 'd-m-Y', $event_date ); ?>" placeholder="Format: 20-07-2016">
		</p>
		<p>
			<label for="event-lieu"><?php _e( 'Lieu:', 'agenda-running' ); ?></label>
			<input type="text" id="event-lieu" name="event-lieu" class="widefat" value="<?php echo $event_lieu; ?>" placeholder="ex : Angers">
		</p>

		<p>
			<label for="event-type"><?php _e( 'Type de course:', 'agenda-running' ); ?></label>
			<select id='event-type' name='event-type' class="widefat" >
				<option value=''>Non defini</option>
				<?php foreach($list_type as $cle=>$val){ ?>
				<option value='<?php echo $cle; ?>' <?php if($cle==$event_type) echo "SELECTED"; ?>><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="event-distance"><?php _e( 'Distance:', 'agenda-running' ); ?></label>
			<input type="text" id="event-distance" name="event-distance" class="widefat" value="<?php echo $event_distance; ?>" placeholder="ex : 42.195">
		</p>		
		<p>
			<label for="event-url"><?php _e( 'URL:', 'agenda-running' ); ?></label>
			<input type="text" id="event-url" name="event-url" class="widefat" value="<?php echo $event_url; ?>" placeholder="">
		</p>
		<p>
			<label for="event-chrono"><?php _e( 'Chrono:', 'agenda-running' ); ?></label>
			<input type="text" id="event-chrono" name="event-chrono" class="widefat" value="<?php echo $event_chrono; ?>" placeholder="00:00:00">
		</p>
		<p>
			<label for="event-place"><?php _e( 'Classement:', 'agenda-running' ); ?></label>
			<input type="text" id="event-place" name="event-place" class="widefat" value="<?php echo $event_place; ?>" placeholder="ex : 1 ou 1/54846">
		</p>
		<?php
	}

	/**
	 * Saving the event along with its meta values
	 * @param  int $post_id The id of the current post
	 */
	function save_event_info( $post_id ) {
		//checking if the post being saved is an 'event',
		//if not, then return
		if ( isset($_POST['post_type']) && 'agenda-running' != $_POST['post_type'] ) {
			return;
		}

		//checking for the 'save' status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST['event-info-nonce'] ) && ( wp_verify_nonce( $_POST['event-info-nonce'], basename( __FILE__ ) ) ) ) ? true : false;

		//exit depending on the save status or if the nonce is not valid
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		//checking for the values and performing necessary actions
		if ( isset( $_POST['event-date'] )) {
			update_post_meta( $post_id, 'event-date', strtotime( $_POST['event-date'] ) );
		}

		if ( isset( $_POST['event-lieu'] ) ) {
			update_post_meta( $post_id, 'event-lieu', sanitize_text_field( $_POST['event-lieu'] ) );
		}

		if ( isset( $_POST['event-url'] ) ) {
			update_post_meta( $post_id, 'event-url', sanitize_text_field( $_POST['event-url'] ) );
		}

		if ( isset( $_POST['event-distance'] ) ) {
			update_post_meta( $post_id, 'event-distance', sanitize_text_field( $_POST['event-distance'] ) );
		}

		if ( isset( $_POST['event-chrono'] ) ) {
			update_post_meta( $post_id, 'event-chrono', sanitize_text_field( $_POST['event-chrono'] ) );
		}		

		if ( isset( $_POST['event-type'] ) ) {
			update_post_meta( $post_id, 'event-type', sanitize_text_field( $_POST['event-type'] ) );
		}

		if ( isset( $_POST['event-place'] ) ) {
			update_post_meta( $post_id, 'event-place', sanitize_text_field( $_POST['event-place'] ) );
		}


	}

	/**
	 * Custom columns head
	 * @param  array $defaults The default columns in the post admin
	 */
	function custom_columns_head( $defaults ) {
		unset( $defaults['date'] );

		$defaults['event_date'] = __( 'Date', 'agenda-running' );
		$defaults['event_chrono'] = __( 'Chrono', 'agenda-running' );

		return $defaults;
	}

	function agenda_running_sortable_columns( $columns ) {
	    $columns['event_date'] = 'event_date';
	    return $columns;
	}

	function event_column_orderby( $vars ) {
		if($vars['post_type']=="agenda-running"){
		    if ( !isset( $vars['orderby'] ) ||  $vars['orderby']=="event_date") {
		        $vars = array_merge( $vars,
		            array(
		                'meta_key'  => 'event-date',
		                'orderby'   => 'meta_value_num',
		                'order'     => $vars['order']
		            )
		        );
		    }
		}

	    return $vars;
	}



	/**
	 * Custom columns content
	 * @param  string 	$column_name The name of the current column
	 * @param  int 		$post_id     The id of the current post
	 */
	function custom_columns_content( $column_name, $post_id ) {
		global $list_type;


		if ( 'event_date' == $column_name ) {
			$start_date = get_post_meta( $post_id, 'event-date', true );
			if($start_date) 
				echo date( 'd-m-Y', $start_date );
		}

		if ( 'event_lieu' == $column_name ) {
			$venue = get_post_meta( $post_id, 'event-lieu', true );
			echo $venue;
		}

		if ( 'event_url' == $column_name ) {
			$url = get_post_meta( $post_id, 'event-url', true );
			echo $url;
		}

		if ( 'event_distance' == $column_name ) {
			$distance = get_post_meta( $post_id, 'event-distance', true );
			echo $distance;
		}

		if ( 'event_chrono' == $column_name ) {
			$chrono = get_post_meta( $post_id, 'event-chrono', true );
			echo $chrono;
		}

		if ( 'event_type' == $column_name ) {
			$type = get_post_meta( $post_id, 'event-type', true );
			echo $list_type[$type];
		}

		if ( 'event_place' == $column_name ) {
			$place = get_post_meta( $post_id, 'event-place', true );
			echo $place;
		}
	}

	function AgendaRunning_single_content( $content ){
		if ( is_singular('agenda-running') || is_post_type_archive('agenda-running') ) {
			global $list_type;

			$event_lieu = get_post_meta( get_the_ID(), 'event-lieu', true );
			$event_distance = get_post_meta( get_the_ID(), 'event-distance', true );
			$event_chrono = get_post_meta( get_the_ID(), 'event-chrono', true );
			$event_type = get_post_meta( get_the_ID(), 'event-type', true );

			if($event_lieu)
				$event  = $event_lieu." ";
			if($event_type)
				$event .= $list_type[$event_type]." ";
			if($event_distance)
				$event .= "(".$event_distance."km)";
			$content .= $event;
		}
		return $content;
	}
}

$AgendaRunning_lists = new AgendaRunning();

/**
 * Flushing rewrite rules on plugin activation/deactivation
 * for better working of permalink structure
 */
function AgendaRunning_lists_activation_deactivation() {
	$events = new AgendaRunning();
	$events->event_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'AgendaRunning_lists_activation_deactivation' );



function get_infos_html($affichage, $data){
	global $list_type;

	$affichage = str_replace("{TITRE}",$data['title'], $affichage);	
	$affichage = str_replace("{DATE}",date("d/m/Y",$data['date']), $affichage);
	$affichage = str_replace("{JOUR}",date("d",$data['date']), $affichage);
	$affichage = str_replace("{MOIS}",date("m",$data['date']), $affichage);
	$affichage = str_replace("{MOISALPHA}",date("M",$data['date']), $affichage);
	$affichage = str_replace("{ANNEE}",date("Y",$data['date']), $affichage);
	$affichage = str_replace("{LIEU}",$data['lieu'], $affichage);
	$affichage = str_replace("{TYPE}",$list_type[$data['type']], $affichage);
	$affichage = str_replace("{DISTANCE}",$data['distance']."km", $affichage);
	$affichage = str_replace("{CLASSEMENT}",$data['place'], $affichage);

	if($data['type'] == "trail" || $data['type'] == "trailurbain" || $data['type'] == "cross")
		$affichage = str_replace("{TYPEDISTANCE}",$list_type[$data['type']]." ".($data['distance']."km"), $affichage);
	elseif($data['type'] == "autre")
		$affichage = str_replace("{TYPEDISTANCE}",($data['distance']."km"), $affichage);
	else
		$affichage = str_replace("{TYPEDISTANCE}",($list_type[$data['type']]), $affichage);


	$affichage = str_replace("{CHRONO}",$data['chrono'], $affichage);
	$affichage = str_replace("{IMAGE}","<img src='".$data['image']."' />", $affichage);
	$affichage = str_replace("{LINK}","<a href='".$data['url']."'>", $affichage);
	$affichage = str_replace("{/LINK}","</a>", $affichage);

	return $affichage;
}

function show_results( $atts ) {
    $affichage = "<strong>{DATE} : {TITRE}</strong><br />{LIEU} ({DISTANCE}) : {CHRONO}";

	//Preparing the query for events
	$meta_quer_args = array(
		'relation'	=>	'AND',
		array(
			'key'		=>	'event-date',
			'value'		=>	time(),
			'compare'	=>	'<'
		)
	);

	$query_args = array(
		'post_type'				=>	'agenda-running',
		'posts_per_page'		=>	1000,
		'post_status'			=>	'publish',
		'ignore_sticky_posts'	=>	true,
		'meta_key'				=>	'event-date',
		'orderby'				=>	'meta_value_num',
		'order'					=>	'DESC',
		'meta_query'			=>	$meta_quer_args
	);

	$AgendaRunning = new WP_Query( $query_args );
		
		while( $AgendaRunning->have_posts() ): $AgendaRunning->the_post();

			$data['title'] = get_the_title();
			$data['date'] = get_post_meta( get_the_ID(), 'event-date', true );
			$data['lieu'] = get_post_meta( get_the_ID(), 'event-lieu', true );
			$data['type'] = get_post_meta( get_the_ID(), 'event-type', true );
			$data['distance'] = get_post_meta( get_the_ID(), 'event-distance', true );
			$data['url'] = get_post_meta( get_the_ID(), 'event-url', true );
			$data['chrono'] = get_post_meta( get_the_ID(), 'event-chrono', true );				
			$data['image'] = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );

		$html = get_infos_html($affichage, $data);
		echo "<div class='AgendaRunningRESULTATS ".$data['type']."'>".$html."</div>";
		endwhile;
}

function show_agenda( $atts ) {
    $affichage = "<strong>{DATE} : {TITRE}</strong><br />{LIEU} ({DISTANCE})";

	//Preparing the query for events
	$meta_quer_args = array(
		'relation'	=>	'AND',
		array(
			'key'		=>	'event-date',
			'value'		=>	time(),
			'compare'	=>	'>='
		)
	);

	$query_args = array(
		'post_type'				=>	'agenda-running',
		'posts_per_page'		=>	1000,
		'post_status'			=>	'publish',
		'ignore_sticky_posts'	=>	true,
		'meta_key'				=>	'event-date',
		'orderby'				=>	'meta_value_num',
		'order'					=>	'ASC',
		'meta_query'			=>	$meta_quer_args
	);

	$AgendaRunning = new WP_Query( $query_args );
		
	while( $AgendaRunning->have_posts() ): $AgendaRunning->the_post();

		$data['title'] = get_the_title();
		$data['date'] = get_post_meta( get_the_ID(), 'event-date', true );
		$data['lieu'] = get_post_meta( get_the_ID(), 'event-lieu', true );
		$data['type'] = get_post_meta( get_the_ID(), 'event-type', true );
		$data['distance'] = get_post_meta( get_the_ID(), 'event-distance', true );
		$data['url'] = get_post_meta( get_the_ID(), 'event-url', true );
		$data['chrono'] = get_post_meta( get_the_ID(), 'event-chrono', true );				
		$data['image'] = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );

	$html = get_infos_html($affichage, $data);
	echo "<div class='AgendaRunningAGENDA ".$data['type']."'>".$html."</div>";
	endwhile;
}

add_shortcode( 'RESULTATS', 'show_results' );
add_shortcode( 'AGENDA', 'show_agenda' );