<?php

use PHPUnit\Framework\TestCase;
use Fias\Dbase\Data;
use Fias\Dbf;

/**
 * Тесты для класса Fias\Dbase\Dbase
 */
class DbfTest extends TestCase
{

  /** @var Dbf Ресурс объекта Dbf */
  private $dbf = null;
  
  /** @var string Имя директории для DBF файлов */
  private static $dirname = __DIR__ . '/../data';

  protected function setUp()
  { 
    $this->dbf = (new Dbf(self::$dirname));
  }

  protected function tearDown()
  {
    $this->dbf = null;
  }

  public static function setUpBeforeClass()
  {
    (new Data(self::$dirname))->update(Data::TYPE_DBF_DELTA);
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

  public function testGetDirname()
  {
    $result = $this->dbf->getDirname();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertDirectoryExists($result);
  }

  public function testSelect()
  {
    $result = $this->dbf->
      from('STRSTAT.DBF')->
      exect()->
      fetchAll();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);

    return $result;
  }

  public function testGetFields()
  {
    $result = $this->dbf->
      select('STRSTATID, NAME')->
      select('SHORTNAME')->
      from('STRSTAT.DBF')->
      exect()->
      getFields();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(3, $result);
    $this->assertTrue($result[1] === 'NAME');
  }

  public function testGetTable()
  {
    $result = $this->dbf->
      select('ACTSTATID')->
      from('ACTSTAT.DBF')->
      exect()->
      getTable();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testEqual()
  {
    $result = $this->dbf->
      select()->
      from('STRSTAT.DBF')->
      equal(
        'STRSTATID = 2, NAME = Литер'
      )->
      exect()->
      fetch();

    $this->assertIsArray($result);
    $this->assertCount(2, $result);
  }
  // 
  /**
   * @depends testSelect
   */
  public function testExclude(array $resultAll)
  {
    $result = $this->dbf->
      select()->
      from('STRSTAT.DBF')->
      exclude(
        'STRSTATID = 1'
      )->
      exect()->
      fetch();

    $this->assertIsArray($result);
    $this->assertLessThan(count($resultAll), count($result[0]));
  }

  public function testInclude()
  {
    $result = $this->dbf->
      select()->
      from('ESTSTAT.DBF')->
      include(
        'NAME = Гараж'
      )->
      exect()->
      fetch();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
  }

  /**
   * @covers \Fias\Dbf::fetch
   * @covers \Fias\Dbf::rowCount
   */
  public function testFetchEqual()
  {
    $result = $this->dbf->
      select()->
      from('CENTERST.DBF')->
      equal(
        'NAME = Объект является центром района'
      )->
      include(
        'CENTERSTID = 2, CENTERSTID = 4'
      )->
      exect();

    $count = $result->rowCount();

    $result = $result->fetch(Dbf::FETCH_EQUAL);

    $this->assertIsInt($count);
    $this->assertEquals(3, $count);

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(1, $result);
  }
  
  /**
   * @covers \Fias\Dbf::fetch
   * @covers \Fias\Dbf::rowCount
   */
  public function testFetchInclude()
  {
    $result = $this->dbf->
      select()->
      from('CENTERST.DBF')->
      equal(
        'NAME = Объект является центром района,
         NAME = Объект является центром (столицей) региона'
      )->
      include(
        'CENTERSTID = 3, CENTERSTID = 4'
      )->
      exect();

    $count = $result->rowCount();

    $result = $result->fetch(Dbf::FETCH_INCLUDE);

    $this->assertIsInt($count);
    $this->assertEquals(4, $count);

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(2, $result);
  }
  
  /**
   * @covers \Fias\Dbf::fetch
   * @covers \Fias\Dbf::rowCount
   */
  public function testFetchExclude()
  {
    $resultAll = $this->dbf->
      select()->
      from('CENTERST.DBF')->
      exect();

    $countAll = $resultAll->rowCount();

    $result = $this->dbf->
      select()->
      from('CENTERST.DBF')->
      exclude(
       'CENTERSTID = 4'
      )->
      exect();

    $count = $result->rowCount();

    $result = $result->fetch(Dbf::FETCH_EXCLUDE);

    $this->assertIsInt($count);
    $this->assertLessThan($countAll, $count);

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(1, $result);
  }

  public function testNumRows()
  {
    $result = $this->dbf->
      select()->
      from('ACTSTAT.DBF')->
      exect()->numRows();

    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testNumFields()
  {
    $result = $this->dbf->
      select()->
      from('ACTSTAT.DBF')->
      exect()->numFields();

    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testGetFieldsInfo()
  {
    $result = $this->dbf->
      select()->
      from('FLATTYPE.DBF')->
      exect()->getFieldsInfo();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
  }

  public function testDownload()
  {
    $result = $this->dbf->download(Data::TYPE_DBF_DELTA);

    $this->assertIsBool($result);
    $this->assertTrue($result);
  }

  public function testUnpack()
  {
    $result = $this->dbf->unpack();
    $file = $this->dbf->getDirname() . DIRECTORY_SEPARATOR . 'ROOM76.DBF';
    
    $this->assertTrue($result);
    $this->assertFileExists($file);
    $this->assertFileIsReadable($file);
  }

  public function testUpdate()
  {
    $result = $this->dbf->update(Data::TYPE_DBF_DELTA);

    $this->assertIsBool($result);
    $this->assertTrue($result);
  }

  public function testClose()
  {
    $result = $this->dbf->close();

    $this->assertTrue($result);
  }

}
