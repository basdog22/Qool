<?php
global $wp_styles,$wpdb,$wp_query;
$wp_styles = new WP_Styles();
$wpdb = new wpdb();
$wp_query = new WP_Query();
class WP_Query {
	var $query;
	var $query_vars = array();
	var $tax_query;
	var $meta_query = false;
	var $queried_object;
	var $queried_object_id;
	var $request;
	var $posts;
	var $post_count = 0;
	var $current_post = -1;
	var $in_the_loop = false;
	var $post;
	var $comments;
	var $comment_count = 0;
	var $current_comment = -1;
	var $comment;
	var $found_posts = 0;
	var $max_num_pages = 0;
	var $max_num_comment_pages = 0;
	var $is_single = false;
	var $is_preview = false;
	var $is_page = false;
	var $is_archive = false;
	var $is_date = false;
	var $is_year = false;
	var $is_month = false;
	var $is_day = false;
	var $is_time = false;
	var $is_author = false;
	var $is_category = false;
	var $is_tag = false;
	var $is_tax = false;
	var $is_search = false;
	var $is_feed = false;
	var $is_comment_feed = false;
	var $is_trackback = false;
	var $is_home = false;
	var $is_404 = false;
	var $is_comments_popup = false;
	var $is_paged = false;
	var $is_admin = false;
	var $is_attachment = false;
	var $is_singular = false;
	var $is_robots = false;
	var $is_posts_page = false;
	var $is_post_type_archive = false;
	var $query_vars_hash = false;
	var $query_vars_changed = true;
	var $thumbnails_cached = false;
	/**
	 * Resets query flags to false.
	 *
	 * The query flags are what page info WordPress was able to figure out.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	function init_query_flags() {
		$this->is_single = false;
		$this->is_preview = false;
		$this->is_page = false;
		$this->is_archive = false;
		$this->is_date = false;
		$this->is_year = false;
		$this->is_month = false;
		$this->is_day = false;
		$this->is_time = false;
		$this->is_author = false;
		$this->is_category = false;
		$this->is_tag = false;
		$this->is_tax = false;
		$this->is_search = false;
		$this->is_feed = false;
		$this->is_comment_feed = false;
		$this->is_trackback = false;
		$this->is_home = false;
		$this->is_404 = false;
		$this->is_comments_popup = false;
		$this->is_paged = false;
		$this->is_admin = false;
		$this->is_attachment = false;
		$this->is_singular = false;
		$this->is_robots = false;
		$this->is_posts_page = false;
		$this->is_post_type_archive = false;
		$this->qool = $qool = &get_array('qool');
		$this->qooldb = $qool->db;
	}

	/**
	 * Initiates object properties and sets default values.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	function init() {
		unset($this->posts);
		unset($this->query);
		$this->query_vars = array();
		unset($this->queried_object);
		unset($this->queried_object_id);
		$this->post_count = 0;
		$this->current_post = -1;
		$this->in_the_loop = false;
		unset( $this->request );
		unset( $this->post );
		unset( $this->comments );
		unset( $this->comment );
		$this->comment_count = 0;
		$this->current_comment = -1;
		$this->found_posts = 0;
		$this->max_num_pages = 0;
		$this->max_num_comment_pages = 0;

		$this->init_query_flags();
	}

	/**
	 * Reparse the query vars.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	function parse_query_vars() {
		$this->parse_query();
	}

	/**
	 * Fills in the query variables, which do not exist within the parameter.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param array $array Defined query variables.
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
	function fill_query_vars($array) {
		$keys = array(
		'error'
		, 'm'
		, 'p'
		, 'post_parent'
		, 'subpost'
		, 'subpost_id'
		, 'attachment'
		, 'attachment_id'
		, 'name'
		, 'static'
		, 'pagename'
		, 'page_id'
		, 'second'
		, 'minute'
		, 'hour'
		, 'day'
		, 'monthnum'
		, 'year'
		, 'w'
		, 'category_name'
		, 'tag'
		, 'cat'
		, 'tag_id'
		, 'author_name'
		, 'feed'
		, 'tb'
		, 'paged'
		, 'comments_popup'
		, 'meta_key'
		, 'meta_value'
		, 'preview'
		, 's'
		, 'sentence'
		, 'fields'
		, 'menu_order'
		);

		foreach ( $keys as $key ) {
			if ( !isset($array[$key]) )
			$array[$key] = '';
		}

		$array_keys = array('category__in', 'category__not_in', 'category__and', 'post__in', 'post__not_in',
		'tag__in', 'tag__not_in', 'tag__and', 'tag_slug__in', 'tag_slug__and');

		foreach ( $array_keys as $key ) {
			if ( !isset($array[$key]) )
			$array[$key] = array();
		}
		return $array;
	}

	/**
	 * Parse a query string and set query type booleans.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string|array $query Optional query.
	 */
	function parse_query( $query =  '' ) {
		if ( ! empty( $query ) ) {
			$this->init();
			$this->query = $this->query_vars = wp_parse_args( $query );
		} elseif ( ! isset( $this->query ) ) {
			$this->query = $this->query_vars;
		}

		$this->query_vars = $this->fill_query_vars($this->query_vars);
		$qv = &$this->query_vars;

		$this->query_vars_changed = true;

		if ( ! empty($qv['robots']) )
		$this->is_robots = true;

		$qv['p'] =  absint($qv['p']);
		$qv['page_id'] =  absint($qv['page_id']);
		$qv['year'] = absint($qv['year']);
		$qv['monthnum'] = absint($qv['monthnum']);
		$qv['day'] = absint($qv['day']);
		$qv['w'] = absint($qv['w']);
		$qv['m'] = absint($qv['m']);
		$qv['paged'] = absint($qv['paged']);
		$qv['cat'] = preg_replace( '|[^0-9,-]|', '', $qv['cat'] ); // comma separated list of positive or negative integers
		$qv['pagename'] = trim( $qv['pagename'] );
		$qv['name'] = trim( $qv['name'] );
		if ( '' !== $qv['hour'] ) $qv['hour'] = absint($qv['hour']);
		if ( '' !== $qv['minute'] ) $qv['minute'] = absint($qv['minute']);
		if ( '' !== $qv['second'] ) $qv['second'] = absint($qv['second']);
		if ( '' !== $qv['menu_order'] ) $qv['menu_order'] = absint($qv['menu_order']);

		// Compat. Map subpost to attachment.
		if ( '' != $qv['subpost'] )
		$qv['attachment'] = $qv['subpost'];
		if ( '' != $qv['subpost_id'] )
		$qv['attachment_id'] = $qv['subpost_id'];

		$qv['attachment_id'] = absint($qv['attachment_id']);

		if ( ('' != $qv['attachment']) || !empty($qv['attachment_id']) ) {
			$this->is_single = true;
			$this->is_attachment = true;
		} elseif ( '' != $qv['name'] ) {
			$this->is_single = true;
		} elseif ( $qv['p'] ) {
			$this->is_single = true;
		} elseif ( ('' !== $qv['hour']) && ('' !== $qv['minute']) &&('' !== $qv['second']) && ('' != $qv['year']) && ('' != $qv['monthnum']) && ('' != $qv['day']) ) {
			// If year, month, day, hour, minute, and second are set, a single
			// post is being queried.
			$this->is_single = true;
		} elseif ( '' != $qv['static'] || '' != $qv['pagename'] || !empty($qv['page_id']) ) {
			$this->is_page = true;
			$this->is_single = false;
		} else {
			// Look for archive queries. Dates, categories, authors, search, post type archives.

			if ( !empty($qv['s']) ) {
				$this->is_search = true;
			}

			if ( '' !== $qv['second'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( '' !== $qv['minute'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( '' !== $qv['hour'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( $qv['day'] ) {
				if ( ! $this->is_date ) {
					$this->is_day = true;
					$this->is_date = true;
				}
			}

			if ( $qv['monthnum'] ) {
				if ( ! $this->is_date ) {
					$this->is_month = true;
					$this->is_date = true;
				}
			}

			if ( $qv['year'] ) {
				if ( ! $this->is_date ) {
					$this->is_year = true;
					$this->is_date = true;
				}
			}

			if ( $qv['m'] ) {
				$this->is_date = true;
				if ( strlen($qv['m']) > 9 ) {
					$this->is_time = true;
				} else if ( strlen($qv['m']) > 7 ) {
					$this->is_day = true;
				} else if ( strlen($qv['m']) > 5 ) {
					$this->is_month = true;
				} else {
					$this->is_year = true;
				}
			}

			if ( '' != $qv['w'] ) {
				$this->is_date = true;
			}

			$this->query_vars_hash = false;
			$this->parse_tax_query( $qv );

			foreach ( $this->tax_query->queries as $tax_query ) {
				if ( 'NOT IN' != $tax_query['operator'] ) {
					switch ( $tax_query['taxonomy'] ) {
						case 'category':
							$this->is_category = true;
							break;
						case 'post_tag':
							$this->is_tag = true;
							break;
						default:
							$this->is_tax = true;
					}
				}
			}
			unset( $tax_query );

			if ( empty($qv['author']) || ($qv['author'] == '0') ) {
				$this->is_author = false;
			} else {
				$this->is_author = true;
			}

			if ( '' != $qv['author_name'] )
			$this->is_author = true;

			if ( !empty( $qv['post_type'] ) && ! is_array( $qv['post_type'] ) ) {
				$post_type_obj = get_post_type_object( $qv['post_type'] );
				if ( ! empty( $post_type_obj->has_archive ) )
				$this->is_post_type_archive = true;
			}

			if ( $this->is_post_type_archive || $this->is_date || $this->is_author || $this->is_category || $this->is_tag || $this->is_tax )
			$this->is_archive = true;
		}

		if ( '' != $qv['feed'] )
		$this->is_feed = true;

		if ( '' != $qv['tb'] )
		$this->is_trackback = true;

		if ( '' != $qv['paged'] && ( intval($qv['paged']) > 1 ) )
		$this->is_paged = true;

		if ( '' != $qv['comments_popup'] )
		$this->is_comments_popup = true;

		// if we're previewing inside the write screen
		if ( '' != $qv['preview'] )
		$this->is_preview = true;

		if ( is_admin() )
		$this->is_admin = true;

		if ( false !== strpos($qv['feed'], 'comments-') ) {
			$qv['feed'] = str_replace('comments-', '', $qv['feed']);
			$qv['withcomments'] = 1;
		}

		$this->is_singular = $this->is_single || $this->is_page || $this->is_attachment;

		if ( $this->is_feed && ( !empty($qv['withcomments']) || ( empty($qv['withoutcomments']) && $this->is_singular ) ) )
		$this->is_comment_feed = true;

		if ( !( $this->is_singular || $this->is_archive || $this->is_search || $this->is_feed || $this->is_trackback || $this->is_404 || $this->is_admin || $this->is_comments_popup || $this->is_robots ) )
		$this->is_home = true;

		// Correct is_* for page_on_front and page_for_posts
		if ( $this->is_home && 'page' == get_option('show_on_front') && get_option('page_on_front') ) {
			$_query = wp_parse_args($this->query);
			// pagename can be set and empty depending on matched rewrite rules. Ignore an empty pagename.
			if ( isset($_query['pagename']) && '' == $_query['pagename'] )
			unset($_query['pagename']);
			if ( empty($_query) || !array_diff( array_keys($_query), array('preview', 'page', 'paged', 'cpage') ) ) {
				$this->is_page = true;
				$this->is_home = false;
				$qv['page_id'] = get_option('page_on_front');
				// Correct <!--nextpage--> for page_on_front
				if ( !empty($qv['paged']) ) {
					$qv['page'] = $qv['paged'];
					unset($qv['paged']);
				}
			}
		}

		if ( '' != $qv['pagename'] ) {
			$this->queried_object = get_page_by_path($qv['pagename']);
			if ( !empty($this->queried_object) )
			$this->queried_object_id = (int) $this->queried_object->ID;
			else
			unset($this->queried_object);

			if  ( 'page' == get_option('show_on_front') && isset($this->queried_object_id) && $this->queried_object_id == get_option('page_for_posts') ) {
				$this->is_page = false;
				$this->is_home = true;
				$this->is_posts_page = true;
			}
		}

		if ( $qv['page_id'] ) {
			if  ( 'page' == get_option('show_on_front') && $qv['page_id'] == get_option('page_for_posts') ) {
				$this->is_page = false;
				$this->is_home = true;
				$this->is_posts_page = true;
			}
		}

		if ( !empty($qv['post_type']) ) {
			if ( is_array($qv['post_type']) )
			$qv['post_type'] = array_map('sanitize_key', $qv['post_type']);
			else
			$qv['post_type'] = sanitize_key($qv['post_type']);
		}

		if ( ! empty( $qv['post_status'] ) ) {
			if ( is_array( $qv['post_status'] ) )
			$qv['post_status'] = array_map('sanitize_key', $qv['post_status']);
			else
			$qv['post_status'] = preg_replace('|[^a-z0-9_,-]|', '', $qv['post_status']);
		}

		if ( $this->is_posts_page && ( ! isset($qv['withcomments']) || ! $qv['withcomments'] ) )
		$this->is_comment_feed = false;

		$this->is_singular = $this->is_single || $this->is_page || $this->is_attachment;
		// Done correcting is_* for page_on_front and page_for_posts

		if ( '404' == $qv['error'] )
		$this->set_404();

		$this->query_vars_hash = md5( serialize( $this->query_vars ) );
		$this->query_vars_changed = false;

		do_action_ref_array('parse_query', array(&$this));
	}

	/*
	* Parses various taxonomy related query vars.
	*
	* @access protected
	* @since 3.1.0
	*
	* @param array &$q The query variables
	*/
	function parse_tax_query( &$q ) {
		if ( ! empty( $q['tax_query'] ) && is_array( $q['tax_query'] ) ) {
			$tax_query = $q['tax_query'];
		} else {
			$tax_query = array();
		}

		if ( !empty($q['taxonomy']) && !empty($q['term']) ) {
			$tax_query[] = array(
			'taxonomy' => $q['taxonomy'],
			'terms' => array( $q['term'] ),
			'field' => 'slug',
			);
		}

		foreach ( $GLOBALS['wp_taxonomies'] as $taxonomy => $t ) {
			if ( 'post_tag' == $taxonomy )
			continue;	// Handled further down in the $q['tag'] block

			if ( $t->query_var && !empty( $q[$t->query_var] ) ) {
				$tax_query_defaults = array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				);

				if ( isset( $t->rewrite['hierarchical'] ) && $t->rewrite['hierarchical'] ) {
					$q[$t->query_var] = wp_basename( $q[$t->query_var] );
				}

				$term = $q[$t->query_var];

				if ( strpos($term, '+') !== false ) {
					$terms = preg_split( '/[+]+/', $term );
					foreach ( $terms as $term ) {
						$tax_query[] = array_merge( $tax_query_defaults, array(
						'terms' => array( $term )
						) );
					}
				} else {
					$tax_query[] = array_merge( $tax_query_defaults, array(
					'terms' => preg_split( '/[,]+/', $term )
					) );
				}
			}
		}

		// Category stuff
		if ( !empty($q['cat']) && '0' != $q['cat'] && !$this->is_singular && $this->query_vars_changed ) {
			$q['cat'] = ''.urldecode($q['cat']).'';
			$q['cat'] = addslashes_gpc($q['cat']);
			$cat_array = preg_split('/[,\s]+/', $q['cat']);
			$q['cat'] = '';
			$req_cats = array();
			foreach ( (array) $cat_array as $cat ) {
				$cat = intval($cat);
				$req_cats[] = $cat;
				$in = ($cat > 0);
				$cat = abs($cat);
				if ( $in ) {
					$q['category__in'][] = $cat;
					$q['category__in'] = array_merge( $q['category__in'], get_term_children($cat, 'category') );
				} else {
					$q['category__not_in'][] = $cat;
					$q['category__not_in'] = array_merge( $q['category__not_in'], get_term_children($cat, 'category') );
				}
			}
			$q['cat'] = implode(',', $req_cats);
		}

		if ( !empty($q['category__in']) ) {
			$q['category__in'] = array_map('absint', array_unique( (array) $q['category__in'] ) );
			$tax_query[] = array(
			'taxonomy' => 'category',
			'terms' => $q['category__in'],
			'field' => 'term_id',
			'include_children' => false
			);
		}

		if ( !empty($q['category__not_in']) ) {
			$q['category__not_in'] = array_map('absint', array_unique( (array) $q['category__not_in'] ) );
			$tax_query[] = array(
			'taxonomy' => 'category',
			'terms' => $q['category__not_in'],
			'operator' => 'NOT IN',
			'include_children' => false
			);
		}

		if ( !empty($q['category__and']) ) {
			$q['category__and'] = array_map('absint', array_unique( (array) $q['category__and'] ) );
			$tax_query[] = array(
			'taxonomy' => 'category',
			'terms' => $q['category__and'],
			'field' => 'term_id',
			'operator' => 'AND',
			'include_children' => false
			);
		}

		// Tag stuff
		if ( '' != $q['tag'] && !$this->is_singular && $this->query_vars_changed ) {
			if ( strpos($q['tag'], ',') !== false ) {
				$tags = preg_split('/[,\r\n\t ]+/', $q['tag']);
				foreach ( (array) $tags as $tag ) {
					$tag = sanitize_term_field('slug', $tag, 0, 'post_tag', 'db');
					$q['tag_slug__in'][] = $tag;
				}
			} else if ( preg_match('/[+\r\n\t ]+/', $q['tag']) || !empty($q['cat']) ) {
				$tags = preg_split('/[+\r\n\t ]+/', $q['tag']);
				foreach ( (array) $tags as $tag ) {
					$tag = sanitize_term_field('slug', $tag, 0, 'post_tag', 'db');
					$q['tag_slug__and'][] = $tag;
				}
			} else {
				$q['tag'] = sanitize_term_field('slug', $q['tag'], 0, 'post_tag', 'db');
				$q['tag_slug__in'][] = $q['tag'];
			}
		}

		if ( !empty($q['tag_id']) ) {
			$q['tag_id'] = absint( $q['tag_id'] );
			$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms' => $q['tag_id']
			);
		}

		if ( !empty($q['tag__in']) ) {
			$q['tag__in'] = array_map('absint', array_unique( (array) $q['tag__in'] ) );
			$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms' => $q['tag__in']
			);
		}

		if ( !empty($q['tag__not_in']) ) {
			$q['tag__not_in'] = array_map('absint', array_unique( (array) $q['tag__not_in'] ) );
			$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms' => $q['tag__not_in'],
			'operator' => 'NOT IN'
			);
		}

		if ( !empty($q['tag__and']) ) {
			$q['tag__and'] = array_map('absint', array_unique( (array) $q['tag__and'] ) );
			$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms' => $q['tag__and'],
			'operator' => 'AND'
			);
		}

		if ( !empty($q['tag_slug__in']) ) {
			$q['tag_slug__in'] = array_map('sanitize_title_for_query', array_unique( (array) $q['tag_slug__in'] ) );
			$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms' => $q['tag_slug__in'],
			'field' => 'slug'
			);
		}

		if ( !empty($q['tag_slug__and']) ) {
			$q['tag_slug__and'] = array_map('sanitize_title_for_query', array_unique( (array) $q['tag_slug__and'] ) );
			$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms' => $q['tag_slug__and'],
			'field' => 'slug',
			'operator' => 'AND'
			);
		}

		$this->tax_query = $tax_query;
	}

	/**
	 * Sets the 404 property and saves whether query is feed.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	function set_404() {
		$is_feed = $this->is_feed;

		$this->init_query_flags();
		$this->is_404 = true;

		$this->is_feed = $is_feed;
	}

	/**
	 * Retrieve query variable.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @return mixed
	 */
	function get($query_var) {
		if ( isset($this->query_vars[$query_var]) )
		return $this->query_vars[$query_var];

		return '';
	}

	/**
	 * Set query variable.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed $value Query variable value.
	 */
	function set($query_var, $value) {
		$this->query_vars[$query_var] = $value;
	}

	/**
	 * Retrieve the posts based on query variables.
	 *
	 * There are a few filters and actions that can be used to modify the post
	 * database query.
	 *
	 * @since 1.5.0
	 * @access public
	 * @uses do_action_ref_array() Calls 'pre_get_posts' hook before retrieving posts.
	 *
	 * @return array List of posts.
	 */
	function get_posts() {
		global $wpdb, $user_ID, $_wp_using_ext_object_cache;

		$this->parse_query();

		do_action_ref_array('pre_get_posts', array(&$this));

		// Shorthand.
		$q = &$this->query_vars;


		// Fill again in case pre_get_posts unset some vars.
		$q = $this->fill_query_vars($q);


		// Parse meta query
		//$this->meta_query = new WP_Meta_Query();
		//$this->meta_query->parse_query_vars( $q );

		// Set a flag if a pre_get_posts hook changed the query vars.
		$hash = md5( serialize( $this->query_vars ) );
		if ( $hash != $this->query_vars_hash ) {
			$this->query_vars_changed = true;
			$this->query_vars_hash = $hash;
		}
		unset($hash);

		// First let's clear some variables
		$distinct = '';
		$whichauthor = '';
		$whichmimetype = '';
		$where = '';
		$limits = '';
		$join = '';
		$search = '';
		$groupby = '';
		$fields = '';
		$post_status_join = false;
		$page = 1;

		if(!$q['post_type']){
			$q['post_type'] = 'product';
		}

		if($q['post__in']){
			foreach ($q['post__in'] as $k=>$v){
				$this->posts[] = $this->qool->getContent($q['post_type'],$v);
			}
		}

		$this->post_count = count($this->posts);



		// Put sticky posts at the top of the posts array
		$sticky_posts = get_option('sticky_posts');
		if ( $this->is_home && $page <= 1 && is_array($sticky_posts) && !empty($sticky_posts) && !$q['ignore_sticky_posts'] ) {
			$num_posts = count($this->posts);
			$sticky_offset = 0;
			// Loop over posts and relocate stickies to the front.
			for ( $i = 0; $i < $num_posts; $i++ ) {
				if ( in_array($this->posts[$i]->ID, $sticky_posts) ) {
					$sticky_post = $this->posts[$i];
					// Remove sticky from current position
					array_splice($this->posts, $i, 1);
					// Move to front, after other stickies
					array_splice($this->posts, $sticky_offset, 0, array($sticky_post));
					// Increment the sticky offset. The next sticky will be placed at this offset.
					$sticky_offset++;
					// Remove post from sticky posts array
					$offset = array_search($sticky_post->ID, $sticky_posts);
					unset( $sticky_posts[$offset] );
				}
			}

			// If any posts have been excluded specifically, Ignore those that are sticky.
			if ( !empty($sticky_posts) && !empty($q['post__not_in']) )
			$sticky_posts = array_diff($sticky_posts, $q['post__not_in']);

			// Fetch sticky posts that weren't in the query results
			if ( !empty($sticky_posts) ) {
				$stickies = get_posts( array(
				'post__in' => $sticky_posts,
				'post_type' => $post_type,
				'post_status' => 'publish',
				'nopaging' => true
				) );

				foreach ( $stickies as $sticky_post ) {
					array_splice( $this->posts, $sticky_offset, 0, array( $sticky_post ) );
					$sticky_offset++;
				}
			}
		}




		return $this->posts;
	}

	/**
	 * Set up the amount of found posts and the number of pages (if limit clause was used)
	 * for the current query.
	 *
	 * @since 3.5.0
	 * @access private
	 */
	function set_found_posts( $q, $limits ) {
		global $wpdb;

		// Bail if posts is an empty array. Continue if posts is an empty string
		// null, or false to accommodate caching plugins that fill posts later.
		if ( $q['no_found_rows'] || ( is_array( $this->posts ) && ! $this->posts ) )
		return;

		if ( ! empty( $limits ) )
		echo '';//$this->found_posts = $wpdb->get_var( apply_filters_ref_array( 'found_posts_query', array( 'SELECT FOUND_ROWS()', &$this ) ) );
		else
		$this->found_posts = count( $this->posts );

		$this->found_posts = apply_filters_ref_array( 'found_posts', array( $this->found_posts, &$this ) );

		if ( ! empty( $limits ) )
		$this->max_num_pages = ceil( $this->found_posts / $q['posts_per_page'] );
	}

	/**
	 * Set up the next post and iterate current post index.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return WP_Post Next post.
	 */
	function next_post() {

		$this->current_post++;

		$this->post = $this->posts[$this->current_post];
		return $this->post;
	}

	/**
	 * Sets up the current post.
	 *
	 * Retrieves the next post, sets up the post, sets the 'in the loop'
	 * property to true.
	 *
	 * @since 1.5.0
	 * @access public
	 * @uses $post
	 * @uses do_action_ref_array() Calls 'loop_start' if loop has just started
	 */
	function the_post() {
		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == -1 ) // loop has just started
		do_action_ref_array('loop_start', array(&$this));

		$post = $this->next_post();
		setup_postdata($post);
	}

	/**
	 * Whether there are more posts available in the loop.
	 *
	 * Calls action 'loop_end', when the loop is complete.
	 *
	 * @since 1.5.0
	 * @access public
	 * @uses do_action_ref_array() Calls 'loop_end' if loop is ended
	 *
	 * @return bool True if posts are available, false if end of loop.
	 */
	function have_posts() {
		if ( $this->current_post + 1 < $this->post_count ) {
			return true;
		} elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) {
			do_action_ref_array('loop_end', array(&$this));
			// Do some cleaning up after the loop
			$this->rewind_posts();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the posts and reset post index.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	function rewind_posts() {
		$this->current_post = -1;
		if ( $this->post_count > 0 ) {
			$this->post = $this->posts[0];
		}
	}

	/**
	 * Iterate current comment index and return comment object.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return object Comment object.
	 */
	function next_comment() {
		$this->current_comment++;

		$this->comment = $this->comments[$this->current_comment];
		return $this->comment;
	}

	/**
	 * Sets up the current comment.
	 *
	 * @since 2.2.0
	 * @access public
	 * @global object $comment Current comment.
	 * @uses do_action() Calls 'comment_loop_start' hook when first comment is processed.
	 */
	function the_comment() {
		global $comment;

		$comment = $this->next_comment();

		if ( $this->current_comment == 0 ) {
			do_action('comment_loop_start');
		}
	}

	/**
	 * Whether there are more comments available.
	 *
	 * Automatically rewinds comments when finished.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return bool True, if more comments. False, if no more posts.
	 */
	function have_comments() {
		if ( $this->current_comment + 1 < $this->comment_count ) {
			return true;
		} elseif ( $this->current_comment + 1 == $this->comment_count ) {
			$this->rewind_comments();
		}

		return false;
	}

	/**
	 * Rewind the comments, resets the comment index and comment to first.
	 *
	 * @since 2.2.0
	 * @access public
	 */
	function rewind_comments() {
		$this->current_comment = -1;
		if ( $this->comment_count > 0 ) {
			$this->comment = $this->comments[0];
		}
	}

	/**
	 * Sets up the WordPress query by parsing query string.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string $query URL query string.
	 * @return array List of posts.
	 */
	function query( $query ) {
		$this->init();
		$this->query = $this->query_vars = wp_parse_args( $query );
		return $this->get_posts();
	}

	/**
	 * Retrieve queried object.
	 *
	 * If queried object is not set, then the queried object will be set from
	 * the category, tag, taxonomy, posts page, single post, page, or author
	 * query variable. After it is set up, it will be returned.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return object
	 */
	function get_queried_object() {
		if ( isset($this->queried_object) )
		return $this->queried_object;

		$this->queried_object = null;
		$this->queried_object_id = 0;

		if ( $this->is_category || $this->is_tag || $this->is_tax ) {
			$tax_query_in_and = wp_list_filter( $this->tax_query->queries, array( 'operator' => 'NOT IN' ), 'NOT' );

			$query = reset( $tax_query_in_and );

			if ( 'term_id' == $query['field'] )
			$term = get_term( reset( $query['terms'] ), $query['taxonomy'] );
			elseif ( $query['terms'] )
			$term = get_term_by( $query['field'], reset( $query['terms'] ), $query['taxonomy'] );

			if ( ! empty( $term ) && ! is_wp_error( $term ) )  {
				$this->queried_object = $term;
				$this->queried_object_id = (int) $term->term_id;

				if ( $this->is_category )
				_make_cat_compat( $this->queried_object );
			}
		} elseif ( $this->is_post_type_archive ) {
			$this->queried_object = get_post_type_object( $this->get('post_type') );
		} elseif ( $this->is_posts_page ) {
			$page_for_posts = get_option('page_for_posts');
			$this->queried_object = get_post( $page_for_posts );
			$this->queried_object_id = (int) $this->queried_object->ID;
		} elseif ( $this->is_singular && !is_null($this->post) ) {
			$this->queried_object = $this->post;
			$this->queried_object_id = (int) $this->post->ID;
		} elseif ( $this->is_author ) {
			$this->queried_object_id = (int) $this->get('author');
			$this->queried_object = get_userdata( $this->queried_object_id );
		}

		return $this->queried_object;
	}

	/**
	 * Retrieve ID of the current queried object.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return int
	 */
	function get_queried_object_id() {
		$this->get_queried_object();

		if ( isset($this->queried_object_id) ) {
			return $this->queried_object_id;
		}

		return 0;
	}

	/**
	 * Constructor.
	 *
	 * Sets up the WordPress query, if parameter is not empty.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string $query URL query string.
	 * @return WP_Query
	 */
	function __construct($query = '') {
		if ( ! empty($query) ) {
			$this->query($query);
		}
	}

	/**
 	 * Is the query for an existing archive page?
 	 *
 	 * Month, Year, Category, Author, Post Type archive...
	 *
 	 * @since 3.1.0
 	 *
 	 * @return bool
 	 */
	function is_archive() {
		return (bool) $this->is_archive;
	}

	/**
	 * Is the query for an existing post type archive page?
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post_types Optional. Post type or array of posts types to check against.
	 * @return bool
	 */
	function is_post_type_archive( $post_types = '' ) {
		if ( empty( $post_types ) || !$this->is_post_type_archive )
		return (bool) $this->is_post_type_archive;

		$post_type_object = $this->get_queried_object();

		return in_array( $post_type_object->name, (array) $post_types );
	}

	/**
	 * Is the query for an existing attachment page?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_attachment() {
		return (bool) $this->is_attachment;
	}

	/**
	 * Is the query for an existing author archive page?
	 *
	 * If the $author parameter is specified, this function will additionally
	 * check if the query is for one of the authors specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 * @return bool
	 */
	function is_author( $author = '' ) {
		if ( !$this->is_author )
		return false;

		if ( empty($author) )
		return true;

		$author_obj = $this->get_queried_object();

		$author = (array) $author;

		if ( in_array( $author_obj->ID, $author ) )
		return true;
		elseif ( in_array( $author_obj->nickname, $author ) )
		return true;
		elseif ( in_array( $author_obj->user_nicename, $author ) )
		return true;

		return false;
	}

	/**
	 * Is the query for an existing category archive page?
	 *
	 * If the $category parameter is specified, this function will additionally
	 * check if the query is for one of the categories specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 * @return bool
	 */
	function is_category( $category = '' ) {
		if ( !$this->is_category )
		return false;

		if ( empty($category) )
		return true;

		$cat_obj = $this->get_queried_object();

		$category = (array) $category;

		if ( in_array( $cat_obj->term_id, $category ) )
		return true;
		elseif ( in_array( $cat_obj->name, $category ) )
		return true;
		elseif ( in_array( $cat_obj->slug, $category ) )
		return true;

		return false;
	}

	/**
	 * Is the query for an existing tag archive page?
	 *
	 * If the $tag parameter is specified, this function will additionally
	 * check if the query is for one of the tags specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $slug Optional. Tag slug or array of slugs.
	 * @return bool
	 */
	function is_tag( $slug = '' ) {
		if ( !$this->is_tag )
		return false;

		if ( empty( $slug ) )
		return true;

		$tag_obj = $this->get_queried_object();

		$slug = (array) $slug;

		if ( in_array( $tag_obj->slug, $slug ) )
		return true;

		return false;
	}

	/**
	 * Is the query for an existing taxonomy archive page?
	 *
	 * If the $taxonomy parameter is specified, this function will additionally
	 * check if the query is for that specific $taxonomy.
	 *
	 * If the $term parameter is specified in addition to the $taxonomy parameter,
	 * this function will additionally check if the query is for one of the terms
	 * specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $taxonomy Optional. Taxonomy slug or slugs.
	 * @param mixed $term. Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 * @return bool
	 */
	function is_tax( $taxonomy = '', $term = '' ) {
		global $wp_taxonomies;

		if ( !$this->is_tax )
		return false;

		if ( empty( $taxonomy ) )
		return true;

		$queried_object = $this->get_queried_object();
		$tax_array = array_intersect( array_keys( $wp_taxonomies ), (array) $taxonomy );
		$term_array = (array) $term;

		// Check that the taxonomy matches.
		if ( ! ( isset( $queried_object->taxonomy ) && count( $tax_array ) && in_array( $queried_object->taxonomy, $tax_array ) ) )
		return false;

		// Only a Taxonomy provided.
		if ( empty( $term ) )
		return true;

		return isset( $queried_object->term_id ) &&
		count( array_intersect(
		array( $queried_object->term_id, $queried_object->name, $queried_object->slug ),
		$term_array
		) );
	}

	/**
	 * Whether the current URL is within the comments popup window.
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_comments_popup() {
		return (bool) $this->is_comments_popup;
	}

	/**
	 * Is the query for an existing date archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_date() {
		return (bool) $this->is_date;
	}

	/**
	 * Is the query for an existing day archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_day() {
		return (bool) $this->is_day;
	}

	/**
	 * Is the query for a feed?
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $feeds Optional feed types to check.
	 * @return bool
	 */
	function is_feed( $feeds = '' ) {
		if ( empty( $feeds ) || ! $this->is_feed )
		return (bool) $this->is_feed;
		$qv = $this->get( 'feed' );
		if ( 'feed' == $qv )
		$qv = get_default_feed();
		return in_array( $qv, (array) $feeds );
	}

	/**
	 * Is the query for a comments feed?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_comment_feed() {
		return (bool) $this->is_comment_feed;
	}

	/**
	 * Is the query for the front page of the site?
	 *
	 * This is for what is displayed at your site's main URL.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_on_front'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true when viewing that page.
	 *
	 * Otherwise the same as @see WP_Query::is_home()
	 *
	 * @since 3.1.0
	 * @uses is_home()
	 * @uses get_option()
	 *
	 * @return bool True, if front of site.
	 */
	function is_front_page() {
		// most likely case
		if ( 'posts' == get_option( 'show_on_front') && $this->is_home() )
		return true;
		elseif ( 'page' == get_option( 'show_on_front') && get_option( 'page_on_front' ) && $this->is_page( get_option( 'page_on_front' ) ) )
		return true;
		else
		return false;
	}

	/**
	 * Is the query for the blog homepage?
	 *
	 * This is the page which shows the time based blog content of your site.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_for_posts'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true only on the page you set as the "Posts page".
	 *
	 * @see WP_Query::is_front_page()
	 *
	 * @since 3.1.0
	 *
	 * @return bool True if blog view homepage.
	 */
	function is_home() {
		return (bool) $this->is_home;
	}

	/**
	 * Is the query for an existing month archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_month() {
		return (bool) $this->is_month;
	}

	/**
	 * Is the query for an existing single page?
	 *
	 * If the $page parameter is specified, this function will additionally
	 * check if the query is for one of the pages specified.
	 *
	 * @see WP_Query::is_single()
	 * @see WP_Query::is_singular()
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $page Page ID, title, slug, or array of such.
	 * @return bool
	 */
	function is_page( $page = '' ) {
		if ( !$this->is_page )
		return false;

		if ( empty( $page ) )
		return true;

		$page_obj = $this->get_queried_object();

		$page = (array) $page;

		if ( in_array( $page_obj->ID, $page ) )
		return true;
		elseif ( in_array( $page_obj->post_title, $page ) )
		return true;
		else if ( in_array( $page_obj->post_name, $page ) )
		return true;

		return false;
	}

	/**
	 * Is the query for paged result and not for the first page?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_paged() {
		return (bool) $this->is_paged;
	}

	/**
	 * Is the query for a post or page preview?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_preview() {
		return (bool) $this->is_preview;
	}

	/**
	 * Is the query for the robots file?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_robots() {
		return (bool) $this->is_robots;
	}

	/**
	 * Is the query for a search?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_search() {
		return (bool) $this->is_search;
	}

	/**
	 * Is the query for an existing single post?
	 *
	 * Works for any post type, except attachments and pages
	 *
	 * If the $post parameter is specified, this function will additionally
	 * check if the query is for one of the Posts specified.
	 *
	 * @see WP_Query::is_page()
	 * @see WP_Query::is_singular()
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post Post ID, title, slug, or array of such.
	 * @return bool
	 */
	function is_single( $post = '' ) {
		if ( !$this->is_single )
		return false;

		if ( empty($post) )
		return true;

		$post_obj = $this->get_queried_object();

		$post = (array) $post;

		if ( in_array( $post_obj->ID, $post ) )
		return true;
		elseif ( in_array( $post_obj->post_title, $post ) )
		return true;
		elseif ( in_array( $post_obj->post_name, $post ) )
		return true;

		return false;
	}

	/**
	 * Is the query for an existing single post of any post type (post, attachment, page, ... )?
	 *
	 * If the $post_types parameter is specified, this function will additionally
	 * check if the query is for one of the Posts Types specified.
	 *
	 * @see WP_Query::is_page()
	 * @see WP_Query::is_single()
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post_types Optional. Post Type or array of Post Types
	 * @return bool
	 */
	function is_singular( $post_types = '' ) {
		if ( empty( $post_types ) || !$this->is_singular )
		return (bool) $this->is_singular;

		$post_obj = $this->get_queried_object();

		return in_array( $post_obj->post_type, (array) $post_types );
	}

	/**
	 * Is the query for a specific time?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_time() {
		return (bool) $this->is_time;
	}

	/**
	 * Is the query for a trackback endpoint call?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_trackback() {
		return (bool) $this->is_trackback;
	}

	/**
	 * Is the query for an existing year archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_year() {
		return (bool) $this->is_year;
	}

	/**
	 * Is the query a 404 (returns no results)?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	function is_404() {
		return (bool) $this->is_404;
	}

	/**
	 * Is the query the main query?
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	function is_main_query() {
		global $wp_the_query;
		return $wp_the_query === $this;
	}
}

class WP_Styles {
	var $base_url;
	var $content_url;
	var $default_version;
	var $text_direction = 'ltr';
	var $concat = '';
	var $concat_version = '';
	var $do_concat = false;
	var $print_html = '';
	var $print_code = '';
	var $default_dirs;

	function WP_Styles() {
		
	}

	function do_item( $handle ) {
		return true;
	}

	function add_inline_style( $handle, $code ) {
		
	}

	function print_inline_style( $handle, $echo = true ) {
		

		return true;
	}

	function all_deps( $handles, $recursion = false, $group = false ) {
		
	}

	function _css_href( $src, $ver, $handle ) {
		
	}

	function in_default_dir($src) {
		
	}

	function do_footer_items() { // HTML 5 allows styles in the body, grab late enqueued items and output them in the footer.
		
	}

	function reset() {
		
	}
	
	function add_data(){
		
	}
}

class WP_Widget {

	var $id_base;			// Root id for all widgets of this type.
	var $name;				// Name for this widget type.
	var $widget_options;	// Option array passed to wp_register_sidebar_widget()
	var $control_options;	// Option array passed to wp_register_widget_control()

	var $number = false;	// Unique ID number of the current instance.
	var $id = false;		// Unique ID string of the current instance (id_base-number)
	var $updated = false;	// Set true when we update the data after a POST submit - makes sure we don't do it twice.

	// Member functions that you must over-ride.

	/** Echo the widget content.
	 *
	 * Subclasses should over-ride this function to generate their widget code.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget($args, $instance) {
		die('function WP_Widget::widget() must be over-ridden in a sub-class.');
	}

	/** Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** Echo the settings update form
	 *
	 * @param array $instance Current settings
	 */
	function form($instance) {
		echo '<p class="no-options-widget">' . __('There are no options for this widget.') . '</p>';
		return 'noform';
	}

	// Functions you'll need to call.

	/**
	 * PHP4 constructor
	 */
	function WP_Widget( $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
		WP_Widget::__construct( $id_base, $name, $widget_options, $control_options );
	}

	/**
	 * PHP5 constructor
	 *
	 * @param string $id_base Optional Base ID for the widget, lower case,
	 * if left empty a portion of the widget's class name will be used. Has to be unique.
	 * @param string $name Name for the widget displayed on the configuration page.
	 * @param array $widget_options Optional Passed to wp_register_sidebar_widget()
	 *	 - description: shown on the configuration page
	 *	 - classname
	 * @param array $control_options Optional Passed to wp_register_widget_control()
	 *	 - width: required if more than 250px
	 *	 - height: currently not used but may be needed in the future
	 */
	function __construct( $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
		$this->id_base = empty($id_base) ? preg_replace( '/(wp_)?widget_/', '', strtolower(get_class($this)) ) : strtolower($id_base);
		$this->name = $name;
		$this->option_name = 'widget_' . $this->id_base;
		$this->widget_options = wp_parse_args( $widget_options, array('classname' => $this->option_name) );
		$this->control_options = wp_parse_args( $control_options, array('id_base' => $this->id_base) );
	}

	/**
	 * Constructs name attributes for use in form() fields
	 *
	 * This function should be used in form() methods to create name attributes for fields to be saved by update()
	 *
	 * @param string $field_name Field name
	 * @return string Name attribute for $field_name
	 */
	function get_field_name($field_name) {
		return 'widget-' . $this->id_base . '[' . $this->number . '][' . $field_name . ']';
	}

	/**
	 * Constructs id attributes for use in form() fields
	 *
	 * This function should be used in form() methods to create id attributes for fields to be saved by update()
	 *
	 * @param string $field_name Field name
	 * @return string ID attribute for $field_name
	 */
	function get_field_id($field_name) {
		return 'widget-' . $this->id_base . '-' . $this->number . '-' . $field_name;
	}

	// Private Functions. Don't worry about these.

	function _register() {
		$settings = $this->get_settings();
		$empty = true;

		if ( is_array($settings) ) {
			foreach ( array_keys($settings) as $number ) {
				if ( is_numeric($number) ) {
					$this->_set($number);
					$this->_register_one($number);
					$empty = false;
				}
			}
		}

		if ( $empty ) {
			// If there are none, we register the widget's existence with a
			// generic template
			$this->_set(1);
			$this->_register_one();
		}
	}

	function _set($number) {
		$this->number = $number;
		$this->id = $this->id_base . '-' . $number;
	}

	function _get_display_callback() {
		return array($this, 'display_callback');
	}

	function _get_update_callback() {
		return array($this, 'update_callback');
	}

	function _get_form_callback() {
		return array($this, 'form_callback');
	}

	/** Generate the actual widget content.
	 *	Just finds the instance and calls widget().
	 *	Do NOT over-ride this function. */
	function display_callback( $args, $widget_args = 1 ) {
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );

		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		$this->_set( $widget_args['number'] );
		$instance = $this->get_settings();

		if ( array_key_exists( $this->number, $instance ) ) {
			$instance = $instance[$this->number];
			// filters the widget's settings, return false to stop displaying the widget
			$instance = apply_filters('widget_display_callback', $instance, $this, $args);
			if ( false !== $instance )
				$this->widget($args, $instance);
		}
	}

	/** Deal with changed settings.
	 *	Do NOT over-ride this function. */
	function update_callback( $widget_args = 1 ) {
		global $wp_registered_widgets;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );

		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		$all_instances = $this->get_settings();

		// We need to update the data
		if ( $this->updated )
			return;

		$sidebars_widgets = wp_get_sidebars_widgets();

		if ( isset($_POST['delete_widget']) && $_POST['delete_widget'] ) {
			// Delete the settings for this instance of the widget
			if ( isset($_POST['the-widget-id']) )
				$del_id = $_POST['the-widget-id'];
			else
				return;

			if ( isset($wp_registered_widgets[$del_id]['params'][0]['number']) ) {
				$number = $wp_registered_widgets[$del_id]['params'][0]['number'];

				if ( $this->id_base . '-' . $number == $del_id )
					unset($all_instances[$number]);
			}
		} else {
			if ( isset($_POST['widget-' . $this->id_base]) && is_array($_POST['widget-' . $this->id_base]) ) {
				$settings = $_POST['widget-' . $this->id_base];
			} elseif ( isset($_POST['id_base']) && $_POST['id_base'] == $this->id_base ) {
				$num = $_POST['multi_number'] ? (int) $_POST['multi_number'] : (int) $_POST['widget_number'];
				$settings = array( $num => array() );
			} else {
				return;
			}

			foreach ( $settings as $number => $new_instance ) {
				$new_instance = stripslashes_deep($new_instance);
				$this->_set($number);

				$old_instance = isset($all_instances[$number]) ? $all_instances[$number] : array();

				$instance = $this->update($new_instance, $old_instance);

				// filters the widget's settings before saving, return false to cancel saving (keep the old settings if updating)
				$instance = apply_filters('widget_update_callback', $instance, $new_instance, $old_instance, $this);
				if ( false !== $instance )
					$all_instances[$number] = $instance;

				break; // run only once
			}
		}

		$this->save_settings($all_instances);
		$this->updated = true;
	}

	/** Generate the control form.
	 *	Do NOT over-ride this function. */
	function form_callback( $widget_args = 1 ) {
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );

		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		$all_instances = $this->get_settings();

		if ( -1 == $widget_args['number'] ) {
			// We echo out a form where 'number' can be set later
			$this->_set('__i__');
			$instance = array();
		} else {
			$this->_set($widget_args['number']);
			$instance = $all_instances[ $widget_args['number'] ];
		}

		// filters the widget admin form before displaying, return false to stop displaying it
		$instance = apply_filters('widget_form_callback', $instance, $this);

		$return = null;
		if ( false !== $instance ) {
			$return = $this->form($instance);
			// add extra fields in the widget form - be sure to set $return to null if you add any
			// if the widget has no form the text echoed from the default form method can be hidden using css
			do_action_ref_array( 'in_widget_form', array(&$this, &$return, $instance) );
		}
		return $return;
	}

	/** Helper function: Registers a single instance. */
	function _register_one($number = -1) {
		wp_register_sidebar_widget(	$this->id, $this->name,	$this->_get_display_callback(), $this->widget_options, array( 'number' => $number ) );
		_register_widget_update_callback( $this->id_base, $this->_get_update_callback(), $this->control_options, array( 'number' => -1 ) );
		_register_widget_form_callback(	$this->id, $this->name,	$this->_get_form_callback(), $this->control_options, array( 'number' => $number ) );
	}

	function save_settings($settings) {
		$settings['_multiwidget'] = 1;
		update_option( $this->option_name, $settings );
	}

	function get_settings() {
		$settings = get_option($this->option_name);

		if ( false === $settings && isset($this->alt_option_name) )
			$settings = get_option($this->alt_option_name);

		if ( !is_array($settings) )
			$settings = array();

		if ( !empty($settings) && !array_key_exists('_multiwidget', $settings) ) {
			// old format, convert if single widget
			$settings = wp_convert_widget_settings($this->id_base, $this->option_name, $settings);
		}

		unset($settings['_multiwidget'], $settings['__i__']);
		return $settings;
	}
}

define( 'EZSQL_VERSION', 'WP1.25' );


define( 'OBJECT', 'OBJECT', true );


define( 'OBJECT_K', 'OBJECT_K' );


define( 'ARRAY_A', 'ARRAY_A' );


define( 'ARRAY_N', 'ARRAY_N' );

class wpdb {
	var $show_errors = false;
	var $suppress_errors = false;
	var $last_error = '';
	var $num_queries = 0;
	var $num_rows = 0;
	var $rows_affected = 0;
	var $insert_id = 0;
	var $last_query;
	var $last_result;
	protected $result;
	protected $col_info;
	var $queries;
	var $prefix = '';
	var $ready = false;
	var $blogid = 0;
	var $siteid = 0;
	var $tables = array( 'posts', 'comments', 'links', 'options', 'postmeta',
		'terms', 'term_taxonomy', 'term_relationships', 'commentmeta' );
	var $old_tables = array( 'categories', 'post2cat', 'link2cat' );
	var $global_tables = array( 'users', 'usermeta' );
	var $ms_global_tables = array( 'blogs', 'signups', 'site', 'sitemeta',
		'sitecategories', 'registration_log', 'blog_versions' );
	var $comments;
	var $commentmeta;
	var $links;
	var $options;
	var $postmeta;
	var $posts;
	var $terms;
	var $term_relationships;
	var $term_taxonomy;
	var $usermeta;
	var $users;
	var $blogs;
	var $blog_versions;
	var $registration_log;
	var $signups;
	var $site;
	var $sitecategories;
	var $sitemeta;
	var $field_types = array();
	var $charset;
	var $collate;
	var $real_escape = false;
	protected $dbuser;
	protected $dbpassword;
	protected $dbname;
	protected $dbhost;
	protected $dbh;
	var $func_call;
	public $is_mysql = null;
	function __construct(  ) {
		
		
	}

	
	function __destruct() {
		return true;
	}

	function __get( $name ) {
		if ( 'col_info' == $name )
			$this->load_col_info();

		return $this->$name;
	}

	
	function __set( $name, $value ) {
		$this->$name = $value;
	}

	
	function __isset( $name ) {
		return isset( $this->$name );
	}

	
	function __unset( $name ) {
		unset( $this->$name );
	}

	
	function init_charset() {
		
	}

	
	function set_charset($dbh, $charset = null, $collate = null) {
		
	}

	
	function set_prefix( $prefix, $set_table_names = true ) {

	}

	
	function set_blog_id( $blog_id, $site_id = 0 ) {
		
	}

	
	function get_blog_prefix( $blog_id = null ) {
		
	}

	function tables( $scope = 'all', $prefix = true, $blog_id = 0 ) {
		
	}

	
	function select( $db, $dbh = null ) {
		
		
	}

	
	function _weak_escape( $string ) {
		return addslashes( $string );
	}

	
	function _real_escape( $string ) {
		if ( $this->dbh && $this->real_escape )
			return mysql_real_escape_string( $string, $this->dbh );
		else
			return addslashes( $string );
	}

	
	function _escape( $data ) {
		if ( is_array( $data ) ) {
			foreach ( (array) $data as $k => $v ) {
				if ( is_array($v) )
					$data[$k] = $this->_escape( $v );
				else
					$data[$k] = $this->_real_escape( $v );
			}
		} else {
			$data = $this->_real_escape( $data );
		}

		return $data;
	}

	
	function escape( $data ) {
		if ( is_array( $data ) ) {
			foreach ( (array) $data as $k => $v ) {
				if ( is_array( $v ) )
					$data[$k] = $this->escape( $v );
				else
					$data[$k] = $this->_weak_escape( $v );
			}
		} else {
			$data = $this->_weak_escape( $data );
		}

		return $data;
	}

	
	function escape_by_ref( &$string ) {
		if ( ! is_float( $string ) )
			$string = $this->_real_escape( $string );
	}

	
	
	function prepare( $query, $args ) {
		
	}

	
	function print_error( $str = '' ) {
		
	}

	
	function show_errors( $show = true ) {
		$errors = $this->show_errors;
		$this->show_errors = $show;
		return $errors;
	}

	
	function hide_errors() {
		$show = $this->show_errors;
		$this->show_errors = false;
		return $show;
	}

	
	function suppress_errors( $suppress = true ) {
		$errors = $this->suppress_errors;
		$this->suppress_errors = (bool) $suppress;
		return $errors;
	}

	
	function flush() {
		
	}

	
	function db_connect() {

		

		
	}

	
	function query( $query ) {
		
	}

	
	function insert( $table, $data, $format = null ) {
		
	}

	
	function replace( $table, $data, $format = null ) {
		
	}

	
	function _insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ) {
		
	}

	
	function update( $table, $data, $where, $format = null, $where_format = null ) {
		
	}

	
	function delete( $table, $where, $where_format = null ) {
		
	}


	
	function get_var( $query = null, $x = 0, $y = 0 ) {
		
	}

	
	function get_row( $query = null, $output = OBJECT, $y = 0 ) {
		
	}

	
	function get_col( $query = null , $x = 0 ) {
		
	}

	
	function get_results( $query = null, $output = OBJECT ) {
		
	}

	
	protected function load_col_info() {
		
	}

	
	function get_col_info( $info_type = 'name', $col_offset = -1 ) {
		
	}

	
	function timer_start() {
		$this->time_start = microtime( true );
		return true;
	}

	
	function timer_stop() {
		return ( microtime( true ) - $this->time_start );
	}

	
	function bail( $message, $error_code = '500' ) {
		
	}

	
	function check_database_version() {
		}

	
	function supports_collation() {
		
	}

	
	public function get_charset_collate() {
		
	}

	
	function has_cap( $db_cap ) {
		
	}

	
	function get_caller() {
		
	}

	
	function db_version() {
		
	}
}

function get_file_data( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	if ( $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
		$extra_headers = array_combine( $extra_headers, $extra_headers ); // keys equal values
		$all_headers = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}

	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
			$all_headers[ $field ] = _cleanup_header_comment( $match[1] );
		else
			$all_headers[ $field ] = '';
	}

	return $all_headers;
}
function _cleanup_header_comment($str) {
	return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}

?>