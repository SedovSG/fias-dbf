<?php
/**
 * Класс для получения данных ФИАС РФ
 *
 * @link       http://www.sedovsg.me
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me) 
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

declare(strict_types = 1);

namespace Fias\Dbase;

/**
 * Класс получает данные с сайта ФИАС РФ
 *
 * @category   Library
 * @package    SedovSG/Fias
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me)
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 * @version    1.0.0
 * @since      1.0.0
 */
class Data
{
  /** @var string Веб-адрес ФИАС РФ */
	public const FIAS_URL = 'https://fias.nalog.ru';

  /** @var integer Все данные в формате DBF */
  public const TYPE_DBF_ALL   = 1;
  
  /** @var integer Все данные в формате XML */
  public const TYPE_XML_ALL   = 2;
  
  /** @var integer Обновления данных в формате DBF */
  public const TYPE_DBF_DELTA = 3;
  
  /** @var integer Обновления данных в формате DBF */
  public const TYPE_XML_DELTA = 4;
  
  /** @var integer Данные КЛАДР в формате ARJ */
  public const TYPE_KLADR_ARJ = 5;
  
  /** @var integer Данные КЛАДР в формате 7Z */
  public const TYPE_KLADR_7Z  = 6;
  
	/**
   * Создаёт экземпляр объекта загрузки и обновления DBF
   * 
   * @return void
   */
	public function __construct(string $pathDbfDirectory = '')
	{
    $this->setPathToDbf($pathDbfDirectory);
    $this->getInfo();
	}

  /**
   * Метод устанавливает путь до файлов DBF
   * 
   * @param string $value Путь
   */
  public function setPathToDbf(string $value = '')
  {
    $this->pathDbfDirectory = $value;

    if(!file_exists($this->pathDbfDirectory))
    {
      mkdir($this->pathDbfDirectory, 0775, true);
    }
  }
  
  /**
   * Метод получает путь до файлов DBF
   * 
   * @return string
   */
  public function getPathToDbf(): string
  {
    return $this->pathDbfDirectory;
  }

  /**
   * Метод получает информацию о базе данных.
   *
   * @throws \LogicException Если библиотека SOAP отсутствует
   * @throws \SoapFault      Ошибка SOAP
   * @throws \Exception      Если не удалось получить информацию о базе данных ФИАС
   *
   * @return boolean
   */
  public function getInfo()
  {
    $soap   = null;
    $result = null;

    if(extension_loaded('soap') == false)
    {
      throw new \LogicException('Библиотека SOAP не подключена');
    }

    try
    {
      $options = [
        'soap_version'   => SOAP_1_2,
        'exceptions'     => true,
        'trace'          => true,
        'cache_wsdl'     => WSDL_CACHE_MEMORY,
        'user_agent'     => 'Mozilla/5.0 (Windows NT 5.2; rv:6.0.2) Gecko/20170101 Firefox/6.0.2'
      ];

      $soap = new \SoapClient(self::FIAS_URL . '/WebServices/Public/DownloadService.asmx?WSDL', $options);

      $result = $soap->GetLastDownloadFileInfo()->GetLastDownloadFileInfoResult;

      if($result == null)
      {
        throw new \Exception("Не удалось получить информацию о базе данных ФИАС");   
      }

      $this->databaseVersion   = $result->VersionId;
      $this->databaseName      = $result->TextVersion;
      $this->urlAllDataDbf     = $result->FiasCompleteDbfUrl;
      $this->urlAllDataXml     = $result->FiasCompleteXmlUrl;
      $this->urlDeltaDataDbf   = $result->FiasDeltaDbfUrl;
      $this->urlDeltaDataXml   = $result->FiasDeltaXmlUrl;
      $this->urlKladrArj       = $result->Kladr4ArjUrl;
      $this->urlKladr7z        = $result->Kladr47ZUrl;

      return true;
    }
    catch(\SoapFault $exp) {
      throw $exp;
    }
  }
  
  /**
   * Метод получает версию базы данных.
   * 
   * @return integer
   */
  public function getDatabaseVersion():int
  {
    return (int) $this->databaseVersion;
  }
  
  /**
   * Метод получает полное название базы данных.
   * 
   * @return string
   */
  public function getDatabaseName():string
  {
    return $this->databaseName;
  }
  
  /**
   * Метод получает дату формирования базы данных.
   * 
   * @return string
   */
  public function getDatabaseDateCreated():string
  {
    return substr($this->databaseName, strpos($this->databaseName, 'от') + 5);
  }

  /**
   * Метод получает URL до Rar архива.
   *
   * @param integer|null $dataType Тип данных
   * 
   * @return string
   */
	public function getUrlForArchve(?int $dataType = null): string
	{
    switch ($dataType) {
      case self::TYPE_DBF_ALL:
        $this->urlArchive = $this->urlAllDataDbf;
        break;
      case self::TYPE_XML_ALL:
        $this->urlArchive = $this->urlAllDataXml;
        break;
      case self::TYPE_DBF_DELTA:
        $this->urlArchive = $this->urlDeltaDataDbf;
        break;
      case self::TYPE_XML_DELTA:
        $this->urlArchive = $this->urlDeltaDataXml;
        break;
      case self::TYPE_KLADR_ARJ:
        $this->urlArchive = $this->urlKladrArj;
        break;
      case self::TYPE_KLADR_7Z:
        $this->urlArchive = $this->urlKladr7z;
        break;
      default:
        $this->urlArchive = $this->urlAllDataDbf;
        break;
    }

		return $this->urlArchive;
	}
  
  /**
   * Метод выполняет обновление данных ФИАС РФ
   *
   * @param integer|null $dataType Тип данных
   * 
   * @return bool
   */
	public function update(?int $dataType = null): bool
	{
    if(empty(glob($this->pathDbfDirectory . '/*.rar')))
    {
      !$this->downloadArchive($dataType) ?: $this->unpackArchive();
    }
    else
    {
      foreach(new \DirectoryIterator($this->pathDbfDirectory) as $fileInfo)
      {
        if($fileInfo->isDot()) continue;

        if($fileInfo->getExtension() === 'rar' && $this->isLocalDatabaseOlderVersion($fileInfo->getFilename()))
        {
          $this->cleanDirectory();
          !$this->downloadArchive($dataType) ?: $this->unpackArchive();
        }
      }
    }

    return true;
	}
  
  /**
   * Метод скачивает архив.
   *
   * @param integer|null $dataType Тип данных
   * 
   * @throws \ErrorException Если локальный файл недоступен для записи
   * @throws \ErrorException Если загружаемый файл недоступен для чтения
   * @throws \LogicException Если библиотека cURL отсутствует
   * @throws \ErrorException Если не удалось скопировать архив
   * 
   * @return bool
   */
  public function downloadArchive(?int $dataType = null): bool
  {
    $file = $this->pathDbfDirectory . DIRECTORY_SEPARATOR . 'fias_' . $this->databaseVersion . '.rar';
    $url  = $this->getUrlForArchve($dataType);

    $localResource = fopen($file, 'w');

    if($localResource === false && !is_writable($file))
    {
      throw new \ErrorException("Локальный файл недоступен для записи");
    }

    $remoteResource = fopen($url, 'r');

    if($remoteResource === false && !is_readable($url))
    {
      throw new \ErrorException("Загружаемый файл недоступен для чтения");
    }

    fclose($remoteResource);

    if(extension_loaded('curl') == false)
    {
      throw new \LogicException('Библиотека cURL не подключена');
    }

    $curl_handle = curl_init($url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 50);
    curl_setopt($curl_handle, CURLOPT_FILE, $localResource);
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl_handle, CURLOPT_ENCODING, "");
    curl_setopt($curl_handle, CURLOPT_USERAGENT,
      'Mozilla/5.0 (Windows NT 5.2; rv:6.0.2) Gecko/20170101 Firefox/6.0.2');

    $result = curl_exec($curl_handle);

    if($result === false)
    {
      throw new \ErrorException("Не удалось скопировать архив");  
    }

    curl_close($curl_handle);

    fclose($localResource);

    return true;
  }
   
  /**
   * Метод распаковывает архив.
   *
   * @throws \LogicException   Если библиотека RarArchive отсутствует
   * @throws \RuntimeException Если не удалось открыть архив
   * 
   * @return bool
   */
  public function unpackArchive(): bool
  {
    $archive     = null;
    $pathnameArchive = '';

    if(extension_loaded('rar') == false)
    {
      throw new \LogicException('Библиотека RarArchive не подключена');
    }

    foreach(new \DirectoryIterator($this->pathDbfDirectory) as $fileInfo)
    {
      if($fileInfo->isDot()) continue;

      if($fileInfo->isFile() && $fileInfo->getExtension() === 'rar')
      {
        if($fileInfo->getBasename() === 'fias_' . $this->databaseVersion . '.rar')
        {
          $pathnameArchive = $fileInfo->getPathname();
        }
      }
    }

    if($pathnameArchive == null) return false;

    $archive = \RarArchive::open($pathnameArchive);
    $entries = $archive->getEntries();

    if($entries !== false)
    {
      foreach($entries as $entry)
      {
        $entry->extract($this->pathDbfDirectory);
      }
    }
    else
    {
      throw new \RuntimeException('Не удалось открыть архив');
    }

    $archive->close();

    return true;
  }
  
  /**
   * Метод проверят является ли локальная базы данных более старой.
   *
   * @param  string  $filename Имя архива базы данных
   * 
   * @return boolean 
   */
  private function isLocalDatabaseOlderVersion(string $filename)
  {
    if($this->getLocalDatabaseVerion($filename) < $this->databaseVersion || $filename == null)
    {
      return true;
    }
  }

  /**
   * Метод очищает директорию, кроме архива актуальных данных.
   */
  protected function cleanDirectory(): void
  {
    foreach (new \DirectoryIterator($this->pathDbfDirectory) as $fileInfo)
    {
      if($fileInfo->isDot()) continue;

      if($fileInfo->isDir())
      {
        rmdir($fileInfo->getPathname());
      }
      else {
        if($fileInfo->getBasename() === 'fias_' . $this->databaseVersion . '.rar') continue;
        unlink($fileInfo->getPathname());
      }
    }
  }

  /**
   * Метод получает версию локальной базы данных. 
   * 
   * @param  ыtring $fileName Имя архива базы данных
   * 
   * @return integer
   */
  private function getLocalDatabaseVerion(string $fileName): int
  {
    return (int) strstr(substr($fileName, 5), '.', true);
  }

  /** @var string Путь до каталога DBF */
  private $pathDbfDirectory = '';

  /** @var integer Версия базы данных */
  private $databaseVersion = 0;

  /** @var string Полное название базы данных  */
  private $databaseName = '';

  /** @var string Дата формирования базы данных */
  private $databaseDateCreated = '';

  /** @var string Url Rar-архива всех данных в формате Dbf */
  private $urlAllDataDbf = '';

  /** @var string Url Rar-архива всех данных в формате Xml  */
  private $urlAllDataXml = '';

  /** @var string Url Rar-архива обновлений в формате Dbf */
  private $urlDeltaDataDbf = '';

  /** @var string Url Rar-архива обновлений в формате Xml  */
  private $urlDeltaDataXml = '';

  /** @var string Url Arj-архива КЛАДР  */
  private $urlKladrArj = '';

  /** @var string Url 7z-архива КЛАДР  */
  private $urlKladr7z = '';

  /** @var string URL до архива */
  private $urlArchive = '';

}
