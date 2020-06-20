<?php
/**
 * Simple Invite Codes
 *
 * @package trepmal/simple-invite-codes
 */

class Simple_Invite_Codes extends WP_CLI_Command {

	/**
	 * Generate Invite Codes
	 *
	 * ## OPTIONS
	 *
	 * [--count=<count>]
	 * : How many codes to generate. Default 5
	 *
	 * [--chars=<chars>]
	 * : Allowed characters in codes. Default abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789
	 * Alphanumeric set, excluding commonly mixed up characters: ilo10
	 *
	 * [--length=<length>]
	 * : Invite code length. Default 6
	 *
	 * ## EXAMPLES
	 *
	 *     wp sic generate --count=10
	 *
	 *     # chars aren't filtered, so you can make, for example, 7s appear more often
	 *     wp sic generate --chars="abcdef77777777777777777777777777" --length=10 --count=12
	 */
	function generate( $args, $assoc_args ) {

		$count = absint( WP_CLI\Utils\get_flag_value( $assoc_args, 'count', 5 ) );
		$chars = WP_CLI\Utils\get_flag_value( $assoc_args, 'chars', 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789' );
		$length = absint( WP_CLI\Utils\get_flag_value( $assoc_args, 'length', 6 ) );

		$skips = 0;
		for ( $i=1; $i<=$count; $i++ ) {
			$invite_code = $this->generate_code( $chars, $length );

			$is_valid_code = get_page_by_title( $invite_code, OBJECT, 'invite_codes' );

			if ( ! is_null( $is_valid_code ) ) {
				$skips++;

				if ( $skips >= 50 ) {
					WP_CLI::error( "Oops. A lot of duplicates are being created, which may mean the character set or length are not broad enough to create enough unique codes. Please try again with different values." );
				}

				continue;
			}

			$id = wp_insert_post( array(
				'post_type'   => 'invite_codes',
				'post_title'  => $invite_code,
				'post_status' => 'publish',
			) );
			WP_CLI::line( WP_CLI::colorize(
				sprintf( "Created code %%g%s%%n (ID: %d)", $invite_code, $id )
			) );
		}
	}

	/**
	 * Generate a code of given length from a given set of characters
	 *
	 * @param (string) $chars Undelimited character set.
	 * @param (int) $length Desired ouptut length.
	 * @return (string) Generated code.
	 */
	private function generate_code( $chars, $length ) {
		$chars = str_split( $chars, 1 );
		shuffle( $chars );
		shuffle( $chars );
		return substr( implode( '', $chars ), 0, $length );
	}

}
