<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Модуль реализующий нотификацию пользователя - количество новых личных сообщений, запросов в друзья.
 */
class mod_user_notify_front implements mod_front
{
    public function before() {}

    public function after() {
        global $engine;
        if($engine->user->get('id') < 1)
            return;
        $this->showNewPmCount();
        $this->showNewFriendRequestCount();
    }

    private function showNewPmCount() {
        global $engine;
        $userid = $engine->user->get('id');
        $lastpmview = $engine->user->customget('lastpmview');
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_messages WHERE `to` = ? AND timeupdate >= ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->bindParam(2, $lastpmview, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $new_pm_count = $result[0];
        $engine->template->globalset('user_notify.pm_new_count', $new_pm_count);
    }

    private function showNewFriendRequestCount() {
        global $engine;
        $friendRequestList = $engine->user->customget('friend_request');
        $friend_array = $engine->system->altexplode(',', $friendRequestList);
        $request_count = sizeof($friend_array);
        $engine->template->globalset('user_notify.friend_request_count', $request_count);
    }

}


?>