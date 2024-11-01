<?php
/** Requiere the JWT token verifier library. */
use Firebase\Auth\Token\Verifier;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://bengal-studio.com/
 * @since      1.0.0
 *
 * @package    Bengal_Studio_Verify_Id_Tokens
 * @subpackage Bengal_Studio_Verify_Id_Tokens/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bengal_Studio_Verify_Id_Tokens
 * @subpackage Bengal_Studio_Verify_Id_Tokens/public
 * @author     Mithun Biswas <bhoot.biswas@gmail.com>
 */
class Bengal_Studio_Verify_Id_Tokens_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The namespace to add to the api calls.
	 * @var [type]
	 */
	private $namespace;

	/**
	 * Store errors to display if the token is wrong
	 * @var [type]
	 */
	private $verify_id_tokens_error = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->namespace   = $this->plugin_name . '/v' . intval( $this->version );

	}

	/**
	 * Add the endpoints to the API
	 */
	public function add_api_routes() {
		register_rest_route(
			$this->namespace,
			'token/validate',
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'validate_token' ],
			]
		);
	}

	/**
	 * Add CORs suppot to the request.
	 */
	public function add_cors() {
		$enable_cors = defined( 'BENGAL_STUDIO_VERIFY_ID_TOKENS_ENABLE_CORS' ) ? BENGAL_STUDIO_VERIFY_ID_TOKENS_ENABLE_CORS : false;
		if ( $enable_cors && ( 'OPTIONS' == $_SERVER['REQUEST_METHOD'] ) ) {
			$origin = get_http_origin();
			if ( $origin ) {
				// Requests from file:// and data: URLs send "Origin: null"
				if ( 'null' !== $origin ) {
					$origin = esc_url_raw( $origin );
				}
			}

			/**
			 * [header description]
			 * @var [type]
			 */
			$headers = apply_filters( 'bengal_studio_verify_id_tokens_cors_allow_headers', 'Access-Control-Allow-Headers, Content-Type, Authorization' );
			header( sprintf( 'Access-Control-Allow-Headers: %s', $headers ) );
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Credentials: true' );

			/**
			 * exit program normally
			 * @var [type]
			 */
			exit();
		}
	}

	/**
	 * This is our Middleware to try to authenticate the user according to the
	 * token send.
	 * @param  [type] $user [description]
	 * @return [type]       [description]
	 */
	public function determine_current_user( $user ) {
		/**
		 * This hook only should run on the REST API requests to determine
		 * if the user in the Token (if any) is valid, for any other
		 * normal call ex. wp-admin/.* return the user.
		 * @var [type]
		 */
		$rest_api_slug = rest_get_url_prefix();
		$valid_api_uri = strpos( $_SERVER['REQUEST_URI'], $rest_api_slug );
		if ( ! $valid_api_uri ) {
			return $user;
		}

		/**
		 * If the request URI is for authorize the user don't do anything,
		 * this avoid double calls to the validate_token function.
		 * @var [type]
		 */
		$authorize_uri = strpos( $_SERVER['REQUEST_URI'], 'token/validate' );
		if ( $authorize_uri > 0 ) {
			return $user;
		}

		/**
		 * [$token description]
		 * @var [type]
		 */
		$token = $this->validate_token();
		if ( is_wp_error( $token ) ) {
			if ( $token->get_error_code() != 'verify_id_tokens_auth_header_not_found' ) {
				/** If there is a error, store it to show it after see rest_pre_dispatch */
				$this->verify_id_tokens_error = $token;
			}

			/**
			 * [return description]
			 * @var [type]
			 */
			return $user;
		}

		/** Everything is ok, get and return the user ID stored in the database */
		$user_obj = get_user_by( 'email', $token['email'][0] );
		if ( $user_obj ) {
			return $user_obj->ID;
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $user;
	}

	/**
	 * Main validation function, this function try to get the Autentication
	 * headers and decoded.
	 * @return [type] [description]
	 */
	public function validate_token() {
		/**
		 * Looking for the HTTP_AUTHORIZATION header, if not present just
		 * return the user.
		 * @var [type]
		 */

		$auth = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

		/* Double check for different auth header string (server dependent) */
		if ( ! $auth ) {
			$auth = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( ! $auth ) {
			return new WP_Error(
				'verify_id_tokens_auth_header_not_found',
				__( 'Authorization header not found.', 'verify-id-tokens' ),
				[
					'status' => 403,
				]
			);
		}

		/**
		 * The HTTP_AUTHORIZATION is present verify the format
		 * if the format is wrong return the user.
		 * @var [type]
		 */
		list($token) = sscanf( $auth, 'Bearer %s' );
		if ( ! $token ) {
			return new WP_Error(
				'verify_id_tokens_auth_header_malformed',
				__( 'Authorization header malformed.', 'verify-id-tokens' ),
				[
					'status' => 403,
				]
			);
		}

		/** Get the Secret Key */
		$secret_key = defined( 'BENGAL_STUDIO_VERIFY_ID_TOKENS_FIREBASE_PROJECT_ID' ) ? BENGAL_STUDIO_VERIFY_ID_TOKENS_FIREBASE_PROJECT_ID : false;
		if ( ! $secret_key ) {
			return new WP_Error(
				'verify_id_tokens_not_configurated',
				__( 'Verify Id Tokens is not configurated properly, please contact the admin.', 'verify-id-tokens' ),
				[
					'status' => 403,
				]
			);
		}

		/** Try to decode the token */
		$verifier = new Verifier( $secret_key );
		try {
			$verified_id_token = $verifier->verifyIdToken( $token );

			/** Everything looks good, send back the success */
			return [
				'code'       => 'verify_id_tokens_token_valid',
				'iss'        => $verified_id_token->getClaim( 'iss' ),
				'user_login' => $verified_id_token->getClaim( 'user_id' ),
				'email'      => $verified_id_token->getClaim( 'firebase' )->identities->email,
				'data'       => [
					'status' => 200,
				],
			];
		} catch ( \Firebase\Auth\Token\Exception\ExpiredToken $e ) {
			/** Expired token, send back the error */
			return new WP_Error(
				'verify_id_tokens_token_expired',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		} catch ( \Firebase\Auth\Token\Exception\IssuedInTheFuture $e ) {
			/** Issued in future, send back the error */
			return new WP_Error(
				'verify_id_tokens_token_issued_in_the_future',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		} catch ( \Firebase\Auth\Token\Exception\InvalidToken $e ) {
			/** Something is wrong trying to decode the token, send back the error */
			return new WP_Error(
				'verify_id_tokens_token_invalid',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		}
	}

	/**
	 * Filter to hook the rest_pre_dispatch, if the is an error in the request
	 * send it, if there is no error just continue with the current request.
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function rest_pre_dispatch( $request ) {
		/**
		 * [if description]
		 * @var [type]
		 */
		if ( is_wp_error( $this->verify_id_tokens_error ) ) {
			return $this->verify_id_tokens_error;
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $request;
	}
}
