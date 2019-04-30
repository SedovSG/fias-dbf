<?php

use PHPUnit\Framework\TestCase;
use Fias\Dbase\Dbase;

/**
 * Тесты для класса Fias\Dbase\Dbase
 */
class DbaseTest extends TestCase
{
  
  /** @var string Полное имя файла */
  private static $filename = 'data/test.dbf';

  public static function tearDownAfterClass()
  {
    if(is_file(self::$filename))
    {
      unlink(self::$filename);
      rmdir(preg_replace('|[/\\\]([^\/\\\]*)$|i', '', self::$filename));
    }
    else
    {
      $objects = scandir(self::$filename);
      foreach ($objects as $object)
      {
        if ($object != "." && $object != "..")
        {
          if (filetype(self::$filename."/".$object) == "dir") rrmdir(self::$filename."/".$object); else unlink(self::$filename."/".$object);
        }
      }
      reset($objects);
      rmdir(self::$filename);
    }
  }

  public function testCreate()
  {
    $fields = [
      ['date', 'D'],
      ['name', 'C', 20],
      ['lastname', 'C', 50],
      ['age', 'N', 3, 0],
      ['email', 'C', 128]
    ];

    $result = Dbase::create(self::$filename, $fields);

    $this->assertFileExists(self::$filename);
    $this->assertIsObject($result);
    $this->assertInstanceOf(Dbase::class, $result);
  }
  
  public function testOpen()
  {
    $result = Dbase::open(self::$filename);
    
    $this->assertIsObject($result);
    $this->assertInstanceOf(Dbase::class, $result);
  }

  public function testGetFieldsInfo()
  {
    $result = Dbase::open(self::$filename)->getFieldsInfo();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testOffsetSet()
  {
    $result = Dbase::open(self::$filename, Dbase::MODE_READ_WRITE);

    $result[] = ['13.08.1992', 'Ivan', 'Samsonov', '27', 'samson@yandex.ru'];
    $result[] = ['05.03.1987', 'Alex', 'Sidorov', '32', 'sidorov@mail.ru'];
    $result[1] = ['05.03.1987', 'Alexandr', 'Sidorov', '32', 'sidorov@mail.ru'];

    $this->assertNotEmpty($result);
    $this->assertCount(2, $result);
  }

  public function testNumFields()
  {
    $result = Dbase::open(self::$filename)->numFields();

    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testCount()
  {
    $result = count(Dbase::open(self::$filename));

    $this->assertIsInt($result);
    $this->assertEquals(2, $result);
  }

  public function testOffsetGet()
  {
    $result = Dbase::open(self::$filename)[0];

    $this->assertIsString($result['name']);
    $this->assertRegExp('|[\-\d\s\.\:T]*|', $result['date']);
  }

  public function testOffsetUnset()
  {
    $result = Dbase::open(self::$filename, Dbase::MODE_READ_WRITE);

    unset($result[1]);

    $this->assertNotEmpty($result);
    $this->assertCount(1, $result);
  }

  public function testCurrent()
  {
    $dbf = Dbase::open(self::$filename);

    foreach($dbf as $val)
    {
      $result = current($val);
      $numArray = $dbf->current(Dbase::TYPE_INDEX_ARRAY);
    }

    $this->assertArrayHasKey(3, $numArray);
    $this->assertIsInt($numArray[3]); // Age
    
    $this->assertIsString($result);
    $this->assertRegExp('|[\-\d\s\.\:T]*|', $result);
  }

  public function testKey()
  {
    $dbf = Dbase::open(self::$filename);

    $key = $dbf->key();
    $this->assertIsInt($dbf[$key]['age']);

    foreach($dbf as $val)
    {
      $result = key($val);
    }
    
    $this->assertIsString($result);
    $this->assertSame('date', $result);
  }

  public function testNext()
  {
    $dbf = Dbase::open(self::$filename);

    foreach($dbf as $val)
    {
      $result = key($val);
      next($val);
      $result = key($val);
    }
    
    $this->assertIsString($result);
    $this->assertSame('name', $result);
  }
  
}
