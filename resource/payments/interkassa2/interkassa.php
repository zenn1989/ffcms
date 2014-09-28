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
 * @author Odarchenko N.D. <odarchenko.n.d@gmail.com>
 * @version 1.0.0
 */

/**
 * Interkassa base class
 *
 * This class is used to initialize the library and also contains several
 * constants.
 *
 * @license MIT-style license
 * @package Interkassa
 * @author Anton Suprun <kpobococ@gmail.com>
 * @version 1.0.0
 */
class Interkassa
{
    /**#@+
     * URL method constant
     *
     * @see Interkassa_Payment::setSuccessMethod()
     * @see Interkassa_Payment::setFailMethod()
     * @see Interkassa_Payment::setStatusMethod()
     */
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_LINK = 'LINK';
    const METHOD_OFF = 'OFF';
    /**#@-*/

    /**#@+
     * State constant
     *
     * @see Interkassa_Status::getState()
     */
    const STATE_SUCCESS = 'success';
    const STATE_FAIL = 'fail';
    /**#@-*/

    /**#@+
     * Fees payer constant
     *
     * @see Interkassa_Status::getFeesPayer()
     */
    const FEES_PAYER_SHOP = 0;
    const FEES_PAYER_BUYER = 1;
    const FEES_PAYER_EQUAL = 2;

    /**#@-*/

    /**
     * Registers library autoloader as an SPL autoloader.
     */
    public static function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array('Interkassa', 'autoload'));
    }

    /**
     * Autoload method.
     *
     * Handles autoloading of classes.
     *
     * @param string $class class name
     *
     * @return boolean true if class has been loaded, false otherwise
     */
    public static function autoload($class)
    {
        if (class_exists($class, false) || interface_exists($class, false))
            return true;

        if (strpos($class, 'Interkassa_') !== 0)
            return false;

        $dir = dirname(__FILE__);
        $bits = explode('_', $class);

        if (!function_exists('lcfirst'))
            foreach ($bits as $i => $bit)
                $bits[$i] = strtolower($bit[0]) . substr($bit, 1);
        else
            $bits = array_map('lcfirst', $bits);

        $file = $dir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $bits) . '.php';

        if (file_exists($file))
        {
            require $file;
            return class_exists($class, false) || interface_exists($class, false);
        }

        return false;
    }
}