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

use DataURI\Exception\InvalidData,
    DataUri\Data;

/**
 *
 * @author      
 * @license     http://opensource.org/licenses/MIT MIT
 */
class Parser
{

  /**
   * DATA URI SCHEME REGEXP
   * offset #1 MimeType 
   * offset #2 Paremeters
   * offset #3 Datas
   */
  const DATA_URI_REGEXP = '/data:([a-zA-Z-\/]+)([a-zA-Z0-9-_;=]+)?,(.*)/';
  
  /**
   * Parse a data URI 
   * 
   * @param string $dataUri A data URI
   * @return \DataUri\Data
   * @throws InvalidArgumentException
   * @throws InvalidData
   */
  public static function parse($dataUri, $len = Data::TAGLEN, $strict = false)
  {
    $dataParams = $matches = array();

    if ( ! preg_match(self::DATA_URI_REGEXP, $dataUri, $matches))
    {
      throw new \InvalidArgumentException();
    }

    $base64 = false;
    
    $mimeType = $matches[1];
    $params = $matches[2];
    $rawData = $matches[3];

    if ("" !== $params)
    {
      foreach (explode(';', $params) as $param)
      {
        if (strstr($param, '='))
        {
          $param = explode('=', $param);
          $dataParams[array_shift($param)] = array_pop($param);
        }
        elseif ($param === Data::BASE_64)
        {
          $base64 = true;
        }
      }
    }
    
    if (($base64 && ! $rawData = base64_decode($rawData, $strict)))
    {
      throw new InvalidData();
    }
    
    $dataURI = new Data($rawData, $mimeType, $dataParams, $len);
    $dataURI->setBase64Encoded($base64);
    
    return $dataURI;
  }
}

