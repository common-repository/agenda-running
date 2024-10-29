<?php

/**
 * Class AgendaRunning
 */
class AgendaRunningPAST extends WP_Widget {

	/**
	 * Initializing the widget
	 */
	public function __construct() {
		$widget_ops = array(
			'description'	=>	__( "Ce widget vous permet d'afficher la liste de vos derniers résultats", 'agenda-running' )
		);

		parent::__construct(
			'AgendaRunningResultats',			//base id
			__( 'AgendaRunning - Résultats', 'agenda-running' ),	//title
			$widget_ops
		);
	}


	/**
	 * Displaying the widget on the back-end
	 * @param  array $instance An instance of the widget
	 */
	public function form( $instance ) {
		$widget_defaults = array(
			'title'			=>	'Résultats',
			'number_events'	=>	5
		);

		$title = esc_attr($instance['title']);
	    $affichage = esc_attr($instance['affichage']);
	    $css = esc_attr($instance['css']);

		$instance  = wp_parse_args( (array) $instance, $widget_defaults );
		?>
		
		<!-- Rendering the widget form in the admin -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'agenda-running' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_events' ); ?>"><?php _e( 'Nombre à afficher', 'agenda-running' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'number_events' ); ?>" name="<?php echo $this->get_field_name( 'number_events' ); ?>" class="widefat">
				<?php for ( $i = 1; $i <= 10; $i++ ): ?>
					<option value="<?php echo $i; ?>" <?php selected( $i, $instance['number_events'], true ); ?>><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
	    <label for="<?php echo $this->get_field_name( 'affichage' ); ?>"><?php _e( 'Affichage spécifique:' ); ?></label>
	    <textarea rowspan="5" class="widefat" id="<?php echo $this->get_field_id( 'affichage' ); ?>" name="<?php echo $this->get_field_name( 'affichage' ); ?>"><?php echo $affichage; ?></textarea>
	    </p>

	    <p>
	    <label for="<?php echo $this->get_field_name( 'css' ); ?>"><?php _e( 'CSS spécifique:' ); ?></label>
	    <textarea rowspan="5" class="widefat" id="<?php echo $this->get_field_id( 'css' ); ?>" name="<?php echo $this->get_field_name( 'css' ); ?>"><?php echo $css; ?></textarea>
	    </p>

		<?php
	}


	/**
	 * Making the widget updateable
	 * @param  array $new_instance New instance of the widget
	 * @param  array $old_instance Old instance of the widget
	 * @return array An updated instance of the widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];
		$instance['number_events'] = $new_instance['number_events'];
		$instance['affichage'] = $new_instance['affichage'];		
		$instance['css'] = $new_instance['css'];

		return $instance;
	}


	/**
	 * Displaying the widget on the front-end
	 * @param  array $args     Widget options
	 * @param  array $instance An instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		echo $args['before_widget'];
		echo "<div id='AgendaRunningRESULTATS'>";
	    echo $args['before_title'];
	    echo apply_filters('widget_title', $instance['title']);
	    echo $args['after_title'];

	    $affichage = $instance['affichage'];

	    echo "<style>".$instance['css']."</style>";

	    if(!$affichage)
	    	$affichage = "<strong>{TITRE}</strong> ({DISTANCE})<br />{LIEU} - {DATE}<br />{CHRONO}";

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
			'posts_per_page'		=>	isset($instance['number_events']) ? $instance['number_events'] : 5,
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
			
		wp_reset_query();


		echo "</div>";

		echo $after_widget;

	}
}

class AgendaRunningNEXT extends WP_Widget {

	/**
	 * Initializing the widget
	 */
	public function __construct() {
		$widget_ops = array(
			'description'	=>	__( "Ce widget vous permet d'afficher la liste de vos prochaines courses", 'agenda-running' )
		);

		parent::__construct(
			'AgendaRunningAgenda',			//base id
			__( 'AgendaRunning - Agenda', 'agenda-running' ),	//title
			$widget_ops
		);
	}


	/**
	 * Displaying the widget on the back-end
	 * @param  array $instance An instance of the widget
	 */
	public function form( $instance ) {
		$widget_defaults = array(
			'title'			=>	'Agenda',
			'number_events'	=>	5
		);

		$title = esc_attr($instance['title']);
	    $affichage = esc_attr($instance['affichage']);
	    $css = esc_attr($instance['css']);

		$instance  = wp_parse_args( (array) $instance, $widget_defaults );
		?>
		
		<!-- Rendering the widget form in the admin -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'agenda-running' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_events' ); ?>"><?php _e( 'Nombre à afficher', 'agenda-running' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'number_events' ); ?>" name="<?php echo $this->get_field_name( 'number_events' ); ?>" class="widefat">
				<?php for ( $i = 1; $i <= 10; $i++ ): ?>
					<option value="<?php echo $i; ?>" <?php selected( $i, $instance['number_events'], true ); ?>><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
	    <label for="<?php echo $this->get_field_name( 'affichage' ); ?>"><?php _e( 'Affichage spécifique:' ); ?></label>
	    <textarea rowspan="5" class="widefat" id="<?php echo $this->get_field_id( 'affichage' ); ?>" name="<?php echo $this->get_field_name( 'affichage' ); ?>"><?php echo $affichage; ?></textarea>
	    </p>

	    <p>
	    <label for="<?php echo $this->get_field_name( 'css' ); ?>"><?php _e( 'CSS spécifique:' ); ?></label>
	    <textarea rowspan="5" class="widefat" id="<?php echo $this->get_field_id( 'css' ); ?>" name="<?php echo $this->get_field_name( 'css' ); ?>"><?php echo $css; ?></textarea>
	    </p>

		<?php
	}


	/**
	 * Making the widget updateable
	 * @param  array $new_instance New instance of the widget
	 * @param  array $old_instance Old instance of the widget
	 * @return array An updated instance of the widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];
		$instance['number_events'] = $new_instance['number_events'];
		$instance['affichage'] = $new_instance['affichage'];
		$instance['css'] = $new_instance['css'];

		return $instance;
	}


	/**
	 * Displaying the widget on the front-end
	 * @param  array $args     Widget options
	 * @param  array $instance An instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		echo $args['before_widget'];
		echo "<div id='AgendaRunningAGENDA'>";
	    echo $args['before_title'];
	    echo apply_filters('widget_title', $instance['title']);
	    echo $args['after_title'];

	    $affichage = $instance['affichage'];

	    echo "<style>".$instance['css']."</style>";

	    if(!$affichage)
	    	$affichage = "<strong>{TITRE}</strong> {DATE}<br />{DISTANCE} - {LIEU}";

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
			'posts_per_page'		=>	isset($instance['number_events']) ? $instance['number_events'] : 5,
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
			
		wp_reset_query();

		echo "</div>";

		echo $after_widget;

	}
}

class AgendaRunningRP extends WP_Widget {

	/**
	 * Initializing the widget
	 */
	public function __construct() {
		$widget_ops = array(
			'description'	=>	__( "Ce widget vous permet d'afficher la liste de vos records personnel", 'agenda-running' )
		);

		parent::__construct(
			'AgendaRunningRP',			//base id
			__( 'AgendaRunning - Records Personnel', 'agenda-running' ),	//title
			$widget_ops
		);
	}


	/**
	 * Displaying the widget on the back-end
	 * @param  array $instance An instance of the widget
	 */
	public function form( $instance ) {
		$widget_defaults = array(
			'title'			=>	'Records Personnel'
		);

		$title = esc_attr($instance['title']);
	    $affichage = esc_attr($instance['affichage']);
	    $css = esc_attr($instance['css']);

		$instance  = wp_parse_args( (array) $instance, $widget_defaults );
		?>
		
		<!-- Rendering the widget form in the admin -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'agenda-running' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>

		<p>
	    <label for="<?php echo $this->get_field_name( 'affichage' ); ?>"><?php _e( 'Affichage spécifique:' ); ?></label>
	    <textarea rowspan="5" class="widefat" id="<?php echo $this->get_field_id( 'affichage' ); ?>" name="<?php echo $this->get_field_name( 'affichage' ); ?>"><?php echo $affichage; ?></textarea>
	    </p>	    

	    <p>
	    <label for="<?php echo $this->get_field_name( 'css' ); ?>"><?php _e( 'CSS spécifique:' ); ?></label>
	    <textarea rowspan="5" class="widefat" id="<?php echo $this->get_field_id( 'css' ); ?>" name="<?php echo $this->get_field_name( 'css' ); ?>"><?php echo $css; ?></textarea>
	    </p>

		<?php
	}


	/**
	 * Making the widget updateable
	 * @param  array $new_instance New instance of the widget
	 * @param  array $old_instance Old instance of the widget
	 * @return array An updated instance of the widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];
		$instance['affichage'] = $new_instance['affichage'];
		$instance['css'] = $new_instance['css'];

		return $instance;
	}


	/**
	 * Displaying the widget on the front-end
	 * @param  array $args     Widget options
	 * @param  array $instance An instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		echo $args['before_widget'];
		echo "<div id='AgendaRunningRP'>";
	    echo $args['before_title'];
	    echo apply_filters('widget_title', $instance['title']);
	    echo $args['after_title'];

	    $affichage = $instance['affichage'];

	    echo "<style>".$instance['css']."</style>";

	    if(!$affichage)
	    	$affichage = "{TYPE} : <strong>{CHRONO}</strong>";

		$rps = array("5k","10k","15k","semimarathon","marathon","100k");
		//Preparing the query for events
		foreach($rps as $rp){
			$meta_quer_args = array(
			'relation'	=>	'AND',
			array(
				'key'		=>	'event-type',
				'value'		=>	$rp,
				'compare'	=>	'='
			),
			array(
				'key'		=>	'event-chrono',
				'value'		=>	'',
				'compare'	=>	'!='
			)
		);

		$query_args = array(
			'post_type'				=>	'agenda-running',
			'posts_per_page'		=>	1,
			'post_status'			=>	'publish',
			'ignore_sticky_posts'	=>	false,
			'meta_key'				=>	'event-chrono',
			'orderby'				=>	'meta_value',
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
			echo "<div class='AgendaRunningRP ".$data['type']."'>".$html."</div>";
			endwhile; 

		wp_reset_query();
		}

		echo "</div>";

		echo $after_widget;

	}
}

function AgendaRunning_lists_widget() {
	register_widget( 'AgendaRunningPAST' );
	register_widget( 'AgendaRunningNEXT' );
	register_widget( 'AgendaRunningRP' );
	
}
add_action( 'widgets_init', 'AgendaRunning_lists_widget' );