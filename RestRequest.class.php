<?php

/**
 * Project:     RestRequest Class
 * File:        RestRequest.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com 
 *
 * @link http://www.rebel-interactive.com/
 * @copyright 2009 REBEL INTERACTIVE, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @package BasecampPHPAPI
 * @version 1.0-dev
 */

/*
 * This class is a highly modified version of the Restful Request API
 * http://www.gen-x-design.com/archives/making-restful-requests-in-php/
 * Feel free to use it for other REST projects other than Basecamp!
 * This library currently supports XML and SimpleXML input/output, but
 * could easily be modified to support other formats such as JSON.
 */

class RestRequest
{
  protected $url;
  protected $verb;
  protected $requestBody;
  protected $requestLength;
  protected $username;
  protected $password;
  protected $contentType;
  protected $acceptType;
  protected $responseBody;
  protected $responseInfo;
  
  public function __construct ($url = null, $verb = 'GET', $requestBody = null)
  {
    $this->url            = $url;
    $this->verb           = $verb;
    $this->requestBody    = $requestBody;
    $this->requestLength  = 0;
    $this->username       = null;
    $this->password       = null;
    $this->contentType    = 'application/xml';
    $this->acceptType     = 'application/xml';
    $this->responseBody   = null;
    $this->responseInfo   = null;    
  }
  
  public function flush ()
  {
    $this->requestBody    = null;
    $this->requestLength  = 0;
    $this->verb        = 'GET';
    $this->responseBody    = null;
    $this->responseInfo    = null;
  }
  
  public function execute ()
  {
    $ch = curl_init();
    $this->setAuth($ch);
    
    try
    {
      switch (strtoupper($this->verb))
      {
        case 'GET':
          $this->executeGet($ch);
          break;
        case 'POST':
          $this->executePost($ch);
          break;
        case 'POSTFILE':
          $this->executePostFile($ch);
          break;
        case 'PUT':
          $this->executePut($ch);
          break;
        case 'DELETE':
          $this->executeDelete($ch);
          break;
        default:
          throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
      }
    }
    catch (InvalidArgumentException $e)
    {
      curl_close($ch);
      throw $e;
    }
    catch (Exception $e)
    {
      curl_close($ch);
      throw $e;
    }
    
  }
    
  protected function executeGet ($ch)
  {    
    $this->doExecute($ch);  
  }
  
  protected function executePost ($ch)
  {    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
    
    $this->doExecute($ch);  
  }

  protected function executePostFile ($ch)
  {    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/octet-stream'));
    curl_setopt($ch, CURLOPT_POST, 1);
    
    $this->doExecute($ch);      
  }
  
  protected function executePut ($ch)
  {
    $this->requestLength = strlen($this->requestBody);
    
    $fh = fopen('php://memory', 'rw');
    fwrite($fh, $this->requestBody);
    rewind($fh);
    
    curl_setopt($ch, CURLOPT_INFILE, $fh);
    curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
    
    $this->doExecute($ch);
    
    fclose($fh);
  }
  
  protected function executeDelete ($ch)
  {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
    
    $this->doExecute($ch);
  }
  
  protected function doExecute (&$curlHandle)
  {
    $this->setCurlOpts($curlHandle);
    $this->responseBody = curl_exec($curlHandle);
    $this->responseInfo = curl_getinfo($curlHandle);
    curl_close($curlHandle);
  }
  
  protected function setCurlOpts (&$curlHandle)
  {
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
    curl_setopt($curlHandle, CURLOPT_URL, $this->url);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandle, CURLOPT_HEADER, true);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, !preg_match("!^https!i",$this->url));
  }
  
  protected function setAuth (&$curlHandle)
  {
    if ($this->username !== null && $this->password !== null)
    {
      curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curlHandle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
    }
  }
  
  public function getAcceptType ()
  {
    return $this->acceptType;
  } 
  
  public function setAcceptType ($acceptType)
  {
    $this->acceptType = $acceptType;
  } 
  
  public function getPassword ()
  {
    return $this->password;
  } 
  
  public function setPassword ($password)
  {
    $this->password = $password;
  } 
  
  public function getResponseBody ()
  {
    return $this->responseBody;
  } 
  
  public function getResponseInfo ()
  {
    return $this->responseInfo;
  } 
  
  public function getUrl ()
  {
    return $this->url;
  } 
  
  public function setUrl ($url)
  {
    $this->url = $url;
  } 
  
  public function getUsername ()
  {
    return $this->username;
  } 
  
  public function setUsername ($username)
  {
    $this->username = $username;
  } 
  
  public function getVerb ()
  {
    return $this->verb;
  } 
  
  public function setVerb ($verb)
  {
    $this->verb = $verb;
  } 

  public function getRequestBody ()
  {
    return $this->requestBody;
  } 
  
  public function setRequestBody ($body)
  {
    $this->requestBody = $body;
  } 

}

