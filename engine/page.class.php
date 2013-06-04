<?php

/**
 * Стандартный клас для работы элементами страницы
 */
class page
{
	private $pathway = array();
	static private $nocacheurl = array();

	private $string_pathway = null;

	private $content_body = array();
	private $content_header = array();
	private $content_left = array();
	private $content_right = array();
	private $content_bottom = array();
	private $content_footer = array();

	private $notifyModuleAfter = array();

	private $isMainPage = false;
    private $isNullPage = false;

	function __construct()
	{
		$this->rawuri();
	}

	/**
	 * Обработка и вывод страницы.
	 */
	public function doload()
	{
		global $template,$system,$cache,$user,$admin,$extension,$meta,$constant;
		$isComponent = false;
		// пользователь пермаментно заблокирован?
		if($user->isPermaBan())
		{
			$template->overloadCarcase('permaban');
			$template->globalset('admin_email', $constant->mail['from_email']);
			return;
		}
		// если пользователь не авторизован и есть полный кеш страницы
		if($user->get('id') == NULL && $cache->check())
		{
			return $cache->get();
		}
		// если размер пачвея более 0
		if(sizeof($this->pathway) > 0)
		{
			$isComponent = $extension->initComponent();
		}
		// вхождение по урлам не найдено. Кхм!
		if(!$isComponent)
		{
			// может быть это главная страничка?
			if(sizeof($this->pathway) == 0 || $system->contains('index.', $this->pathway[0]))
			{
				$meta->set('title', $constant->seo_meta['title']);
				$this->isMainPage = true;
			}
			// Нет? Не главная? Скомпилим 404
			else
			{
                $this->isNullPage = true;
                $cache->setNoExist(true);
				$this->content_body[] = $template->compile404();
			}
		}
		$meta->compile();
		$template->init();
	}

    /**
     * Является ли текущая страница главной?
     * @return bool
     */
    public function isMain()
	{
		return $this->isMainPage;
	}

	/**
	 * Функция по поиску вхождений в правилах урл-ов
	 * к примеру /com/site/dddd/static.html является вхождением
	 * в /com/*
	 */
	public function findRuleInteration($rule_way)
	{
		global $system;
		$rule_split = explode("/", $rule_way);
		for($i=0;$i<=sizeof($rule_split);$i++)
		{
			// если уровень правила содержит * - возвращаем истину
			if($rule_split[$i] == "*")
			{
				return true;
			}
			else
			{
                // это главная страница?
                if($rule_split[$i] == "index" && $this->isMain())
                {
                    return true;
                }
				// если уровень правила и пачвея совпали
				if($rule_split[$i] == $this->pathway[$i])
				{
					// если это последний элемент пачвея
					if($system->contains('.html', $this->pathway[$i]))
					{
						return true;
					}
					// иначе - крутим дальше цикл
				}
				else
				{
					// если не совпали - возврат лжи
					return false;
				}
			}
		}
		// если после цикла нет точного определения, возвращаем лож, так как вхождения нет
		return false;
	}

	/**
	 * Разобранный путь реквеста в массив по сплиту /
	 */
	public function getPathway()
	{
		return $this->pathway;
	}
	
	/**
	 * Разобранный путь реквеста в массив разделяя по / без учета 1го вхождения
	 * @return mixed
	 */
	public function shiftPathway()
	{
		$way = $this->pathway;
		array_shift($way);
		return $way;
	}

	/**
	 * Чистый пачвей для спец. нужд
	 */
	public function getStrPathway()
	{
		return $this->string_pathway;
	}

	/**
	 * Запретить кеширование
	 */
	public static function setNoCache($nullway)
	{
		self::$nocacheurl[] = $nullway;
	}

	/**
	 * Получить запрещенные урл-ы для кеша
	 */
	public static function getNoCache()
	{
		return self::$nocacheurl;
	}

	/**
	 * Разбивка запроса от пользователя на массив.
	 * Пример: /novosti/obshestvo/segodnya-sluchilos-prestuplenie.html приймет вид:
	 * array(0 => 'novosti', 1 => 'obshestvo', 2 => 'segodnya-sluchilos-prestuplenie.html')
	 */
	private function rawuri()
	{
		$this->string_pathway = $_SERVER['REQUEST_URI'];
		$split = explode("/", $this->string_pathway);
		foreach($split as $values)
		{
			if($values != null)
			{
				$this->pathway[] = $values;
			}
		}
	}

	/**
	 * Возвращение массива позиции. Пример.
	 */
	public function getContentPosition($position)
	{
		$pos = "content_{$position}";
		return $this->{$pos};
	}

	/**
	 * Добавление к массиву позиций значения
	 */
	public function setContentPosition($position, $content, $index = null)
	{
		$var = "content_{$position}";
		if($index == null)
		{
			$this->{$var}[] = $content;
		}
		else
		{
			$this->{$var}[$index] = $content;
		}
	}

    /**
     * Функция возвращающая true в случае если текущая страница является системной
     * @return bool
     */
    public function isNullPage()
    {
        return $this->isNullPage;
    }

    /**
     * Создание HASH-набора символов из текущего pathway. Учитывает вхождение до первого окончания .html без учета pathway[0]
     * Пример: /news/someother/some_interest_words_1.html/1#com-32
     * обработается как md5('/someother/some_interest_words_1.html');
     * Рекомендовано использование для сквозных модулей, которым необходима уникальная строка для каждой страницы
     * @return string
     */
    public function hashFromPathway()
    {
        global $system;
        $string = null;
        for($i=1;$i<=sizeof($this->pathway);$i++)
        {
            if($system->extensionEquals($this->pathway[$i], '.html'))
            {
                $string .= $this->pathway[$i];
                continue;
            }
            elseif($this->pathway[$i] != null)
            {
                $string .= $this->pathway[$i]."/";
            }
        }
        return $string != null ? md5($string) : null;
    }
}


?>