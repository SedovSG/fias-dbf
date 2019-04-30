<?php

use PHPUnit\Framework\TestCase;
use Fias\Dbase\Data;

/**
 * Тесты для класса Fias\Dbase\Data
 */
class DataTest extends TestCase
{

  /** @var Data Ресурс объекта Data */
  private $data = null;

  /** @var string Имя директории для DBF файлов */
  private static $dirname = __DIR__ . '/../../data';

  protected function setUp()
  { 
    $this->data = new Data(self::$dirname);
  }

  protected function tearDown()
  {
    $this->data = null;
  }

  public static function tearDownAfterClass()
  {
    if(is_file(self::$dirname))
    {
      unlink(self::$dirname);
      rmdir(preg_replace('|[/\\\]([^\/\\\]*)$|i', '', self::$dirname));
    }
    else
    {
      $objects = scandir(self::$dirname);
      foreach ($objects as $object)
      {
        if ($object != "." && $object != "..")
        {
          if (filetype(self::$dirname."/".$object) == "dir") rrmdir(self::$dirname."/".$object); else unlink(self::$dirname."/".$object);
        }
      }
      reset($objects);
      rmdir(self::$dirname);
    }
  }

  public function testGetPathToDbf()
  {
    $result = $this->data->getPathToDbf();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertDirectoryExists($result);
  }

  public function testGetInfo()
  {
    $result = $this->data->getInfo();

    $this->assertIsBool($result);
    $this->assertNotFalse($result);
  }

  public function testGetDatabaseVersion()
  {
    $result = $this->data->getDatabaseVersion();

    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testGetDatabaseName()
  {
    $result = $this->data->getDatabaseName();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testGetDatabaseDateCreated()
  {
    $result = $this->data->getDatabaseDateCreated();

    $this->assertRegExp('|\d{2}\.\d{2}\.\d{4}|', $result);
  }
  
  /**
   * @dataProvider additionTypesUrlForArchiveProvider
   */
  public function testGetUrlForArchive($type)
  {
    $result = $this->data->getUrlForArchve($type);

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertRegExp('~(^http[s]*://data.nalog.ru/Public/Downloads/\d*/.*\.(?:rar|arj|7z)*)~', $result);
  }

  // public function testDownloadArchive()
  // {
  //   $result = $this->data->downloadArchive(Data::TYPE_DBF_DELTA);
    
  //   $this->assertIsBool($result);
  //   $this->assertTrue($result);
  // }

  public function unpackArchive()
  {
    $result = $this->data->unpackArchive();
    $file = $this->data->getPathToDbf() . DIRECTORY_SEPARATOR . 'ACTSTAT.DBF';
    
    $this->assertTrue($result);
    $this->assertFileExists($file);
    $this->assertFileIsReadable($file);
  }
  
  /**
   * @cover Data::isLocalDatabaseOlderVersion
   * @cover Data::getLocalDatabaseVerion
   * @cover Data::cleanDirectory
   */
  public function testUpdate()
  {
    $result = $this->data->update(Data::TYPE_DBF_DELTA);
    $file = $this->data->getPathToDbf() . DIRECTORY_SEPARATOR . 'ACTSTAT.DBF';

    $this->assertTrue($result);
    $this->assertFileExists($file);
    $this->assertFileIsReadable($file);
  }
  
  public function additionTypesUrlForArchiveProvider()
  {
    return [
      'dbf-all'    => [Data::TYPE_DBF_ALL],
      'xml-all'    => [Data::TYPE_XML_ALL],
      'dbf-delta'  => [Data::TYPE_DBF_DELTA],
      'xml-delta'  => [Data::TYPE_XML_DELTA],
      'kladr-arj'  => [Data::TYPE_KLADR_ARJ],
      'kladr-7z'   => [Data::TYPE_KLADR_7Z],
      'order-int'  => [4],
      'older-null' => [null]
    ];
  }

}
