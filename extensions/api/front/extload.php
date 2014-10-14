<?php

use engine\system;
use engine\database;
use engine\property;
use engine\user;

class api_extload_front extends \engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $fhash = system::getInstance()->get('file');
        $fid = (int)system::getInstance()->get('id');
        $uid = user::getInstance()->get('id');

        if($fid < 1 || system::getInstance()->length($fhash) < 1)
            exit("nil");

        $stmt = database::getInstance()->con()->prepare("SELECT download,versioncontrol,price FROM ".property::getInstance()->get('db_prefix')."_com_extension_item WHERE id = ?");
        $stmt->bindParam(1, $fid, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            exit('ext not found');

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $last_version = $result['download'];

        $output_file_data = null;
        $output_file_name = null;
        $output_file_size = null;

        $paid_time = 0;
        if($result['price'] > 0) {
            if($uid < 1)
                exit("Paid extension required auth");
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_extension_buy WHERE owner = ? AND product_id = ?");
            $stmt->bindParam(1, $uid, \PDO::PARAM_INT);
            $stmt->bindParam(2, $fid, \PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() == 1) {
                $paidres = $stmt->fetch(\PDO::FETCH_ASSOC);
                $paid_time = strtotime('+6 month', $paidres['time']);
            } else {
                $stmt = null;
                exit("Paid extension not purchased!");
            }
            $stmt = null;
        }

        if(system::getInstance()->doublemd5($last_version) == $fhash) { // its latest version, all is OK - return file
            $file_path = root . '/upload/extension/' . $last_version;
            if(file_exists($file_path)) {
                $serial_versioncontrol = unserialize($result['versioncontrol']);
                $full_version = $serial_versioncontrol;
                $lastrevision = array_pop($full_version);

                if($result['price'] > 0) {
                    if($paid_time < $lastrevision['time'])
                        exit('Your updates time is out of date');
                }

                $output_file_data = @file_get_contents($file_path);
                $output_file_name = $last_version;
                $output_file_size = filesize($file_path);
            }
        } else { // maybe its oldest or other version, not latest?
            $version_control = unserialize($result['versioncontrol']);
            foreach($version_control as $version) {
                if(system::getInstance()->doublemd5($version['download']) == $fhash) {
                    $file_path = root . '/upload/extension/' . $version['download'];
                    if(file_exists($file_path)) {

                        if($result['price'] > 0) {
                            if($paid_time < $version['time'])
                                exit('Your updates time is out of date');
                        }

                        $output_file_data = @file_get_contents($file_path);
                        $output_file_name = $version['download'];
                        $output_file_size = filesize($file_path);
                        break;
                    }
                }
            }
        }
        if(!is_null($output_file_data) && !is_null($output_file_name)) {
            // update info about download count
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_extension_item SET `loadcount` = `loadcount`+1 WHERE id = ?");
            $stmt->bindParam(1, $fid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;

            header("Pragma: public", true);
            header("Expires: 0"); // set expiration time
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header("Content-Disposition: attachment; filename=".$output_file_name);
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$output_file_size);
            exit($output_file_data);
        }
        exit("File is not founded!");
    }
}