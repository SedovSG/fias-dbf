<?php
/**
 * Класс реализует методы работы с файловой БД dBase (DBF) 
 *
 * @link       http://www.sedovsg.me
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me) 
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

declare(strict_types = 1);

namespace Fias\Dbase;

/**
 * Класс реализует методы работы с фаловой БД dBase (DBF)
 *
 * @category   Library
 * @package    SedovSG/Fias
 * @author     Memory Clutter, <memclutter@gmail.com>
 * @copyright  Copyright (c) 2015-2019 Седов Станислав. (http://www.sedovsg.me)
 * @license    https://opensource.org/licenses/MIT MIT License
 * @version    1.1.0
 * @since      1.0.0
 */
class Dbase implements \ArrayAccess, \Iterator, \Countable
{
  /** @var int Разрешение на запись */
	const MODE_READ = 0;

  /** @var int Разрешение на чтение */
	const MODE_WRITE = 1;

  /** @var int Разрешение на чтение и запись */
	const MODE_READ_WRITE = 2;

  /** @var int Тип массива - ассоциативный */
  const TYPE_HASH_ARRAY  = 1;
  
  /** @var int Тип массива - индексный */
  const TYPE_INDEX_ARRAY = 2;
  
  /**
   * Метод открывает базу данных.
   *
   * @param  string $filename  Имя базы данных
   * @param  int    $mode      Режим открытия базы данных
   * 0 - режим для чтения, 1 - режим для записи, 2 - режим для чтения и записи
   * @throws \ErrorException   Если файл БД не найден
   * @throws \ErrorException   Если библиотека dBase отсутствует
   * @throws \RuntimeException Если не удалось открыть файл БД
   *
   * @return Dbase
   */
	public static function open($filename, $mode = self::MODE_READ): Dbase
	{
		if(!file_exists($filename))
		{
		  throw new \ErrorException(sprintf('Файл %s не найден', $filename));
		}

		if(!function_exists('dbase_open'))
		{
		  throw new \LogicException('Расширение dBase не поддерживается данной версией PHP');
		}

		self::$dbase = dbase_open($filename, $mode);

		if(self::$dbase === false)
		{
      throw new \RuntimeException(sprintf('Не удалось открыть файл базы данных %s', $filename));
		}

		return new self(self::$dbase);
	}

  /**
   * Метод создаёт базу данных.
   *
   * @param  string $filename  Имя базы данных
   * @param  array  $fields    Массив массивов, в котором каждый массив описывает формат
   * одного поля базы данных. Формат каждого поля состоит из имени этого поля, символа,
   * указывающего тип поля, и, при необходимости, его длину, точность и флаг обнуляемости.
   * @throws \ErrorException   Если файл БД не найден
   * @throws \ErrorException   Если библиотека dBase отсутствует
   * @throws \RuntimeException Если не удалось открыть файл БД
   *
   * @return Dbase
   */
	public static function create($filename, array $fields): Dbase
  {
    $filename = self::setPathToDbf($filename);

    if(!function_exists('dbase_create'))
    {
      throw new \LogicException('Расширение dBase не поддерживается данной версией PHP');
    }

    self::$dbase = dbase_create($filename, $fields);

    if(self::$dbase === false)
    {
      throw new \RuntimeException(sprintf('Не удалось создать файл базы данных %s', $filename));
    }

    return new self(self::$dbase);
  }
  
  /**
   * Метод закрывает соединение с базой данных
   * 
   * @return bool
   */
  public static function close(): bool
  {
    return dbase_close(self::$dbase);
  }
  
  /**
   * Метод получает информацию о свойствах полей базы данных.
   *
   * @return array
   */
	public function getFieldsInfo(): array
	{
		return dbase_get_header_info(self::$dbase);
	}

  /**
   * Метод получает количество полей базы данных.
   *
   * @return int
   */
	public function numFields(): int
	{
    return dbase_numfields(self::$dbase);
	}

  /**
   * Метод получает количество записей в базе данных.
   *
   * @return int
   */
  public function count(): int
	{
    return dbase_numrecords(self::$dbase);
	}
  
  /**
   * Метод получает текущее значение элемента в виде массива.
   *
   * @param  int   $type Флаг типа массива. true - ассоциативный, false - индексированный
   *
   * @return mixed
   */
	public function current(int $type = self::TYPE_HASH_ARRAY)
	{
		if($type === self::TYPE_INDEX_ARRAY)
		{
		  $result = dbase_get_record(self::$dbase, $this->record);
		}
		elseif($type === self::TYPE_HASH_ARRAY)
		{
			$result = dbase_get_record_with_names(self::$dbase, $this->record);
		}

    return $result;
	}
  
  /**
   * Метод сдвигает указатель к следующему элементу.
   *
   * @return int
   */
	public function next()
	{
		++$this->record;
	}
  
  /**
   * Метод получает ключ текущего элемента.
   *
   * @return int
   */
	public function key(): int
	{
		return $this->record;
	}
  
  /**
   * Метод проверяет является ли действительной текущая позиция.
   * Должен вызываться после методов Iterator::rewind() или Iterator::next()
   *
   * @return bool
   */
	public function valid(): bool
  {
    return $this->record <= count($this);
  }
  
  /**
   * Метод сбрасывает указатель на начальную позицию.
   *
   * @return int
   */
  public function rewind()
  {
    $this->record = 1;
  }

  /**
   * Метод проверят существования значения по заданному ключу.
   *
   * @param  mixed $offset Позиция
   *
   * @return bool
   */
  public function offsetExists($offset): bool
  {
    if($offset !== null)
    {
      return(false !== dbase_get_record_with_names(self::$dbase, $offset + 1));
    }

    return false;
  }
  
  /**
   * Метод получает значение по заданному ключу.
   *
   * @param  mixed $offset         Позиция
   * @throws \OutOfBoundsException Если текущая позиция указателя некорректна
   *
   * @return array
   */
  public function offsetGet($offset): array
  {
    if($this->offsetExists($offset))
    {
      return dbase_get_record_with_names(self::$dbase, $offset + 1);
    }
    else
    {
      throw new \OutOfBoundsException(sprintf('Недействительная позиция %s', $offset));
    }
  }
  
  /**
   * Метод устанавливает значение с указанием индекса.
   *
   * @param  mixed $offset Позиция
   * @param  mixed $value  Значение
   *
   * @return mixed
   */
  public function offsetSet($offset, $value)
  {
    if($this->offsetExists($offset))
    {
      dbase_replace_record(self::$dbase, $value, $offset + 1);
    }
    else
    {
      dbase_add_record(self::$dbase, $value);
    }
  }
  
  /**
   * Метод удаляет значение текущей позиции.
   *
   * @param  mixed $offset         Позиция
   * @throws \OutOfBoundsException Если текущая позиция указателя некорректна
   *
   * @return void
   */
  public function offsetUnset($offset)
  {
    if(isset($this[$offset]))
    {
      dbase_delete_record(self::$dbase, $offset + 1);
      dbase_pack(self::$dbase);
    }
    else
    {
      throw new \OutOfRangeException(sprintf('Недействительная позиция %s', $offset));
    }
  }

  /**
   * Метод устанавливает путь до файлов DBF
   *
   * @throws \ErrorException Если не удалось создать директорию
   * 
   * @param string $value Путь
   *
   * @return string
   */
  private static function setPathToDbf(string $value = '')
  {
    $value = (strstr($value, '..')) ? __DIR__ . DIRECTORY_SEPARATOR . $value : $value;

    preg_match('|(.*)[/\\\]([^\/\\\]*)$|i', $value, $filename);

    if(!file_exists($filename[1]))
    {
      if(!mkdir($filename[1], 0775, true))
      {
        throw new \ErrorException("Не удалось создать директорию");
      }
    }

    return $filename[0];
  }

  /** @var null Ссылка на ресурс dBase */
  private static $dbase  = null;

  /** @var int Позиция смещения */
  private $record = 0;

}
