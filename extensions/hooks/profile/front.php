<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Пример добавления ссылки в публичное пользв. меню компонента usercontrol
 * @author zenn
 *
 */
class hook_profile_front implements hook_front
{
    public function load()
    {
        return $this;
    }

    public function before()
    {
        global $extension, $constant, $user;
        if ($extension->object['com']['usercontrol']) {
            $callback = $extension->object['com']['usercontrol'];
            $callback->hook_item_menu .= "<li><a href='{$constant->url}/user/id{$user->get('id')}/blackjack'>Black jack</a></li>";
            $callback->hook_item_url['blackjack'] = "Welcome to hook with blackjack and bitches ;D";
        }
    }
}


?>