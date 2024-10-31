<?php
/**
 * Base Api Class
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Mwb_Gf_Integration_With_Engagebay
 * @subpackage Mwb_Gf_Integration_With_Engagebay/mwb-crm-fw/api
 */

/**
 * Base Api Class.
 *
 * This class defines all code necessary api communication.
 *
 * @since      1.0.0
 * @package    Mwb_Gf_Integration_With_Engagebay
 * @subpackage Mwb_Gf_Integration_With_Engagebay/mwb-crm-fw/api
 */
class Mwb_Gf_Integration_Engagebay_Api_Base extends Mwb_Gf_Integration_Api_Base {

	/**
	 * Crm prefix
	 *
	 * @var    string   Crm prefix
	 * @since  1.0.0
	 */
	public static $crm_prefix;

	/**
	 * Production Base auth url.
	 *
	 * @var     string  Production Base auth url
	 * @since   1.0.0
	 */
	public static $api_url = 'https://app.engagebay.com/dev/api/';

	/**
	 * Engagebay API key
	 *
	 * @var     string  api key
	 * @since   1.0.0
	 */
	private static $api_key;

	/**
	 * Current instance URL
	 *
	 * @var     string    Current instance url.
	 * @since   1.0.0
	 */
	public static $instance_url;

	/**
	 * Creates an instance of the class
	 *
	 * @var     object     An instance of the class
	 * @since   1.0.0
	 */
	protected static $_instance = null; // phpcs:ignore

	/**
	 * Main Mwb_Gf_Integration_Engagebay_Api_Base Instance.
	 *
	 * Ensures only one instance of Mwb_Gf_Integration_Engagebay_Api_Base is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @return Mwb_Gf_Integration_Engagebay_Api_Base - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		self::initialize();
		return self::$_instance;
	}

	/**
	 * Initialize properties.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $token_data Saved token data.
	 */
	public static function initialize( $token_data = array() ) {

		self::$crm_prefix = Mwb_Gf_Integration_With_Engagebay::mwb_get_current_crm_property( 'slug' );

		self::$api_key    = get_option( 'mwb-' . self::$crm_prefix . '-gf-api-key', '' );
	}

	/**
	 * Get api domain.
	 *
	 * @since    1.0.0
	 * @return   string   Site redirecrt Uri.
	 */
	public function get_redirect_uri() {
		return admin_url();
	}

	/**
	 * Get instance url.
	 *
	 * @since    1.0.0
	 * @return   string   Instance url.
	 */
	public function get_instance_url() {
		return ! empty( self::$instance_url ) ? self::$instance_url : false;
	}


	/**
	 * Get Request headers.
	 *
	 * @since    1.0.0
	 * @param string $method Request method.
	 * @return   array   Headers.
	 */
	public function get_auth_header( $method = '' ) {
		$authorization_key = self::$api_key;
		if ( ! empty( $method ) && 'get' === $method ) {
			$headers = array(
				'Authorization' => $authorization_key,
				'Accept'        => 'application/json',
			);
		} else {
			$headers = array(
				'Authorization' => $authorization_key,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			);
		}
		return $headers;
	}


	/**
	 * Create single record on Engagebay
	 *
	 * @param  string  $object      CRM object name.
	 * @param  array   $record_data Request data.
	 * @param  boolean $is_bulk     Is a bulk request.
	 * @param  array   $log_data    Data to create log.
	 * @param  bool    $manual_sync If synced manually.
	 *
	 * @since 1.0.0
	 *
	 * @return array Response data.
	 */
	public function create_or_update_record( $object, $record_data, $is_bulk = false, $log_data = array(), $manual_sync = false ) {
		$is_update     = false;
		$response_data = array(
			'success' => false,
			'msg'     => __( 'Something went wrong', 'mwb-gf-integration-with-engagebay' ),
		);

		$record_id = false;
		$feed_id   = ! empty( $log_data['feed_id'] ) ? $log_data['feed_id'] : false;
		if ( $manual_sync && ! empty( $log_data['method'] ) ) {
			$event = $log_data['method'];
		} else {
			$event = __FUNCTION__;
		}

		// Check for the existing record based on selected primary field.
		if ( $feed_id ) {
			$duplicate_check_fields = get_post_meta( $feed_id, 'mwb-' . self::$crm_prefix . '-gf-primary-field', true );
			$primary_field          = ! empty( $duplicate_check_fields ) ? $duplicate_check_fields : false;
		}

		if ( $primary_field ) {
			$search_response = $this->check_for_existing_record( $object, $record_data[ $primary_field ], $primary_field );
		}

		$record_id = ! empty( $search_response ) ? $search_response : false;

		if ( ! $record_id ) {

			$response  = $this->create_record( $object, $record_data, $is_bulk, $log_data );
			$is_update = 'create';
			if ( $this->is_success( $response ) ) {
				$response_data['success']  = true;
				$response_data['msg']      = 'Create_Record';
				$response_data['response'] = $response;
				$response_data['id']       = $this->get_object_id_from_response( $response );
				if ( isset( $response['data'] ) ) {
					if ( ! empty( $response['company-updated'] ) ) {
						$response_data['msg'] = 'Update_Record';
						$is_update            = 'update';
					}
				}
			} else {
				$response_data['success']  = false;
				$response_data['msg']      = esc_html__( 'Error posting to CRM', 'mwb-gf-integration-with-engagebay' );
				$response_data['response'] = $response;
			}
		} else {

			// Update an existing record based on record_id.
			$response = $this->update_record( $record_id, $object, $record_data, $is_bulk, $log_data );
			if ( $this->is_success( $response ) ) {
				$response_data['success']  = true;
				$response_data['msg']      = 'Update_Record';
				$response_data['response'] = $response;
				$response_data['id']       = $record_id;
			}
			$is_update = 'update';
		}

		// Insert log in db.
		$this->log_request_in_db( $event, $object, $record_data, $response, $log_data, $is_update );

		return $response_data;
	}

	/**
	 * Insert log data in db.
	 *
	 * @param     string  $event                Trigger event/ Feed .
	 * @param     string  $engagebay_object     Name of engagebay module.
	 * @param     array   $request              An array of request data.
	 * @param     array   $response             An array of response data.
	 * @param     array   $log_data             Data to log.
	 * @param     boolean $is_update            update or not.
	 * @return    void
	 */
	public function log_request_in_db( $event, $engagebay_object, $request, $response, $log_data, $is_update = false ) {

		$engagebay_id = $this->get_object_id_from_response( $response );

		if ( '-' == $engagebay_id ) { // phpcs:ignore
			if ( ! empty( $log_data['id'] ) ) {
				$engagebay_id = $log_data['id'];
			}
		}

		if ( isset( $request['form_name'] ) ) {
			unset( $request['form_name'] );
		}
		if ( is_array( $request ) && ! empty( $request ) ) {
			foreach ( $request as $name => $value ) {
				$request[ $name ] = $value['value'];
			}
		}
		$request  = serialize( $request ); // @codingStandardsIgnoreLine
		if ( ! empty( $response['data'] ) ) {
			$response['data'] = json_decode( $response['data'], ARRAY_A );
		}
		if ( ! empty( $response['company-updated'] ) ) {
			unset( $response['company-updated'] );
		}
		$response = serialize( $response ); // @codingStandardsIgnoreLine

		switch ( $is_update ) {
			case 'update':
				$operation = 'Update';
				break;

			case 'create':
			default:
				$operation = 'Create';
				break;
		}

		$feed             = ! empty( $log_data['feed_name'] ) ? $log_data['feed_name'] : false;
		$feed_id          = ! empty( $log_data['feed_id'] ) ? $log_data['feed_id'] : false;
		$event            = ! empty( $event ) ? $event : false;
		$engagebay_object = ! empty( $log_data['engagebay_object'] ) ? $log_data['engagebay_object'] . ' - ' . $operation : false;

		$time     = time();
		$log_data = compact( 'event', 'engagebay_object', 'request', 'response', 'engagebay_id', 'feed_id', 'feed', 'time' );
		$this->insert_log_data( $log_data );

	}

	/**
	 * Retrieve object ID from crm response.
	 *
	 * @param     array $response     An array of response data from crm.
	 * @since     1.0.0
	 * @return    integer
	 */
	public function get_object_id_from_response( $response ) {
		$id = '-';
		if ( isset( $response['data'] ) ) {
			$response['data'] = json_decode( $response['data'], ARRAY_A );
			if ( isset( $response['data']['id'] ) ) {
				return ! empty( $response['data']['id'] ) ? $response['data']['id'] : $id;
			}
		}
		return $id;
	}

	/**
	 * Insert data to db.
	 *
	 * @param      array $data    Data to log.
	 * @since      1.0.0
	 * @return     void
	 */
	public function insert_log_data( $data ) {

		$connect         = 'Mwb_Gf_Integration_Connect_' . self::$crm_prefix . '_Framework';
		$connect_manager = $connect::get_instance();

		if ( 'yes' != $connect_manager->get_settings_details( 'logs' ) ) { // phpcs:ignore
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'mwb_' . self::$crm_prefix . '_gf_log';
		$wpdb->insert( $table, $data ); // phpcs:ignore
	}

	/**
	 * Check for exsiting record in search query response.
	 *
	 * @param array  $response      Search query response.
	 * @param array  $record_data   Request data of searched record.
	 * @param string $primary_field Primary field name.
	 *
	 * @return string|bool          Id of existing record or false.
	 */
	public function may_be_get_record_id_from_search( $response, $record_data, $primary_field ) {
		$record_id     = false;
		$found_records = array();
		if ( isset( $response['code'] ) && 200 == $response['code'] && 'OK' == $response['message'] ) { // phpcs:ignore
			if ( ! empty( $response['data'] ) && ! empty( $response['data']['searchRecords'] ) ) {
				$found_records = $response['data']['searchRecords'];
			}
		}

		if ( count( $found_records ) > 0 ) {
			foreach ( $found_records as $key => $record ) {
				if ( $record[ $primary_field ] === $record_data[ $primary_field ] ) {
					$record_id = $record['Id'];
					break;
				}
			}
		}
		return $record_id;
	}

	/**
	 * Check for existing record using parameterizedSearch.
	 *
	 * @param string $object        Target object name.
	 * @param array  $record_data   Record data.
	 * @param string $primary_field Primary field.
	 *
	 * @return array                Response data array.
	 */
	public function check_for_existing_record( $object, $record_data, $primary_field ) {

		if ( empty( $object ) ) {
			return;
		}
		$record_id = '';
		// This GET Request is a query or a CRUD operation.
		$this->base_url = self::$api_url;
		$headers        = $this->get_auth_header( 'get' );
		if ( 'Contacts' === $object ) {
			$endpoint    = 'panel/subscribers/contact-by-email/' . $record_data['value'];
			$get_contact = $this->get( $endpoint, array(), $headers );
			if ( $this->is_success( $get_contact ) ) {
				$get_contact['data'] = json_decode( $get_contact['data'], ARRAY_A );
				$response_data       = $get_contact['data'];
				$record_id           = $response_data['id'];
			}
		}

		return $record_id;
	}

	/**
	 * Check if resposne has success code.
	 *
	 * @param  array $response  Response data.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean true|false.
	 */
	public function is_success( $response ) {
		if ( ! empty( $response['code'] ) ) {
			return in_array( $response['code'], array( 200, 201, 204, 202 ) ); // phpcs:ignore
		}
		return false;
	}

	/**
	 * Create a new record.
	 *
	 * @param  string  $object     Object name.
	 * @param  array   $record_data Record data.
	 * @param  boolean $is_bulk    Is a bulk request.
	 * @param  array   $log_data   Data to create log.
	 * @return array               Response data.
	 */
	public function create_record( $object, $record_data, $is_bulk, $log_data ) {
		if ( empty( $object ) || empty( $record_data ) ) {
			return;
		}
		$this->base_url = self::$api_url;
		$headers        = $this->get_auth_header();
		$sync_data      = array();
		$form_name      = '';
		if ( isset( $record_data['form_name'] ) ) {
			$form_name = $record_data['form_name'];
			unset( $record_data['form_name'] );
		}

		if ( 'Contacts' === $object ) {
			$endpoint = 'panel/subscribers/subscriber';
			if ( empty( $record_data['email']['value'] ) ) {
				$response = array(
					'code'    => 400,
					'message' => 'Bad Request',
					'data'    => 'Email field is empty.',
				);
			} else {
				foreach ( $record_data as $name => $value ) {
					$sync_data[] = array(
						'name'  => $name,
						'value' => $value['value'],
					);
				}
				$contact_data = array(
					'properties' => $sync_data,
					'tags'       => array(
						array( 'tag' => $form_name ),
					),
				);
				$request_data = wp_json_encode( $contact_data );
				$response     = $this->post( $endpoint, $request_data, $headers );
			}
		} elseif ( 'Deals' === $object ) {
			$endpoint   = 'panel/deals/deal';
			$properties = array();

			if ( empty( $record_data['name']['value'] ) ) {
				$response = array(
					'code'    => 400,
					'message' => 'Bad Request',
					'data'    => 'Deal name field is empty.',
				);
			} else {

				foreach ( $record_data as $name => $value ) {
					if ( $value['is_custom'] == 'yes' ) {
						$properties[] = array(
							'name'  => $name,
							'value' => $value['value'],
						);
					} else {
						$sync_data[ $name ] = $value['value'];
					}
				}
				$sync_data['properties'] = $properties;
				$sync_data['tags']       = array(
					array( 'tag' => $form_name ),
				);
				$request_data            = wp_json_encode( $sync_data );
				$response                = $this->post( $endpoint, $request_data, $headers );
			}
		} elseif ( 'Companies' === $object ) {
			$endpoint = 'panel/companies/company';
			if ( empty( $record_data['name']['value'] ) ) {
				$response = array(
					'code'    => 400,
					'message' => 'Bad Request',
					'data'    => 'Company name field is empty.',
				);
			} else {
				foreach ( $record_data as $name => $value ) {
					$sync_data[] = array(
						'name'  => $name,
						'value' => $value['value'],
					);
				}
				$contact_data = array(
					'properties' => $sync_data,
					'tags'       => array(
						array( 'tag' => $form_name ),
					),
				);

				$request_data = wp_json_encode( $contact_data );
				$response     = $this->post( $endpoint, $request_data, $headers );
				if ( ! $this->is_success( $response ) ) {
					if ( 400 === $response['code'] ) {
						if ( false !== strpos( $response['data'], 'already exists' ) ) {
							$get_header   = $this->get_auth_header( 'get' );
							$params       = array();
							$company_name = '';
							foreach ( $record_data as $name => $value ) {
								if ( 'name' === $name ) {
									$company_name = $value['value'];
									break;
								}
							}
							$get_id   = 0;
							$get_data = $this->get( 'search?q=' . $company_name . '&type=Company', $params, $get_header );
							if ( $this->is_success( $get_data ) ) {
								$response_data = json_decode( $get_data['data'], ARRAY_A );
								$count         = $response_data[0]['count'];
								if ( 1 === $count ) {
									if ( ( $response_data[0]['name'] == $company_name ) || ( $response_data[0]['name_sort'] == strtolower( $company_name ) ) ) {
										$get_id = $response_data[0]['id'];
									}
								} else {
									foreach ( $response_data as $companies ) {
										if ( ( $companies['name'] == $company_name ) || ( $companies['name_sort'] == strtolower( $company_name ) ) ) {
											$get_id = $companies['id'];
										}
									}
								}
								if ( ! empty( $get_id ) ) {
									$contact_data = array(
										'id'         => $get_id,
										'properties' => $sync_data,
										'tags'       => array(
											array( 'tag' => $form_name ),
										),
									);
									$body         = wp_json_encode( $contact_data );
									$response     = $this->put( 'panel/companies/update-partial', $body, $headers );
									if ( ! empty( $response ) && is_array( $response ) ) {
										$response['company-updated'] = 'updated';
									}
								} else {
									$contact_data = array(
										'properties' => $sync_data,
										'tags'       => array(
											array( 'tag' => $form_name ),
										),
									);
									$request_data = wp_json_encode( $contact_data );
									$response     = $this->post( $endpoint, $request_data, $headers );
								}
							}
						}
					}
				}
			}
		} elseif ( 'Tasks' === $object ) {
			$endpoint = 'panel/tasks';
			if ( empty( $record_data['name']['value'] ) ) {
				$response = array(
					'code'    => 400,
					'message' => 'Bad Request',
					'data'    => 'Task name field is empty.',
				);
			} else {
				foreach ( $record_data as $name => $value ) {
					$sync_data[ $name ] = $value['value'];
				}
				$request_data = wp_json_encode( $sync_data );
				$response     = $this->post( $endpoint, $request_data, $headers );
			}
		}

		return $response;
	}

	/**
	 * Update an existing record.
	 *
	 * @param  string  $record_id   Record id to be updated.
	 * @param  string  $object      Object name.
	 * @param  array   $record_data Record data.
	 * @param  boolean $is_bulk     Is a bulk request.
	 * @param  array   $log_data    Data to create log.
	 * @return array                Response data.
	 */
	public function update_record( $record_id, $object, $record_data, $is_bulk, $log_data ) {

		if ( empty( $object ) || empty( $record_data ) ) {
			return;
		}
		$this->base_url = self::$api_url;
		$headers        = $this->get_auth_header();
		$sync_data      = array();
		$form_name      = '';
		if ( isset( $record_data['form_name'] ) ) {
			$form_name = $record_data['form_name'];
			unset( $record_data['form_name'] );
		}

		if ( 'Contacts' === $object ) {
			$endpoint = 'panel/subscribers/update-partial';
			foreach ( $record_data as $name => $value ) {
				$sync_data[] = array(
					'name'  => $name,
					'value' => $value['value'],
				);
			}
			$contact_data = array(
				'id'         => $record_id,
				'properties' => $sync_data,
				'tags'       => array(
					array( 'tag' => $form_name ),
				),
			);

			$request_data = wp_json_encode( $contact_data );
			$response     = $this->put( $endpoint, $request_data, $headers );
		} elseif ( 'Companies' === $object ) {
			$endpoint = 'panel/companies/update-partial';
			foreach ( $record_data as $name => $value ) {
				$sync_data[] = array(
					'name'  => $name,
					'value' => $value['value'],
				);
			}
			$contact_data = array(
				'id'         => $record_id,
				'properties' => $sync_data,
				'tags'       => array(
					array( 'tag' => $form_name ),
				),
			);
			$request_data = wp_json_encode( $contact_data );
			$response     = $this->put( $endpoint, $request_data, $headers );
		}

		return $response;
	}

	/**
	 * Get available object in crm.
	 *
	 * @param  boolean $force Fetch from api.
	 * @return array          Response data.
	 */
	public function get_crm_objects( $force = false ) {

		$objects = apply_filters(
			'mwb_' . Mwb_Gf_Integration_With_Engagebay::mwb_get_current_crm_property( 'slug' ) . '_gf_objects_list',
			array(
				'Contacts'  => 'Contacts',
				'Companies' => 'Companies',
				'Deals'     => 'Deals',
				'Tasks'     => 'Tasks',
			)
		);

		return $objects;
	}

	/**
	 * Validate connection
	 *
	 * @since 1.0.0
	 * @param string $auth_key api key.
	 * @return array
	 */
	public function validate_crm_connection( $auth_key ) {
		$headers  = array(
			'Authorization' => $auth_key,
			'Accept'        => 'application/json',
		);
		$endpoint = 'panel/users/profile/user-info';

		$this->base_url = self::$api_url;

		$params = array();

		$response = $this->get( $endpoint, $params, $headers );
		if ( is_wp_error( $response ) ) {
			$message = array(
				'status' => false,
				'code'   => 400,
				'msg'    => __(
					'An unexpected error occurred. Please try again.',
					'mwb-gf-integration-with-engagebay'
				),
			);
		} else {
			$response_body = $response['data'];
			$response_code = $response['code'];
			$response_body = json_decode( $response['data'], ARRAY_A );
			if ( isset( $response_body ) && 200 === $response_code ) {
				if ( ! empty( $response_body ) ) {
					$message = array(
						'status' => true,
						'code'   => 200,
						'data'   => $response_body,
					);
				}
			} else {
				$message = array(
					'status' => false,
					'code'   => $response_code,
					'data'   => $response_body,
				);
			}
		}
		return $message;
	}

	/**
	 * Get fields assosiated with an object.
	 *
	 * @param  string  $object Name of object.
	 * @param  boolean $force  Fetch from api.
	 * @return array           Response data.
	 */
	public function get_object_fields( $object, $force = false ) {

		$data = get_transient( 'mwb_engagebay_gf_' . $object . '_fields' );
		if ( ! $force && false !== ( $data ) ) {
			return $data;
		}

		$object_fields = array();
		if ( 'Contacts' === $object ) {
			$object_fields = array(
				'email'     => array(
					'field_name'    => 'email',
					'field_label'   => 'Email',
					'field_type'    => 'TEXT',
					'is_required'   => true,
					'primary_field' => 'email',
				),
				'name'      => array(
					'field_name'  => 'name',
					'field_label' => 'First Name',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
				'last_name' => array(
					'field_name'  => 'last_name',
					'field_label' => 'Last Name',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
				'phone'     => array(
					'field_name'  => 'phone',
					'field_label' => 'Phone Number',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
				'role'      => array(
					'field_name'  => 'role',
					'field_label' => 'Role',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
				'website'   => array(
					'field_name'  => 'website',
					'field_label' => 'Website',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
			);
			set_transient( 'mwb_engagebay_gf_' . $object . '_fields', $object_fields );
		} elseif ( 'Deals' === $object ) {

			$object_fields = array(
				'name'               => array(
					'field_name'  => 'name',
					'field_label' => 'Deal Name',
					'field_type'  => 'TEXT',
					'is_required' => true,
				),
				'unique_id'          => array(
					'field_name'  => 'unique_id',
					'field_label' => 'Deal ID',
					'field_type'  => 'NUMBER',
					'is_required' => false,
				),
				'description'        => array(
					'field_name'  => 'description',
					'field_label' => 'Description',
					'field_type'  => 'TEXTAREA',
					'is_required' => false,
				),
				'milestoneLabelName' => array(
					'field_name'  => 'milestoneLabelName',
					'field_label' => 'Milestone',
					'field_type'  => 'LIST',
					'is_required' => true,
					'field_data'  => 'New, Prospect, Proposal, Won, Lost',
				),
				'amount'             => array(
					'field_name'  => 'amount',
					'field_label' => 'Amount',
					'field_type'  => 'NUMBER',
					'is_required' => false,
				),
				'closed_date'        => array(
					'field_name'  => 'closed_date',
					'field_label' => 'Close Date',
					'field_type'  => 'DATE',
					'is_required' => false,
				),
			);
			set_transient( 'mwb_engagebay_gf_' . $object . '_fields', $object_fields );
		} elseif ( 'Companies' === $object ) {
			$object_fields = array(
				'name'  => array(
					'field_name'  => 'name',
					'field_label' => 'Company Name',
					'field_type'  => 'TEXT',
					'is_required' => true,
				),
				'url'   => array(
					'field_name'  => 'url',
					'field_label' => 'Company Domain (URL)',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
				'email' => array(
					'field_name'  => 'email',
					'field_label' => 'Company Email',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
				'phone' => array(
					'field_name'  => 'phone',
					'field_label' => 'Phone Number',
					'field_type'  => 'TEXT',
					'is_required' => false,
				),
			);
			set_transient( 'mwb_engagebay_gf_' . $object . '_fields', $object_fields );
		} elseif ( 'Tasks' === $object ) {
			$object_fields = array(
				'name'           => array(
					'field_name'  => 'name',
					'field_label' => 'Name',
					'field_type'  => 'TEXT',
					'is_required' => true,
				),
				'description'    => array(
					'field_name'  => 'description',
					'field_label' => 'Details',
					'field_type'  => 'TEXTAREA',
					'is_required' => false,
				),
				'type'           => array(
					'field_name'  => 'type',
					'field_label' => 'Type',
					'field_type'  => 'LIST',
					'is_required' => false,
					'field_data'  => 'TODO, EMAIL, CALL',
				),
				'closed_date'    => array(
					'field_name'  => 'closed_date',
					'field_label' => 'Due Date',
					'field_type'  => 'DATE',
					'is_required' => false,
				),
				'task_milestone' => array(
					'field_name'  => 'task_milestone',
					'field_label' => 'Status',
					'field_type'  => 'LIST',
					'is_required' => false,
					'field_data'  => 'not_started, in_progress, waiting, completed, deferred',
				),
				'task_priority'   => array(
					'field_name'  => 'task_priority',
					'field_label' => 'Priority',
					'field_type'  => 'LIST',
					'is_required' => false,
					'field_data'  => 'HIGH, MEDIUM, LOW',
				),
			);
			set_transient( 'mwb_engagebay_gf_' . $object . '_fields', $object_fields );
		}
		return $object_fields;

	}

	/**
	 * Check for mandatory fields and add an index to it also retricts phone fields.
	 *
	 * @param    array $fields  An array of fields data.
	 * @since    1.0.0
	 * @return   array
	 */
	public function maybe_add_mandatory_fields( $fields = array() ) {
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return;
		}

		$fields_arr = array();

		foreach ( $fields as $key => $field ) {
			if ( ( isset( $field['createable'] ) && true == $field['createable'] ) || 'Id' == $field['name'] || ( isset( $field['custom'] ) && true == $field['custom'] ) ) { // phpcs:ignore

				$mandatory = '';
				if ( ! empty( $field['nameField'] ) || ( ! empty( $field['createable'] ) && empty( $field['nillable'] ) && empty( $field['defaultedOnCreate'] ) ) ) {
					$mandatory = true;
				}

				$field['mandatory'] = $mandatory;
				$fields_arr[]       = $field;
			}
		}

		return $fields_arr;

	}

	/**
	 * Returns custom fields for Engagebay object.
	 *
	 * @since 1.0.0
	 * @param array $fields custom fields.
	 * @param array $object_fields object fields.
	 * @return array
	 */
	public function get_custom_fields( $fields, $object_fields ) {
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$field_name                   = $field['field_name'];
				$object_fields[ $field_name ] = array(
					'field_name'   => $field_name,
					'field_label'  => $field['field_label'],
					'field_type'   => $field['field_type'],
					'is_required'  => false,
					'custom_field' => 'yes',
				);
				if ( 'LIST' === $field['field_type'] || 'CHECKBOX' === $field['field_type'] || 'MULTICHECKBOX' === $field['field_type'] || 'FILE' === $field['field_type'] || 'TEXTAREA' === $field['field_type'] ) {
					if ( ! empty( $field['field_data'] ) ) {
						$field_data = $field['field_data'];
					} else {
						$field_data = '';
					}
					$object_fields[ $field_name ]['field_data'] = $field_data;
				}
			}
		}
		return $object_fields;
	}

	/**
	 * Get Owner from all users for quickbooks request.
	 *
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function get_owner_account() {
		$_account = '';
		$auth_key = get_option( 'mwb-' . Mwb_Gf_Integration_With_Engagebay::mwb_get_current_crm_property( 'slug' ) . '-gf-api-key', '' );
		$headers  = array(
			'Authorization' => $auth_key,
			'Accept'        => 'application/json',
		);
		$endpoint = 'panel/users/profile/user-info';

		$this->base_url = self::$api_url;

		$params = array();

		$response = $this->get( $endpoint, $params, $headers );

		if ( 200 === $response['code'] ) {
			$result = ! empty( $response['data'] ) ? json_decode( $response['data'], ARRAY_A ) : array();
			foreach ( $result as $key => $account ) {
				if ( 'name' === $key ) {
					$_account = $account;
					break;
				}
			}
		}

		update_option( 'mwb-' . Mwb_Gf_Integration_With_Engagebay::mwb_get_current_crm_property( 'slug' ) . '-gf-owner-account', $_account );
		return $_account;
	}

	// End of class.
}
