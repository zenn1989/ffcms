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
        global $user;
        if($user->get('id') < 1)
            return;
        $this->showNewPmCount();
        $this->showNewFriendRequestCount();
    }

    private function showNewPmCount() {
        global $database, $constant, $template, $user;
        $userid = $user->get('id');
        $lastpmview = $user->customget('lastpmview');
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_messages WHERE `to` = ? AND timeupdate >= ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->bindParam(2, $lastpmview, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $new_pm_count = $result[0];
        $template->globalset('user_notify.pm_new_count', $new_pm_count);
    }

    private function showNewFriendRequestCount() {
        global $system, $template, $user;
        $friendRequestList = $user->customget('friend_request');
        $friend_array = $system->altexplode(',', $friendRequestList);
        $request_count = sizeof($friend_array);
        $template->globalset('user_notify.friend_request_count', $request_count);
    }

}


?>