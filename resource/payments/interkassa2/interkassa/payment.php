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
 * Interkassa payment class
 *
 * This class represents a payment. It can be used to acquire an array of all
 * the form field values with the correct field names (see
 * {@link Interkassa_Payment::getFormValues() getFormValues() method}.
 *
 * @license MIT-style license
 * @package Interkassa
 * @author Anton Suprun <kpobococ@gmail.com>
 * @version 1.0.0
 */
class Interkassa_Payment
{
    /**
     * Shop instance
     *
     * @var Interkassa_Shop
     */
    protected $_shop;

    /**
     * Payment id
     *
     * @var string
     */
    protected $_id;

    /**
     * Payment amount
     *
     * @var float
     */
    protected $_amount;

    /**
     * Payment description
     *
     * @var string
     */
    protected $_description;


    /**
     * Payment baggage field
     *
     * @var string
     */
    protected $_baggage = false;

    /**
     * Success url
     *
     * @var string
     */
    protected $_success_url = false;

    /**
     * Failure url
     *
     * @var string
     */
    protected $_fail_url = false;

    /**
     * Status url
     *
     * @var string
     */
    protected $_status_url = false;

    /**
     * Success url method
     *
     * @var string
     */
    protected $_success_method = Interkassa::METHOD_POST;

    /**
     * Failure url method
     *
     * @var string
     */
    protected $_fail_method = Interkassa::METHOD_POST;

    /**
     * Status url method
     *
     * @var string
     */
    protected $_status_method = Interkassa::METHOD_POST;

    /**
     * Payment form action
     *
     * @var string
     */
    protected $_form_action = 'https://sci.interkassa.com/';

    /**
     * Users Locale
     * @var string
     */
    protected $_locale = false;

    /**
     * Users currency name
     * @var string
     */
    protected $_currency = false;

    /**
     * Create payment instance
     *
     * @param Interkassa_Shop $shop
     * @param array $options
     * @internal param \Interkassa_Shop $interkassa
     *
     * @see Interkassa_Payment::__construct()
     *
     * @return Interkassa_Payment
     */
    public static function factory(Interkassa_Shop $shop, array $options)
    {
        return new Interkassa_Payment($shop, $options);
    }

    /**
     * Constructor
     *
     * Accepted payment options are:
     * - id - payment id
     * - amount - payment amount
     * - description - payment description
     * - baggage - payment baggage field. Optional
     * - success_url - url to redirect the user in case of success. Optional
     * - fail_url - url to redirect the user in case of failure. Optional
     * - status_url - url to send payment status. Optional
     * - success_method - method to use when redirecting to success_url. Optional
     * - fail_method - method to use when redirecting to fail_url. Optional
     * - status_method - method to use when sending payment status. Optional
     * - form_action - payment form action url. Optional
     *
     * @param Interkassa_Shop $shop
     * @param array $options an array of payment options
     *
     * @throws Interkassa_Exception if any required options are missing
     */
    public function __construct(Interkassa_Shop $shop, array $options)
    {
        $this->_shop = $shop;

        if (!isset($options['id']))
        {
            throw new Interkassa_Exception('Payment id is required');
        }

        if (!isset($options['amount']))
        {
            throw new Interkassa_Exception('Payment amount is required');
        }

        if (!isset($options['description']))
        {
            throw new Interkassa_Exception('Payment description is required');
        }

        $this->_id = (string)$options['id'];
        $this->_amount = (float)$options['amount'];
        $this->_description = (string)$options['description'];


        if (!empty($options['baggage']))
        {
            $this->setBaggage($options['baggage']);
        }

        if (!empty($options['success_url']))
        {
            $this->setSuccessUrl($options['success_url']);
        }

        if (!empty($options['success_method']))
        {
            $this->setSuccessMethod($options['success_method']);
        }

        if (!empty($options['fail_url']))
        {
            $this->setFailUrl($options['fail_url']);
        }

        if (!empty($options['fail_method']))
        {
            $this->setFailMethod($options['fail_method']);
        }

        if (!empty($options['status_url']))
        {
            $this->setStatusUrl($options['status_url']);
        }

        if (!empty($options['status_method']))
        {
            $this->setStatusMethod($options['status_method']);
        }

        if (!empty($options['form_action']))
        {
            $this->setFormAction($options['form_action']);
        }

        if (!empty($options['locale']))
        {
            $this->setLocale($options['locale']);
        }

        if (!empty($options['currency']))
        {
            $this->setCurrency($options['currency']);
        }
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get payment amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Get payment amount as string
     *
     * @param int $decimals number of decimal points
     *
     * @return string
     */
    public function getAmountAsString($decimals = 2)
    {
        return number_format($this->_amount, $decimals, '.', '');
    }

    /**
     * Get payment description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Get payment baggage field
     *
     * @return string
     */
    public function getBaggage()
    {
        return $this->_baggage;
    }

    /**
     * Set payment baggage field
     *
     * @param string $baggage
     *
     * @return Interkassa_Payment self
     */
    public function setBaggage($baggage)
    {
        if (!empty($baggage))
        {
            $this->_baggage = (string)$baggage;
        }

        return $this;
    }

    /**
     * Get success url
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->_success_url;
    }

    /**
     * Set success url
     *
     * @param string $url
     *
     * @return Interkassa_Payment self
     */
    public function setSuccessUrl($url)
    {
        if (!empty($url))
        {
            $this->_success_url = (string)$url;
        }

        return $this;
    }

    /**
     * Get success url method
     *
     * Returns {@link Interkassa::METHOD_POST}, {@link Interkassa::METHOD_GET}
     * or {@link Interkassa::METHOD_LINK}
     *
     * @return string
     */
    public function getSuccessMethod()
    {
        return $this->_success_method;
    }

    /**
     * Set success url method
     *
     * @param string $method
     *
     * @uses Interkassa::METHOD_POST
     * @uses Interkassa::METHOD_GET
     * @uses Interkassa::METHOD_LINK
     *
     * @return Interkassa_Payment self
     */
    public function setSuccessMethod($method)
    {
        if (empty($method))
        {
            return $this;
        }

        $methods = array(
            Interkassa::METHOD_POST,
            Interkassa::METHOD_GET,
            Interkassa::METHOD_LINK
        );

        if (in_array($method, $methods))
        {
            $this->_success_method = $method;
        }

        return $this;
    }

    /**
     * Get failure url
     *
     * @return string
     */
    public function getFailUrl()
    {
        return $this->_fail_url;
    }

    /**
     * Set failure url
     *
     * @param string $url
     *
     * @return Interkassa_Payment self
     */
    public function setFailUrl($url)
    {
        if (!empty($url))
        {
            $this->_fail_url = (string)$url;
        }

        return $this;
    }

    /**
     * Get failure url method
     *
     * Returns {@link Interkassa::METHOD_POST}, {@link Interkassa::METHOD_GET}
     * or {@link Interkassa::METHOD_LINK}
     *
     * @return string
     */
    public function getFailMethod()
    {
        return $this->_fail_method;
    }

    /**
     * Set failure url method
     *
     * @param string $method
     *
     * @uses Interkassa::METHOD_POST
     * @uses Interkassa::METHOD_GET
     * @uses Interkassa::METHOD_LINK
     *
     * @return Interkassa_Payment self
     */
    public function setFailMethod($method)
    {
        if (empty($method))
        {
            return $this;
        }

        $methods = array(
            Interkassa::METHOD_POST,
            Interkassa::METHOD_GET,
            Interkassa::METHOD_LINK
        );

        if (in_array($method, $methods))
        {
            $this->_fail_method = $method;
        }

        return $this;
    }

    /**
     * Get status url
     *
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->_status_url;
    }

    /**
     * Set status url
     *
     * @param string $url
     *
     * @return Interkassa_Payment self
     */
    public function setStatusUrl($url)
    {
        if (!empty($url))
        {
            $this->_status_url = (string)$url;
        }

        return $this;
    }

    /**
     * Get status url method
     *
     * Returns {@link Interkassa::METHOD_POST}, {@link Interkassa::METHOD_GET}
     * or {@link Interkassa::METHOD_OFF}
     *
     * @return string
     */
    public function getStatusMethod()
    {
        return $this->_status_method;
    }

    /**
     * Set status url method
     *
     * @param string $method
     *
     * @uses Interkassa::METHOD_POST
     * @uses Interkassa::METHOD_GET
     * @uses Interkassa::METHOD_OFF
     *
     * @return Interkassa_Payment self
     */
    public function setStatusMethod($method)
    {
        if (empty($method))
            return $this;

        $methods = array(
            Interkassa::METHOD_POST,
            Interkassa::METHOD_GET,
            Interkassa::METHOD_OFF
        );

        if (in_array($method, $methods))
        {
            $this->_status_method = $method;
        }

        return $this;
    }

    /**
     * Get payment form field values
     *
     * Returns an associative array of the payment form field names as array
     * keys, and their respective values as array values
     *
     * @uses Interkassa_Payment::getAmountAsString() to form payment amount value
     *
     * @return array
     */
    public function getFormValues()
    {
        $fields = array(
            'ik_co_id' => $this->getShop()->getId(),
            'ik_am' => $this->getAmountAsString(),
            'ik_pm_no' => $this->getId(),
            'ik_desc' => $this->getDescription()
        );

        $success_url = $this->getSuccessUrl();
        $fail_url = $this->getFailUrl();
        $status_url = $this->getStatusUrl();
        $locale = $this->getLocale();
        $curr = $this->getCurrency();

        if ($locale)
            $fields['ik_loc'] = $locale;

        $fields['ik_x_baggage'] = (string)$this->getBaggage();

        if ($success_url)
        {
            $fields['ik_suc_u'] = (string)$success_url;
            $fields['ik_suc_m'] = (string)$this->getSuccessMethod();
        }

        if ($fail_url)
        {
            $fields['ik_fal_u'] = (string)$fail_url;
            $fields['ik_fal_m'] = (string)$this->getFailMethod();
        }

        if ($status_url)
        {
            $fields['ik_ia_u'] = (string)$status_url;
            $fields['ik_ia_m'] = (string)$this->getStatusMethod();
        }

        if ($curr)
            $fields['ik_cur'] = (string)$curr;

        return $fields;
    }

    /**
     * Get payment form action
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->_form_action;
    }

    /**
     * Set payment form action
     *
     * @param string $url
     *
     * @return Interkassa_Payment self
     */
    public function setFormAction($url)
    {
        if ($url)
            $this->_form_action = (string)$url;

        return $this;
    }

    /**
     * Get shop instance for this payment
     *
     * @return Interkassa_Shop
     */
    public function getShop()
    {
        return $this->_shop;
    }

    /**
     * Set users interface locale
     * @param $locale
     * @return Interkassa_Payment self
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * Get users interface locale
     * return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * ex. USD; EUR; UAH
     * @param $currency
     * @return Interkassa_Payment self
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }
}