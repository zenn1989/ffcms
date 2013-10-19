<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Компонент статических страниц
 */


/**
 * регистрация области uri компонента
 * Первый параметр - uri, второй - директория компонента
 */
if (!extension::registerPathWay('static', 'static')) {
    exit("Component static cannot be registered!");
}

/**
 * Главный класс компонента. Имя = com_ + имя директории компонента.
 */
class com_static_front implements com_front
{
    /**
     * Одноименный метод. Должен возвращать результат обработки.
     */
    public function load()
    {
        global $engine;
        $way = $engine->page->getPathway();
        // генерируем pathway для sql запроса из массива
        $sqllink = null;
        for ($i = 1; $i <= count($way) - 1; $i++) {
            $sqllink .= $way[$i];
            if ($i != count($way) - 1) {
                $sqllink .= "/";
            }
        }
        // либо запрос пуст, либо пользователь наркоман
        if ($sqllink == null) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        $engine->page->setContentPosition('body', $this->loadSinglePage($sqllink));

    }

    /**
     * Отображение статической страницы. Первый компонент (:
     */
    private function loadSinglePage($pathway)
    {
        global $engine;
        $query = "SELECT * FROM {$engine->constant->db['prefix']}_com_static WHERE pathway = ?";
        $stmt = $engine->database->con()->prepare($query);
        $stmt->bindParam(1, $pathway, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($stmt->rowCount() != 1) {
            return $engine->template->compile404();
        }
        $com_theme = $engine->template->get("page", "components/static/");
        $serial_title = unserialize($result['title']);
        $serial_text = unserialize($result['text']);
        $serial_keywords = unserialize($result['keywords']);
        $serial_description = unserialize($result['description']);
        $engine->meta->add('title', $serial_title[$engine->language->getCustom()]);
        $engine->meta->set('keywords', $serial_keywords[$engine->language->getCustom()]);
        $engine->meta->set('description', $serial_description[$engine->language->getCustom()]);
        return $engine->template->assign(array('title', 'text', 'date'), array($serial_title[$engine->language->getCustom()], $serial_text[$engine->language->getCustom()], $engine->system->toDate($result['date'], 'd')), $com_theme);

    }

}

?>