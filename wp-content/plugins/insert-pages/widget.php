<?php
/**
 * Insert Pages widget.
 *
 * @package insert-pages
 */

/**
 * Class: InsertPagesWidget extends WP_Widget
 *
 * Provides a widget for inserting a page into a widget area.
 *
 * @package insert-pages
 */
class InsertPagesWidget extends WP_Widget {

	/**
	 * Set up the widget.
	 */
	public function __construct() {
		// Load admin javascript for Widget options on admin page (widgets.php).
		add_action( 'sidebar_admin_page', array( $this, 'widget_admin_js' ) );

		// Load admin javascript for Widget options on theme customize page (customize.php).
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'widget_admin_js' ) );

		// Call parent constructor to initialize the widget.
		parent::__construct( 'ipw', __( 'Insert Page', 'insert-pages' ), array( 'description' => __( 'Insert a page into a widget area.', 'insert-pages' ) ) );
	}

	/**
	 * Load javascript for interacting with the Insert Page widget.
	 */
	public function widget_admin_js() {
		wp_enqueue_script( 'insertpages_widget', plugins_url( '/js/widget.js', __FILE__ ), array( 'jquery' ), '20160429' );
	}

	/**
	 * Output the content of the widget.
	 *
	 * @param array $args Widget args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		global $insert_pages_plugin;

		// Print widget wrapper.
		echo $args['before_widget'];

		// Build the shortcode attributes array from the widget args.
		$atts = array();
		if ( array_key_exists( 'page', $instance ) ) {
			$atts['page'] = $instance['page'];
		}
		if ( array_key_exists( 'display', $instance ) ) {
			$atts['display'] = $instance['display'];
		}
		if ( array_key_exists( 'template', $instance ) && 'template' === $instance['display'] ) {
			$atts['display'] = $instance['template'];
		}
		if ( array_key_exists( 'class', $instance ) ) {
			$atts['class'] = $instance['class'];
		}
		if ( array_key_exists( 'inline', $instance ) ) {
			$atts['inline'] = '1' === $instance['inline'];
		}
		if ( array_key_exists( 'querystring', $instance ) ) {
			$atts['querystring'] = $instance['querystring'];
		}
		if ( array_key_exists( 'public', $instance ) ) {
			$atts['public'] = '1' === $instance['public'];
		}

		// Render the inserted page using the plugin's shortcode handler.
		$content = $insert_pages_plugin->insert_pages_handle_shortcode_insert( $atts );

		// Print inserted page.
		echo $content;

		// Print widget wrapper.
		echo $args['after_widget'];
	}

	/**
	 * Output the options form on admin.
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'page' => '',
			'display' => 'link',
			'template' => '',
			'class' => '',
			'inline' => '',
			'querystring' => '',
			'public' => '',
		)); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'page' ) ); ?>"><?php esc_html_e( 'Page/Post ID or Slug', 'insert-pages' ); ?>:</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'page' ) ); ?>" value="<?php echo esc_attr( $instance['page'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>"><?php esc_html_e( 'Display', 'insert-pages' ); ?>:</label><br />
			<select class="insertpage-format-select" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>">
				<option value='title' <?php selected( $instance['display'], 'title' ); ?>><?php esc_html_e( 'Title', 'insert-pages' ); ?></option>
				<option value='link' <?php selected( $instance['display'], 'link' ); ?>><?php esc_html_e( 'Link', 'insert-pages' ); ?></option>
				<option value='excerpt' <?php selected( $instance['display'], 'excerpt' ); ?>><?php esc_html_e( 'Excerpt', 'insert-pages' ); ?></option>
				<option value='excerpt-only' <?php selected( $instance['display'], 'excerpt-only' ); ?>><?php esc_html_e( 'Excerpt only (no title)', 'insert-pages' ); ?></option>
				<option value='content' <?php selected( $instance['display'], 'content' ); ?>><?php esc_html_e( 'Content', 'insert-pages' ); ?></option>
				<option value='post-thumbnail' <?php selected( $instance['display'], 'post-thumbnail' ); ?>><?php esc_html_e( 'Post Thumbnail', 'insert-pages' ); ?></option>
				<option value='all' <?php selected( $instance['display'], 'all' ); ?>><?php esc_html_e( 'All (includes custom fields)', 'insert-pages' ); ?></option>
				<option value='template' <?php selected( $instance['display'], 'template' ); ?>><?php esc_html_e( 'Use a custom template', 'insert-pages' ); ?> &raquo;</option>
			</select>
			<select class="insertpage-template-select" name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>" disabled="disabled">
				<option value='all'><?php esc_html_e( 'Default Template', 'insert-pages' ); ?></option>
				<?php if ( function_exists( 'page_template_dropdown' ) ) :
					page_template_dropdown( $instance['template'] );
				endif; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>"><?php esc_html_e( 'Extra Classes', 'insert-pages' ); ?>:</label>
			<input type="text" class="widefat" autocomplete="off" name="<?php echo esc_attr( $this->get_field_name( 'class' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>" value="<?php echo esc_attr( $instance['class'] ); ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'inline' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'inline' ) ); ?>" value="1" <?php checked( $instance['inline'], '1' ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'inline' ) ); ?>"><?php esc_html_e( 'Inline?', 'insert-pages' ); ?></label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'querystring' ) ); ?>"><?php esc_html_e( 'Querystring', 'insert-pages' ); ?>:</label>
			<input type="text" class="widefat" autocomplete="off" name="<?php echo esc_attr( $this->get_field_name( 'querystring' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'querystring' ) ); ?>" value="<?php echo esc_attr( $instance['querystring'] ); ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'public' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'public' ) ); ?>" value="1" <?php checked( $instance['public'], '1' ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'public' ) ); ?>"><?php esc_html_e( 'Anonymous users can see this inserted even if its status is private', 'insert-pages' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Process widget options on save.
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 */
	public function update( $new_instance, $old_instance ) {
		// Sanitize form options.
		$instance = $old_instance;
		$instance['page'] = array_key_exists( 'page', $new_instance ) ? strip_tags( $new_instance['page'] ) : '';
		$instance['display'] = array_key_exists( 'display', $new_instance ) ? strip_tags( $new_instance['display'] ) : '';
		$instance['template'] = array_key_exists( 'template', $new_instance ) ? strip_tags( $new_instance['template'] ) : '';
		$instance['class'] = array_key_exists( 'class', $new_instance ) ? strip_tags( $new_instance['class'] ) : '';
		$instance['inline'] = array_key_exists( 'inline', $new_instance ) ? strip_tags( $new_instance['inline'] ) : '';
		$instance['querystring'] = array_key_exists( 'querystring', $new_instance ) ? strip_tags( $new_instance['querystring'] ) : '';
		$instance['public'] = array_key_exists( 'public', $new_instance ) ? strip_tags( $new_instance['public'] ) : '';

		return $instance;
	}
}
