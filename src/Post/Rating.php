<?php

namespace Svbk\WP\Helpers\Post;


/**
 * Sensei Lesson Rating Main Class.
 *
 * @package  Rating
 * @category Extension
 * @author   Brando Meniconi
 */

class Rating  {
	
	public $rating_min = 1;
	public $rating_max = 5;
	public $type = 'rating';
	public $post_type;
	
	public function __construct( $post_type = '' ){
		
		if( $post_type ) {
			$this->post_type = $post_type;
		}
		
		add_action( 'init', array( $this, 'init'), 100 );
		add_filter( 'preprocess_comment', array( $this, 'maybe_convert_to_rating') );
		add_filter( 'pre_wp_update_comment_count_now', array( $this, 'comment_count_exclude_ratings'), 10, 3 ); 

		if( is_admin() ){
			add_filter( 'get_comment_text', array( $this, 'admin_render_rating' ), 10 ,2 );
		}
		
	}
	
	public function init() { }
	
	public function render_form( $post_id ) { 
		
		$rate_min = apply_filters( 'post_rating_min', $this->rating_min, $post_id, $this );
		$rate_max = apply_filters( 'post_rating_max', $this->rating_max, $post_id, $this );
		
		do_action('post_rating_before_form', $post_id, $this); 
		
		$rating = $this->by_user( get_current_user_id(), $post_id );
		
		$current = $rating ?: $rate_max; 
		?>
		<form id="rating" method="POST" class="rating <?php echo esc_attr( $this->post_type ); ?>-rating type-<?php echo esc_attr( $this->type ); ?>" action="<?php echo add_query_arg( 'type', $this->type, site_url( '/wp-comments-post.php' ) ) ; ?>" >
			<?php wp_nonce_field() ?>
			<?php for( $rate = $rate_min; $rate <= $rate_max; $rate++ ) : 
				$rating_ID = 'rating-' .  $this->post_type . '-' . $this->type . '-' .  $rate;
				?>
			    <input type="radio" name="comment" value="<?php echo esc_attr($rate); ?>"  <?php checked( $current, $rate ); ?> id="<?php echo esc_attr($rating_ID); ?>" />
			  	<label class="rate rate-<?php echo esc_attr($rate); ?>" for="<?php echo esc_attr($rating_ID); ?>"><span><?php echo apply_filters( 'post_rating_label', sprintf( __('%d Stars', 'svbk-helper'), $rate ), $rate, $post_id, $this ); ?></span></label>
			<?php endfor; ?>
			<?php echo get_comment_id_fields($post_id); ?>
			<button type="submit" class="submit button" ><?php echo $rating ? _x('Save', 'save the rating', 'svbk-helpers' ) : _x('Rate', 'submit the rating', 'svbk-helpers' ); ?></button>
		</form>
		<?php do_action( 'post_rating_after_form', $post_id, $this );
	}
	
	public function maybe_convert_to_rating( $commentdata ){
		
		if( filter_input( INPUT_GET, 'type' ) === $this->type ) {
			$commentdata['comment_type'] = $this->type;
		}
		
		return $commentdata;
	}
	
	/**
	 * Adds a new rating to the database.
	 *
	 * Filters new comment to ensure that the fields are sanitized and valid before
	 * inserting comment into database. Calls {@see 'comment_post'} action with comment ID
	 * and whether comment is approved by WordPress. Also has {@see 'preprocess_comment'}
	 * filter for processing the comment data before the function handles it.
	 *
	 * We use `REMOTE_ADDR` here directly. If you are behind a proxy, you should ensure
	 * that it is properly set, such as in wp-config.php, for your environment.
	 *
	 * See {@link https://core.trac.wordpress.org/ticket/9235}
	 *
	 * @since 1.5.0
	 * @since 4.3.0 'comment_agent' and 'comment_author_IP' can be set via `$commentdata`.
	 * @since 4.7.0 The `$avoid_die` parameter was added, allowing the function to
	 *              return a WP_Error object instead of dying.
	 *
	 * @see wp_insert_comment()
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $commentdata {
	 *     Comment data.
	 *
	 *     @type string $comment_author       The name of the comment author.
	 *     @type string $comment_author_email The comment author email address.
	 *     @type string $comment_author_url   The comment author URL.
	 *     @type string $comment_content      The content of the comment.
	 *     @type string $comment_date         The date the comment was submitted. Default is the current time.
	 *     @type string $comment_date_gmt     The date the comment was submitted in the GMT timezone.
	 *                                        Default is `$comment_date` in the GMT timezone.
	 *     @type int    $comment_parent       The ID of this comment's parent, if any. Default 0.
	 *     @type int    $comment_post_ID      The ID of the post that relates to the comment.
	 *     @type int    $user_id              The ID of the user who submitted the comment. Default 0.
	 *     @type int    $user_ID              Kept for backward-compatibility. Use `$user_id` instead.
	 *     @type string $comment_agent        Comment author user agent. Default is the value of 'HTTP_USER_AGENT'
	 *                                        in the `$_SERVER` superglobal sent in the original request.
	 *     @type string $comment_author_IP    Comment author IP address in IPv4 format. Default is the value of
	 *                                        'REMOTE_ADDR' in the `$_SERVER` superglobal sent in the original request.
	 * }
	 * @param bool $avoid_die Should errors be returned as WP_Error objects instead of
	 *                        executing wp_die()? Default false.
	 * @return int|false|WP_Error The ID of the comment on success, false or WP_Error on failure.
	 */	
	public function insert( $post_id, $rating, $user_id ){

		$commentdata['comment_type'] = $this->type;

		return wp_new_comment( $commentdata, $avoid_die = false );	
	}

	public function get_all( $args = array()  ){
		
		$defaults = array(
			'type' => $this->type,
			'post_type' => $this->post_type,
			'status' => 'approve',
			'orderby' => 'comment_date_gmt',
			'order' => 'ASC',			
		);
		
		$comments = get_comments( wp_parse_args( $args, $defaults ) );
		
		return wp_list_pluck( $comments, 'comment_content', 'comment_author_email');
	}

	/**
	 * Retrieve a list of ratings.
	 *
	 * The comment list can be for the blog as a whole or for an individual post.
	 *
	 * @since 2.7.0
	 *
	 * @param string|array $args Optional. Array or string of arguments. See WP_Comment_Query::__construct()
	 *                           for information on accepted arguments. Default empty.
	 * @return int|array List of comments or number of found comments if `$count` argument is true.
	 */	
	public function by_user( $user_id, $post_id = null, $args = array() ){

		$defaults = array(
			'number' => 1,
			'user_id' => $user_id,
			'post_id' => $post_id,
			'orderby' => 'comment_date_gmt',
			'order' => 'DESC',			
		);
		
		$ratings = $this->get_all( wp_parse_args( $args, $defaults ) );
	
		if( !empty( $ratings ) ) {
			return intval( array_pop($ratings) );
		}
		
		return false;
	}

	public function average( $ratings ){
		$ratings_count = count( $ratings );
		
		if( $ratings_count > 0 ) {
			$ratings = array_map('intval', $ratings);
			$average = ceil( array_sum( $ratings ) / count( $ratings ) );
			return $average;
		} else  {
			return 0;
		}
	}
	
	public function render( $post_id, $args = array() ) {

		$defaults = array(
			'post_id' => $post_id
		);
		
		$ratings = $this->get_all( wp_parse_args( $args, $defaults ) );
		
		$ratings_count	= apply_filters( 'post_ratings_render_count', count( $ratings ), $post_id, $ratings, $this );		
		$avg_rating		= apply_filters( 'post_ratings_render_average', $this->average( $ratings ), $post_id, $ratings, $this );
	
		echo $this->render_rating( $avg_rating, $ratings_count );
	}
	
	public function admin_render_rating( $rating, $comment ) {
		
		if( ($comment->comment_type !== $this->type) ) {
			return $rating;
		}
		
		return $this->render_rating( intval($rating) );
	}
	
	public function render_rating( $rating, $count = null ) { 
		
		$output =  '<div id="rating" class="rating ' . esc_attr( $this->post_type ) .'-rating type-'. esc_attr( $this->type ) .' rating-'. esc_attr( $rating ). '" >' ;
		$output .= '<span class="rating-value">' . sprintf( __('Rating: %d Stars', 'svbk-helper'), $rating ) . '</span>';
		
		if( null !== $count ) {
			$output .= '&nbsp;<span class="rating-count">(' . $count . ')</span>';
		}
		
		$output .= '</div>';
		
		return $output;
	}	

	public function comment_count_exclude_ratings($new, $old, $post_id){
		global $wpdb;
		
		if( $this->post_type == get_post_type( $post_id ) ) {
			$new = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type != '$this->type'", $post_id ) );
		}

		return $new;
	}

}