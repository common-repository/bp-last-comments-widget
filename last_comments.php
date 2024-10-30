<?php
/*
Plugin Name: BuddyPress Last Comments Widget
Author: Inna Z
Description: Shows a list of most recently added BP activity comments.
Text Domain: bp-last-comments-widget
Domain Path: /languages
Version: 2.0
Created on: 26.09.2017
Last modified: 15.10.2017
*/

/* Start Adding Functions Below this Line */

// Register and load the widget
function bp_last_comments_load_widget() {
    register_widget( 'bp_last_comments_widget_plugin' );
}
add_action( 'widgets_init', 'bp_last_comments_load_widget' );

$plugin_dir = basename( dirname( __FILE__ ) );
load_plugin_textdomain( 'bp-last-comments-widget', null, $plugin_dir.'/languages/' );

// Get SQL records from DB
function bp_get_recent_comments($number_blogs)
{
	global $wpdb;

	$recent_blogs = $wpdb->get_results( "SELECT user_id, content, item_id, id, date_recorded FROM {$wpdb->prefix}bp_activity WHERE TYPE = 'activity_comment' ORDER BY date_recorded DESC LIMIT $number_blogs" );
	return $recent_blogs;
 
 }
 
 // Draw html line for each comment
 function bp_show_recent_comments($number_blogs, $show_date)
 {
	$recent_blogs = bp_get_recent_comments($number_blogs);
					
	foreach($recent_blogs as $recent_blog):
		$domain = get_option('siteurl');
		$blog_url = $domain."/activity/p/".$recent_blog->item_id ."/#acomment-".$recent_blog->id;
		$blog_content = $recent_blog->content;
		$the_date = mysql2date( get_option( 'date_format' ), $recent_blog->date_recorded);
		mb_internal_encoding("UTF-8");
?>
			<li>
				<a href="<?php echo $blog_url;?>"><?php echo stripslashes(mb_substr(esc_attr($blog_content), 0, 60)); ?></a>
				<?php if ( $show_date ) : ?>
					<span class="post-date"><?php echo $the_date; ?></span>
				<?php endif; ?>
			</li>
<?php 
	endforeach;
 } 
 
// Creating the widget 
class bp_last_comments_widget_plugin extends WP_Widget { 
	
	// Constructor
	function __construct() {
		parent::__construct(
		 
		// Base ID of your widget
		'bp_last_comments_widget_plugin', 
		 
		// Widget name will appear in UI
		__('(BuddyPress) Last Comments Widget', 'bp_last_comments_plugin_domain'), 
		 
		// Widget description
		array( 'description' => __( 'BP widget based on last activity comments', 'bp_last_comments_plugin_domain' ), ) 
		);
	}
	 
	// Creating Frontend widget	 
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		 
		// This is where you run the code and display the output
		echo "<ul>";
		bp_show_recent_comments($instance['count'], $show_date);
		echo "</ul>";
		
		echo $args['after_widget'];
	}
			 
	// Widget Backend (admin) widget
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'bp_last_comments_plugin_domain' );
		}
		$count = absint( $instance['count'] );
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
	?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Last Comments Widget Title:', 'bp-last-comments-widget' ); ?></label> 
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>"  value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of comments:', 'bp-last-comments-widget' ); ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo esc_attr( absint( $count ) ); ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display date?', 'bp-last-comments-widget' ); ?></label>
		</p>
	<?php 
	}
		 
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = absint($new_instance['count'] ) ;
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;

		return $instance;
	}
} // Class bp_last_comments_plugin ends here
 
/* Stop Adding Functions Below this Line */
?>