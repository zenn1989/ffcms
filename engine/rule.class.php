<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Класс реализующий правила и их обработку в шаблонах. Пример: {$if rule}some data{/$if}
 * @author zenn
 *
 */
class rule
{
    public $rule_data = array();

    // реализация нативных правил
    function rule()
    {
        global $user;
        if ($user->get('id') > 0) {
            $this->rule_data['user.auth'] = true;
        }
        if ($user->get('access_to_admin') > 0) {
            $this->rule_data['user.admin'] = true;
        }
    }

    /**
     * Проверка условия.
     * @param unknown_type $rule
     */
    public function check($rule)
    {
        // возможен массив условий a && !a && b
        $rule_array = explode(" && ", $rule);
        $result_check = true;
        foreach ($rule_array as $singleton) {
            if (substr($singleton, 0, 1) == "!") {
                $singleton = substr($singleton, 1);
                // если условие истино - то найдено исключение для !
                if ($this->rule_data[$singleton]) {
                    $result_check = false;
                }
            } else {
                if (!$this->rule_data[$singleton]) {
                    $result_check = false;
                }
            }
        }
        return $result_check;
    }

    /**
     * Добавление правил для расширений и пользовательских наработок.
     * @param unknown_type $rule
     * @param unknown_type $value
     */
    public function add($rule, $value)
    {
        if (!array_key_exists($rule, $this->rule_data)) {
            $this->rule_data[$rule] = $value;
        }
    }
}


?>