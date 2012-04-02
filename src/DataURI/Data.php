<?php

/**
 * Copyright (c) 2012 Nicolas Le Goff
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace DataURI;

use DataURI\Exception\FileExists,
    DataURI\Exception\FileNotFound,
    DataURI\Exception\TooLongData,
    DataURI\Exception\InvalidData;
use Symfony\Component\HttpFoundation\File\File as SymfoFile;

/**
 *
 * @author      
 * @license     http://opensource.org/licenses/MIT MIT
 */
class Data
{
  /**
   * The LITLEN (1024) limits the number of characters which can appear in 
   * a single attribute value literal
   */

  const LITLEN = 0;

  /**
   * The ATTSPLEN (2100) limits the sum of all
   * lengths of all attribute value specifications which appear in a tag
   */
  const ATTSPLEN = 1;

  /**
   * The TAGLEN (2100) limits the overall length of a tag
   */
  const TAGLEN = 2;

  /**
   * ATTS_TAG_LIMIT is the length limit allowed for TAGLEN & ATTSPLEN DataURi
   */
  const ATTS_TAG_LIMIT = 2100;

  /**
   * LIT_LIMIT is the length limit allowed for LITLEN DataURi
   */
  const LIT_LIMIT = 1024;

  /**
   * Base64 encode prefix
   */
  const BASE_64 = 'base64';

  /**
   * File data
   * @var string 
   */
  protected $data;

  /**
   * File mime type
   * @var string 
   */
  protected $mimeType;

  /**
   * Parameters provided in DataURI
   * @var Array 
   */
  protected $parameters;

  /**
   * Tell whether data is encoded 
   * @var boolean 
   */
  protected $base64Encoded = false;

  public function __construct($data, $mimeType = null, Array $parameters = array(), $length = self::TAGLEN)
  {
    $this->data = $data;
    $this->mimeType = $mimeType;
    $this->parameters = $parameters;

    $this->init($length);
  }

  /**
   * File Datas
   * @return string 
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * File mime type
   * @return string 
   */
  public function getMimeType()
  {
    return $this->mimeType;
  }

  /**
   * File parameters
   * @return string 
   */
  public function getParameters()
  {
    return $this->parameters;
  }

  /**
   * Data is base64 encoded
   * @return boolean 
   */
  public function isBase64Encoded()
  {
    return $this->base64Encoded;
  }

  /**
   * Set if Data is base64 encoded
   * @param boolean $base64Encoded
   * @return \DataURI\Data 
   */
  public function setBase64Encoded($base64Encoded)
  {
    $this->base64Encoded = $base64Encoded;
    return $this;
  }

  /**
   * Add a parameters to the DataURi
   * 
   * @param string $paramName
   * @param string $paramValue
   * @return \DataURI\File 
   */
  public function addParameters($paramName, $paramValue)
  {
    $this->parameters[$paramName] = $paramValue;

    return $this;
  }

  /**
   * Write datas to the specified file
   * 
   * @param string $pathfile File to be written
   * @param Boolean $override Datas to write
   * @return \Symfony\Component\HttpFoundation\File\File
   * @throws FileNotFound
   * @throws FileExists 
   */
  public function write($pathfile, $override = false)
  {
    if ( ! file_exists($pathfile))
    {
      throw new FileNotFound();
    }

    file_put_contents($pathfile, $this->data, $override ? 0 : FILE_APPEND);

    return new SymfoFile($pathfile);
  }

  /**
   * Get a new instance of DataUri\File from SplFileInfo object
   * 
   * @param \SplFileInfo $file
   * @return \DataURI\Data
   */
  public static function buildFromFile($file, $base64 = false, $len = Data::TAGLEN)
  {
    if ( ! $file instanceof SymfoFile)
    {
      $file = new SymfoFile($file);
    }

    $data = file_get_contents($file->getPathname());
    
    if ($base64 && ! $data = base64_encode($data))
    {
      throw new InvalidData();
    }
    
    $dataURI = new static($data, $file->getMimeType(), array(), $len);
    $dataURI->setBase64Encoded($base64);
    
    return $dataURI;
  }

  /**
   * Contructor initialization
   * 
   * @return void 
   */
  private function init($length)
  {
    if ($length === self::LITLEN && strlen($this->data) > self::LIT_LIMIT)
    {
      throw new TooLongData();
    }
    elseif (strlen($this->data) > self::ATTS_TAG_LIMIT)
    {
      throw new TooLongData();
    }

    if (null === $this->mimeType)
    {
      $this->mimeType = 'text/plain';
      $this->addParameters('charset', 'US-ASCII');
    }

    return;
  }

}
