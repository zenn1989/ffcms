<?php
/**
 * Interkassa API for PHP
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT-style license
 * @package Interkassa
 * @author Anton Suprun <kpobococ@gmail.com>
 * @version 1.0.0
 */

/**
 * Interkassa shop class
 *
 * This class represents a shop. It holds shop id and secret key. It also has
 * several convenience methods to work with the API.
 *
 * @license MIT-style license
 * @package Interkassa
 * @author Anton Suprun <kpobococ@gmail.com>
 * @version 1.0.0
 */
class Interkassa_Shop
{
    /**
     * Shop id
     *
     * @var string
     */
    protected $_id;

    /**
     * Secret key
     *
     * @var string
     */
    protected $_secret_key;

    /**
     * Factory method
     *
     * @param array $options
     *
     * @see Interkassa_Shop::__construct()
     *
     * @return Interkassa_Shop
     */
    public static function factory(array $options)
    {
        return new Interkassa_Shop($options);
    }

    /**
     * Constructor
     *
     * Accepted configuration options are:
     * - id - your interkassa shop id;
     * - secret_key - your interkassa secret key. Used to parse status requests;
     *
     * @param array $options shop configuration. See above
     *
     * @throws Interkassa_Exception if any option values are missing
     */
    public function __construct(array $options)
    {
        if (!isset($options['id'])) {
            throw new Interkassa_Exception('Shop id is required');
        }

        if (!isset($options['secret_key'])) {
            throw new Interkassa_Exception('Secret key is required');
        }

        $this->_id         = $options['id'];
        $this->_secret_key = $options['secret_key'];
    }

    /**
     * Create shop payment instance
     *
     * @param array $data payment data
     *
     * @see Interkassa_Payment::__construct()
     *
     * @return Interkassa_Payment
     */
    public function createPayment(array $data)
    {
        return Interkassa_Payment::factory($this, $data);
    }

    /**
     * Receive shop status data
     *
     * @param array $source source array to use. Defaults to $_REQUEST
     *
     * @return Interkassa_Status
     *
     * @see Interkassa_Status::__construct()
     *
     * @throws Interkassa_Exception if received shop id does not match current shop id
     */
    public function receiveStatus(array $source = null)
    {
        if ($source == null) {
            $source = $_REQUEST;
        }

        return Interkassa_Status::factory($this, $source);
    }

    /**
     * Get shop id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->_secret_key;
    }
}