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
 * Interkassa payment status class
 *
 * This class represents payment status. It contains additional data sent from
 * interkassa and automatically checks data signature. Note, that only status
 * url updates contain signature, so it is not recommended to rely on succes url
 * or fail url statuses to confirm user payment.
 *
 * @license MIT-style license
 * @package Interkassa
 * @author Anton Suprun <kpobococ@gmail.com>
 * @version 1.0.0
 */
class Interkassa_Status
{
    protected $_verified = false;

    protected $_timestamp;
    protected $_state;
    protected $_trans_id;
    protected $_currency;
    protected $_fees_payer;

    protected $_shop;
    protected $_payment;

    /**
     * Create payment status instance
     *
     * @param Interkassa_Shop $shop
     * @param array $source
     *
     * @see Interkassa_Status::__constructor()
     *
     * @return Interkassa_Status
     */
    public static function factory(Interkassa_Shop $shop, array $source)
    {
        return new Interkassa_Status($shop, $source);
    }

    /**
     * Constructor
     *
     * @param Interkassa_Shop $shop
     * @param array $source the data source to use, e.g. $_POST.
     *
     * @throws Interkassa_Exception if some data not received, received shop id
     *                              does not match current shop id or received
     *                              signature is invalid
     */
    public function __construct(Interkassa_Shop $shop, array $source)
    {
        $this->_shop = $shop;

        foreach (array(
                     'ik_co_id' => 'Shop id',
                     'ik_pm_no' => 'Payment id',
                     'ik_am' => 'Payment amount',
                     'ik_desc' => 'Payment description',
                     'ik_pw_via' => 'Payway Via',
                     'ik_sign' => 'Payment Signature',
                     'ik_cur' => 'Currency',
                     'ik_inv_prc' => 'Payment Time',
                     'ik_inv_st' => 'Payment State',
                     'ik_trn_id' => 'Transaction',
                     'ik_ps_price' => 'PaySystem Price',
                     'ik_co_rfn' => 'Checkout Refund'
                 ) as $field => $title)
            if (!isset($source[$field]))
                throw new Interkassa_Exception($title . ' not received');

        $received_id = strtoupper($source['ik_co_id']);
        $shop_id = strtoupper($shop->getId());

        if ($received_id !== $shop_id)
            throw new Interkassa_Exception('Received shop id does not match current shop id');

        if ($this->_checkSignature($source))
            $this->_verified = true;
        else
            throw new Interkassa_Exception('Signature does not match the data');

        $payment = $shop->createPayment(array(
            'id' => $source['ik_pm_no'],
            'amount' => $source['ik_am'],
            'description' => $source['ik_desc']
        ));

        if (!empty($source['ik_x_baggage']))
            $payment->setBaggage($source['ik_x_baggage']);

        $this->_payment = $payment;
        $this->_timestamp = $source['ik_inv_prc'];
        $this->_state = (string)$source['ik_inv_st'];
        $this->_trans_id = (string)$source['ik_trn_id'];
        $this->_currency = $source['ik_cur'];
        $this->_fees_payer = $source['ik_ps_price'] - $source['ik_co_rfn'];
    }

    /**
     * Get transaction time as a timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    /**
     * Get transaction time as a DateTime instance
     *
     * @see http://php.net/http://ua2.php.net/manual/en/class.datetime.php
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return new DateTime('@' . $this->getTimestamp());
    }

    /**
     * Get transaction state
     *
     * Returns {@link Interkassa::STATE_SUCCESS} or {@link Interkassa::STATE_FAIL}
     *
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Get transaction id
     *
     * This id is provided by interkassa
     *
     * @return string
     */
    public function getTransId()
    {
        return $this->_trans_id;
    }

    /**
     * Get currency exchange rate
     *
     * Returns the currency exchange rate defined in shop preferences at the
     * time of the transaction
     *
     * @return float
     */
    public function getCurrencyName()
    {
        return $this->_currency;
    }

    /**
     * Get transaction fees payer
     *
     * @return float
     */
    public function getFeesPayer()
    {
        return $this->_fees_payer;
    }

    /**
     * Get verification status
     *
     * Returns true if the status update signature was present and correctly
     * verified. Returns false if the status update had no signature present.
     *
     * Note, if the status update contained a signature but the data was not
     * correctly verified, the constructor throws an error.
     *
     * @return bool
     */
    public function getVerified()
    {
        return $this->_verified;
    }

    /**
     * Get payment instance
     *
     * @return Interkassa_Payment
     */
    public function getPayment()
    {
        return $this->_payment;
    }

    /**
     * Get shop instance
     *
     * @return Interkassa_Shop
     */
    public function getShop()
    {
        return $this->_shop;
    }

    /**
     * Check status data signature
     *
     * @param array $source the data source
     *
     * @return bool
     */
    final protected function _checkSignature($source)
    {
        $post = $source;
        unset($post['ik_sign']);
        ksort($post, SORT_STRING);
        array_push($post, $this->getShop()->getSecretKey());
        $signature = base64_encode(md5(implode(':', $post), true));
        return $source['ik_sign'] === $signature;
    }
}