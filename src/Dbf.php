<?php
/**
 * Класс для работы с форматом хранения данных DBF
 *
 * @link       http://www.sedovsg.me
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me) 
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

declare(strict_types = 1);

namespace Fias;

use Fias\Dbase\Data;
use Fias\Dbase\Dbase;

/**
 * Класс реализует механизм работы с форматом хранения данных DBF
 *
 * @category   Library
 * @package    SedovSG/Fias
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me)
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 * @version    1.0.0
 * @since      1.0.0
 */
class Dbf
{
  /** @var int Все записи */
  public const FETCH_ALL     = 0;
  
  /** @var int Записи, соответствующие условию "Равно" */
  public const FETCH_EQUAL   = 1;
  
  /** @var int Записи, соответствующие условию "Содержит" */
  public const FETCH_INCLUDE = 2;
  
  /** @var int Записи, соответствующие условию "Исключено" */
  public const FETCH_EXCLUDE = 3;

 	/**
   * Метод создаёт экземпляр объекта класса DBF 
   *
   * @param string $dir Имя директории расположения файла DBF
   * 
   * @return void
   */
	public function __construct(string $dirName)
	{
    $this->data = new Data($dirName);
	}
  
  /**
   * Метод получает имя директории расположения файла DBF
   *
   * @return string
   */
  public function getDirname(): string
  {
    return $this->data->getPathToDbf();
  }

  /**
   * Метод открывает источник данных DBF
   *
   * @param  int $mode Режим открытия источника данных DBF
   * @see Dbase::open
   * 
   * @return \Fias\Dbase\Dbase
   */
  public function open($mode = Dbase::MODE_READ): Dbase
  {
    $this->path  = $this->getDirName() . DIRECTORY_SEPARATOR . $this->table;

    $this->dbase = Dbase::open($this->path, $mode);

    return $this->dbase;
  }

  /**
   * Метод закрывает источник данных DBF
   *
   * @return bool
   */
  public function close(): bool
  {
    return Dbase::close();
  }
  
  /**
   * Метод скачивает архив DBF
   *
   * @param Data::TYPE $dataType Тип данных
   * 
   * @return bool
   */
  public function download(?int $dataType = null): bool
  {
    return $this->data->downloadArchive($dataType);
  }
  
  /**
   * Метод распаковывает архив.
   *
   * @return bool
   */
  public function unpack(): bool
  {
    return $this->data->unpackArchive();
  }
  
  /**
   * Метод обновляет файлы DBF
   *
   * @param Data::TYPE $dataType Тип данных
   * 
   * @return bool
   */
  public function update(?int $dataType = null): bool
  {
    return $this->data->update($dataType);
  }

  /**
   * Метод устанавливает поля источника данных DBF
   *
   * @param  string $field Имя поля
   *
   * @return Dbf
   */
  public function select(string $field = ''): Dbf
  {
    if(!empty($field))
    {
      if(strstr($field, ','))
      {
        $this->fields = array_map(function($val) {
          return trim($val);
        }, explode(',', $field));
      }
      else
      {
        $this->fields[] = $field;
      }
    }

    return $this;
  }

  /**
   * Метод получает поля источника данных DBF
   *
   * @return array
   */
  public function getFields(): array
  {
    return $this->fields;
  }
  
  /**
   * Метод устанавливает источник данных DBF
   *
   * @param  string $table Имя источника данных
   *
   * @return Dbf
   */
  public function from(string $table = ''): Dbf
  {
    if(!empty($table))
    {
      $this->table = $table;
    }

    return $this;
  }

  /**
   * Метод получает имя источника данных DBF
   *
   * @return string
   */
  public function getTable(): string
  {
    return $this->table;
  }
  
  /**
   * Метод устанавливает условие "Равно"
   *
   * @param  string $query Строка запроса, вида: 'fieldName::fieldValue'
   *
   * @return Dbf
   */
  public function equal(string $query): Dbf
  {
    if(!empty($query))
    {
      $query = explode(',', $query);

      foreach($query as $key => $value)
      {
        $fieldName  = trim(strstr($value, '=', true));
        $fieldValue = trim(strstr($value, '='), ' =');

        $this->equals[] = ['field' => $fieldName, 'value' => $fieldValue];
      }
    }

    return $this;
  }
  
  /**
   * Метод устанавливает условие "Исключено"
   *
   * @param  string $query Строка запроса, вида: 'fieldName::!fieldValue'
   *
   * @return Dbf
   */
  public function exclude(string $query): Dbf
  {
    if(!empty($query))
    {
      $query = explode(',', $query);

      foreach($query as $key => $value)
      {
        $fieldName  = trim(strstr($value, '=', true));
        $fieldValue = trim(strstr($value, '='), ' =');

        $this->exclude[] = ['field' => $fieldName, 'value' => $fieldValue];
      }
    }

    return $this;
  }
  
  /**
   * Метод устанавливает условие "Содержит"
   *
   * @param  string $query Строка запроса, вида: 'fieldName::*fieldValue'
   *
   * @return Dbf
   */
  public function include(string $query): Dbf
  {
    if(!empty($query))
    {
      $query = explode(',', $query);

      foreach($query as $key => $value)
      {
        $fieldName  = trim(strstr($value, '=', true));
        $fieldValue = trim(strstr($value, '='), ' =');

        $this->include[] = ['field' => $fieldName, 'value' => $fieldValue];
      }
    }

    return $this;
  }

  /**
   * Метод выполняет запрос к источнику DBF
   *
   * @return Dbf
   */
  public function exect(): Dbf
  {
    $this->parse();
    $this->where();

    return $this;
  }
  
  /**
   * Метод получает данные, соответствующие запросу.
   *
   * @param  int $type Константа типа условия
   *
   * @return array
   */
  public function fetch(int $type = self::FETCH_ALL): array
  {
    $result = [];

    if($type == self::FETCH_ALL)
    {
      $result = array_merge($this->equaled, $this->included, $this->excluded);
    }
    elseif($type == self::FETCH_EQUAL)
    {
      $result = $this->equaled;
    }
    elseif($type == self::FETCH_INCLUDE)
    {
      $result = $this->included;
    }
    elseif($type == self::FETCH_EXCLUDE)
    {
      $result = $this->excluded;
    }

    return $result;
  }
  
  /**
   * Метод получает все элементы файла источника данных DBF
   *
   * @return array
   */
  public function fetchAll(): array
  {
    return $this->records;
  }

  /**
   * Метод получает количество уникальных элементов запроса.
   *
   * @return int
   */
  public function rowCount(): int
  {
    $count         = 0;
    $countEqualed  = 0;
    $countIncluded = 0;

    if(empty($this->equaled) && empty($this->included))
    {
      $count = sizeof($this->records);
    }
    else
    {
      (empty($this->equaled)) ?: $countEqualed = sizeof($this->equaled);
      
      (empty($this->included)) ?: $countIncluded = sizeof($this->included);
      
      $count = $countEqualed + $countIncluded;
    }

    return $count;
  }

  /**
   * Метод получает общее количество элементов в файле источника данных DBF
   *
   * @return int
   */
  public function numRows(): int
  {
    return count($this->records);
  }
  
  /**
   * Метод получает количество полей базы данных.
   *
   * @return int
   */
  public function numFields(): int
  {
    return $this->dbase->numFields();
  }

  /**
   * Метод получает информацию о свойствах полей базы данных
   * 
   * @return array
   */
  public function getFieldsInfo(): array
  {
    return $this->dbase->getFieldsInfo();
  }

  /**
   * Метод выполняет разбор файла источника данных DBF
   *
   * @return array
   */
  private function parse(): array
  {
    $this->open();

  	foreach($this->dbase as $index => $value)
  	{
      if($this->fields == null)
      {
        $this->fields = array_keys($value);
      }

  		if(!empty($this->fields))
  		{
  		  foreach($this->fields as $key)
  		  {
  		    $this->records[$index][$key] = self::encode((string) $value[$key]);
  		  }
  		}
  		else
  		{
  			$this->records[] = $value;
  		}
  	}

  	return $this->records;
  }
  
  /**
   * Метод выполняет условия запроса. 
   *
   * @return Dbf
   */
  private function where(): Dbf
  {
    $equal   = [];
    $exclude = [];
    $include = [];

    if(!empty($this->equals))
    {
      $result = [];

      foreach($this->equals as $key => $var)
      {
        $equal[] = $this->getEqualed($var);
      }

      $this->equaled = array_filter($equal);
    }

    if(!empty($this->exclude))
    {
      foreach($this->exclude as $var)
      {
        $exclude[] = $this->getExcluded($var);
      }

      $this->excluded = array_filter($exclude);

      (empty($this->equaled)) ?: $this->equaled = [];
    }

    if(!empty($this->include))
    {
      foreach($this->include as $var)
      {
        $include[] = $this->getIncluded($var);
      }

      $this->included = array_filter($include);
    }

    return $this;
  }
  
  /**
   * Метод получает данные, соответствующие запросу "Равно"
   *
   * @param  array  $value Данные запроса "Равно"
   *
   * @return array
   */
  private function getEqualed(array $value): array
  {
    $result = [];

    foreach($this->records as $key => $var)
    {
      if(key_exists($value['field'], $var))
      {
        if($value['value'] == $var[$value['field']])
        {
          $result = $var;
        }
      }
    }

    return $result;
  }
  
  /**
   * Метод получает данные, соответствующие запросу "Исключено"
   *
   * @param  array  $value Данные запроса "Исключено"
   *
   * @return array
   */
  private function getExcluded(array $value): array
  {
    foreach($this->records as $key => $var)
    {
      if(key_exists($value['field'], $var))
      {
        if($value['value'] == $var[$value['field']])
        {
          unset($this->records[$key]);
        }
      }
    }


    return $this->records;
  }

  /**
   * Метод получает данные, соответствующие запросу "Содержит"
   *
   * @todo  Решить проблему неправильной работы сопоставления
   *
   * @param  array  $value Данные запроса "Содержит"
   *
   * @return array
   */
  private function getIncluded(array $value): array
  {
    $result = [];

    ($value['field'] != 'NAMEP') ?: $value['value'] = base64_decode($value['value']);

    foreach($this->records as $key => $var)
    {
      if(key_exists($value['field'], $var))
      {
        if(preg_match("|^{$value['value']}|is", $var[$value['field']]))
        {
          $result[] = $var;
        }
      }
    }

    return $result;
  }
  
  /**
   * Метод построчно меняет кодировку файла источника данных DBF.
   *
   * @param  string $value Строка
   *
   * @return string
   */
  private static function encode(string $value): string
  {
  	return trim(mb_convert_encoding ($value, 'UTF-8', 'CP866'));
  }
  
  /** @var Data Объект данных ФИАС РФ */
  private $data = null;

  /** @var string Директория расположения файла DBF */
  private $dirName = '';

  /** @var string Путь до файла DBF */
  private $path = '';

  /** @var object dBase::open */
  private $dbase = null;

  /** @var string Имя файла DBF */
  private $table = '';

  /** @var array Массив названия ключей */
  private $fields = [];

  /** @var array Массив результирующих записей */
  private $records = [];

  /** @var array Массив условий "Равно" */
  private $equals = [];

  /** @var array Массив условий "Исключено" */
  private $exclude = [];

  /** @var array Массив условий "Содержит" */
  private $include = [];
  
  /** @var array Массив результирующих записей условия "Равно" */
  private $equaled = [];
  
  /** @var array Массив результирующих записей условия "Содержит" */
  private $included = [];
  
  /** @var array Массив результирующих записей условия "Исключено" */
  private $excluded = [];

}
