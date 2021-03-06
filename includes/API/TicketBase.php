<?php

namespace WPAS_API\API;

use WP_REST_Server;
use WP_REST_Posts_Controller;
use WP_Error;

class TicketBase extends WP_REST_Posts_Controller {


	public function __construct( $post_type ) {
		parent::__construct( $post_type );

		$this->namespace = wpas_api()->get_api_namespace();
	}

	/**
	 * Prepare item query and add ticket ID if it exists
	 *
	 * @param array $prepared_args
	 * @param null  $request
	 *
	 * @return array
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {
		$query_args = parent::prepare_items_query( $prepared_args, $request );

		if ( $request['ticket_id'] ) {
			$query_args['post_parent'] = absint( $request['ticket_id'] );
		}

		return apply_filters( "wpas_api_{$this->rest_base}_prepare_items_query", $query_args, $prepared_args, $request, $this );
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['status'] = array(
			'default'           => 'any',
			'description'       => __( 'Limit result set to items assigned one or more statuses.', 'awesome-support-api' ),
			'type'              => 'array',
			'items'             => array(
				'enum'          =>  array( 'read', 'unread', 'any' ),
				'type'          => 'string',
			),
			'sanitize_callback' => array( $this, 'sanitize_ticket_param' ),
		);

		/**
		 * Filter collection parameters for the posts controller.
		 *
		 * @param array   $query_params JSON Schema-formatted collection parameters.
		 * @param object  Tickets
		 */
		return apply_filters( "wpas_api_{$this->rest_base}_collection_params", $query_params, $this );
	}

	/**
	 * Sanitizes and validates a list of arguments against the provided attributes.
	 *
	 * @param  string|array    $statuses  One or more post statuses.
	 * @param  \WP_REST_Request $request   Full details about the request.
	 * @param  string          $parameter Additional parameter to pass to validation.
	 * @return array|WP_Error A list of valid statuses, otherwise WP_Error object.
	 */
	public function sanitize_ticket_param( $statuses, $request, $parameter ) {
		$items = wp_parse_slug_list( $statuses );

		// The default status is different in WP_REST_Attachments_Controller
		$attributes = $request->get_attributes();
		$default    = isset( $attributes['args'][ $parameter ]['default'] ) ? $attributes['args'][ $parameter ]['default'] : '' ;

		foreach ( $items as $item ) {
			if ( $item === $default ) {
				continue;
			}

			$result = rest_validate_request_arg( $item, $request, $parameter );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return apply_filters( "wpas_api_{$this->rest_base}_sanitize_ticket_param", $items, $statuses, $request, $parameter, $this );
	}

	/**
	 * Checks if a ticket can be read by the current user
	 *
	 * Correctly handles posts with the inherit status.
	 *
	 * @param \WP_Post $post
	 * @return bool Whether the post can be read.
	 */
	public function check_read_permission( $post ) {

		// make sure we are dealing with the ticket
		if ( 'ticket' != $post->post_type ) {
			$post = get_post( $post->post_parent );
		}

		$return = wpas_can_view_ticket( $post->ID );

		if ( 'public' === get_post_meta( $post->ID , '_wpas_pbtk_flag', true ) ) {
			$return = true;
		}

		return apply_filters( 'wpas_api_check_ticket_read_permission', $return, $post, $this );
	}

}