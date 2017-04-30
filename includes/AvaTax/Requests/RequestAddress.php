<?php
/**
 * Define the RequestAddress class
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace RCP_Avatax\AvaTax\Requests;

defined( 'ABSPATH' ) or exit;

/**
 * The AvaTax API address request class.
 *
 * @since 1.0.0
 */
class RequestAddress extends Request {

	/**
	 * Validate an address.
	 *
	 * @since 1.0.0
	 * @param array $address The address details. @see `API::validate_address()` for formatting.
	 * @return object The validated and normalized address.
	 */
	public function validate_address( $address ) {
		$this->path = 'address/validate?' . http_build_query( $this->prepare_address( $address ), '', '&' );
	}

}
