<?php

namespace engine;

class maintenance extends singleton {

    public function make() {
        if(!property::getInstance()->get('maintenance')) // is not a maintenance mod
            return;
        if(permission::getInstance()->have('admin/main')) // not show for admin
            return;
        $login_form = extension::getInstance()->call(extension::TYPE_COMPONENT, 'user')->viewLogin(); // call to login view & worker
        $tpl = template::getInstance()->twigRender('maintenance.tpl', array('login_form' => $login_form)); // render with login form
        template::getInstance()->justPrint($tpl, array());
    }
}