<?php

use engine\system;
use engine\extension;
use engine\logger;
use engine\user;

class api_payment_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $type = system::getInstance()->get('type');
        switch($type) {
            case "webmoney":
                $this->viewWebmoney();
                break;
            case "interkassa":
                $this->viewInterkassa();
                break;
            case "robokassa":
                $this->viewRobokassa();
                break;
        }
    }

    private function viewRobokassa() {
        if(!extension::getInstance()->getConfig('balance_use_rk', 'user', extension::TYPE_COMPONENT, 'boolean'))
            exit("Robokassa API disabled");
        require_once(root . '/resource/payments/robokassa/robokassa.class.php');
        $init_rk = new Robokassa(
            extension::getInstance()->getConfig('balance_rk_id', 'user', extension::TYPE_COMPONENT, 'str'),
            extension::getInstance()->getConfig('balance_rk_key_1', 'user', extension::TYPE_COMPONENT, 'str'),
            extension::getInstance()->getConfig('balance_rk_key_2', 'user', extension::TYPE_COMPONENT, 'str'),
            extension::getInstance()->getConfig('balance_rk_test', 'user', extension::TYPE_COMPONENT, 'boolean')
        );

        $init_rk->OutSum = $amount = (float)system::getInstance()->post('OutSum');
        $init_rk->InvId = $transid = (int)system::getInstance()->post('InvId');

        $user_id = (int)system::getInstance()->post('shp_userid');

        $init_rk->addCustomValues(array(
            'shp_userid' => $user_id,
        ));

        if(!$init_rk->checkHash($_POST['SignatureValue'])) {
            exit("Hash sum was wrong!");
        }

        $mul = $params['config']['balance_rk_mul'] = extension::getInstance()->getConfig('balance_rk_mul', 'user', extension::TYPE_COMPONENT, 'float');
        $amount *= $mul;

        user::getInstance()->addBalance($user_id, $amount);
        $payparam = array(
            'amount' => $amount,
            'sys_trans_id' => $transid
        );
        user::getInstance()->putLog($user_id, 'balance.rkadd', $payparam, 'Recharge balance via robokassa');
        echo "Success payment";
    }

    private function viewInterkassa() {
        if(!extension::getInstance()->getConfig('balance_use_ik', 'user', extension::TYPE_COMPONENT, 'boolean'))
            exit("Interkassa API disabled");

        $post_kasa_id = system::getInstance()->post('ik_co_id');
        if($post_kasa_id != extension::getInstance()->getConfig('balance_ik_id', 'user', extension::TYPE_COMPONENT, 'str'))
            exit("undefined id");


        require_once(root . '/resource/payments/interkassa2/interkassa.php');
        Interkassa::register();
        $shop = Interkassa_Shop::factory(array(
            'id' => extension::getInstance()->getConfig('balance_ik_id', 'user', extension::TYPE_COMPONENT, 'str'),
            'secret_key' => extension::getInstance()->getConfig('balance_ik_key', 'user', extension::TYPE_COMPONENT, 'str')
        ));

        try {
            $status = $shop->receiveStatus(system::getInstance()->post(null)); // POST is used by default
        } catch (Interkassa_Exception $e) {
            logger::getInstance()->log(logger::LEVEL_WARN, "Interkassa payment check signature fail. From ip: ".system::getInstance()->getRealIp().", post_data : ".json_encode(system::getInstance()->post(null)));
            header('HTTP/1.0 400 Bad Request');
            exit;
        }

        $payment = $status->getPayment();
        $mul_c = extension::getInstance()->getConfig('balance_ik_mul', 'user', extension::TYPE_COMPONENT, 'float');

        $user_id = system::getInstance()->toInt($payment->getId());
        $amount = (float)$payment->getAmount();
        $amount *= $mul_c;

        user::getInstance()->addBalance($user_id, $amount);
        $payparam = array(
            'currency' => $payment->getCurrency(),
            'amount' => $amount,
            'sys_invs_id' => system::getInstance()->post('ik_inv_id'),
            'sys_trans_id' => system::getInstance()->post('ik_trn_id'),
            'date' => system::getInstance()->post('ik_inv_prc')
        );
        user::getInstance()->putLog($user_id, 'balance.ikadd', $payparam, 'Recharge balance via interkassa');
    }

    private function viewWebmoney() {
        if(!extension::getInstance()->getConfig('balance_use_webmoney', 'user', extension::TYPE_COMPONENT, 'boolean'))
            exit("Webmoney API disabled");

        $wm_cfg_purse = extension::getInstance()->getConfig('balance_wm_purse', 'user', extension::TYPE_COMPONENT, 'str');
        $wm_cfg_mul = extension::getInstance()->getConfig('balance_wm_mul', 'user', extension::TYPE_COMPONENT, 'float');
        $wm_cfg_secret = extension::getInstance()->getConfig('balance_wm_secretkey', 'user', extension::TYPE_COMPONENT, 'str');

        $real_ip = system::getInstance()->getRealIp();
        $ip_array_routes = system::getInstance()->altexplode('.', $real_ip);
        array_pop($ip_array_routes);
        $ip_masc = system::getInstance()->altimplode('.', $ip_array_routes);
        $wm_ips = array( // actual on september 2014.
            '212.118.48',
            '212.158.173',
            '91.200.28',
            '91.227.52'
        );

        if(!in_array($ip_masc, $wm_ips)) {
            logger::getInstance()->log(logger::LEVEL_WARN, 'Call to Webmoney REST_API from wrong ip: '.$real_ip.' masc: '.$ip_masc);
            return null;
        }

        $pre_request = system::getInstance()->post('LMI_PREREQUEST');

        $wm_seller_purse = system::getInstance()->post('LMI_PAYEE_PURSE'); // seller purse (must be our)
        $wm_payment_amount = system::getInstance()->post('LMI_PAYMENT_AMOUNT'); // payment price amount
        $wm_item_id = (int)system::getInstance()->post('LMI_PAYMENT_NO'); // user id
        $wm_test_mode = system::getInstance()->post('LMI_MODE'); // is test?
        $wm_paym_id = system::getInstance()->post('LMI_SYS_INVS_NO'); // webmoney payment id
        $wm_trans_id = system::getInstance()->post('LMI_SYS_TRANS_NO'); // webmoney transaction id
        $wm_trans_date = system::getInstance()->post('LMI_SYS_TRANS_DATE'); // date in strange format
        $wm_hash_trans = system::getInstance()->post('LMI_HASH'); // hash sum, can be null before 200OK response is checked
        $wm_buyer_wmpurse = system::getInstance()->post('LMI_PAYER_PURSE'); // client wm purse
        $wm_buyer_wmid = system::getInstance()->post('LMI_PAYER_WM'); // client WMID

        if($pre_request == 1) { // its a pre-request, validation before pay
            if($wm_seller_purse != $wm_cfg_purse)
                exit("Seller purse is invalid");
            if(!user::getInstance()->exists($wm_item_id))
                exit("User id: ".$wm_item_id." not exist");
            echo "YES";
        } else { // its a result request after payment
            if($wm_hash_trans == null) // didnt know why, but webmoney make 2 requests if PREREQUEST is disabled.
                exit("Hash sum is null");
            $totaldata = $wm_seller_purse . $wm_payment_amount . $wm_item_id . $wm_test_mode . $wm_paym_id . $wm_trans_id . $wm_trans_date . $wm_cfg_secret . $wm_buyer_wmpurse . $wm_buyer_wmid;

            $calchash = strtoupper(hash('sha256', $totaldata));

            if($calchash != $wm_hash_trans || $wm_seller_purse != $wm_cfg_purse) {
                logger::getInstance()->log(logger::LEVEL_NOTIFY, 'Wrong balance recharge webmoney from ip: '.$real_ip.'. Hash gen: '.$calchash . ' get: '.$wm_hash_trans . '. All data json: '.json_encode(system::getInstance()->post()));
                return null;
            }

            $money_to_balance = $wm_payment_amount * $wm_cfg_mul;

            if($money_to_balance <= 0)
                return null;

            user::getInstance()->addBalance($wm_item_id, $money_to_balance);
            $payparam = array(
                'from_wm_purse' => $wm_buyer_wmpurse,
                'from_wm_id' => $wm_buyer_wmid,
                'date' => $wm_trans_date,
                'sys_invs_id' => $wm_paym_id,
                'sys_trans_id' => $wm_trans_id,
                'amount' => $money_to_balance
            );
            user::getInstance()->putLog($wm_item_id, 'balance.wmadd', $payparam, 'Recharge balance via webmoney');
        }
    }
}