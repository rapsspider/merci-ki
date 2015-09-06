<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) 2005-2015, Cake Software Foundation, Inc.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software
 *
 *
 *
 * This class was done using the CakeResponse.
 * @see http://api.cakephp.org/2.0/source-class-CakeResponse.html
 */

namespace MerciKI\Network;

use MerciKI\Config;
use MerciKI\Exception\MerciKIException;

/**
 * Cake Response is responsible for managing the response text, status and headers of a HTTP response.
 *
 * By default controllers will use this class to render their response. If you are going to use
 * a custom response class it should subclass this object in order to ensure compatibility.
 *
 */
class Response {

    /**
     * Holds HTTP response statuses
     *
     * @var array
     */
    protected $_statusCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'Unsupported Version'
    );

    /**
     * Holds type key to mime type mappings for known mime types.
     *
     * @var array
     */
    protected $_mimeTypes = array(
        'html' => array('text/html', '*/*'),
        'json' => 'application/json',
        'xml' => array('application/xml', 'text/xml'),
        'javascript' => 'application/javascript',
        'form' => 'application/x-www-form-urlencoded',
        'file' => 'multipart/form-data',
        'xhtml' => array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
    );

    /**
     * Protocol header to send to the client
     *
     * @var string
     */
    protected $_protocole = 'HTTP/1.1';

    /**
     * Status code to send to the client
     *
     * @var int
     */
    protected $_status = 200;

    /**
     * Content type to send. This can be an 'extension' that will be transformed using the $_mimetypes array
     * or a complete mime-type
     *
     * @var int
     */
    protected $_contentType = 'text/html';

    /**
     * Buffer list of headers
     *
     * @var array
     */
    protected $_headers = array();

    /**
     * Buffer string for response message
     *
     * @var string
     */
    protected $_body = null;

    /**
     * The charset the response body is encoded with
     *
     * @var string
     */
    protected $_charset = 'UTF-8';

    /**
     * Constructor
     *
     * @param array $options list of parameters to setup the response. Possible values are:
     *  - body: the response text that should be sent to the client
     *  - statusCodes: additional allowable response codes
     *  - status: the HTTP status code to respond with
     *  - charset: the charset for the response body
     */
    public function __construct(array $options = array()) {
        if (isset($options['body'])) {
            $this->body($options['body']);
        }
        if (isset($options['statusCodes'])) {
            $this->httpCodes($options['statusCodes']);
        }
        if (isset($options['status'])) {
            $this->statusCode($options['status']);
        }
        if (!isset($options['charset'])) {
            $options['charset'] = Config::$encoding;
        }
        $this->charset($options['charset']);
    }

    /**
     * Sends the complete response to the client including headers and message body.
     * Will echo out the content in the response body.
     *
     * @return void
     */
    public function send() {
        // If a redirect must be done and the status code didn't change.
        if (isset($this->_headers['Location']) && $this->_status === 200) {
            $this->statusCode(302);
        }

        // It takes the message associated to this status code.
        $codeMessage = $this->_statusCodes[$this->_status];

        // It sends header and sets the content to null if the status code is 304 or 204.
        $this->_sendHeader("{$this->_protocole} {$this->_status} {$codeMessage}");
        $this->_setContent();

        // It sets the content type.
        $this->_setContentType();

        // It sends all the header of this response.
        foreach ($this->_headers as $header => $values) {
            foreach ((array)$values as $value) {
                $this->_sendHeader($header, $value);
            }
        }

        // It sends the body of the response.
        $this->_sendContent($this->_body);
        

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Formats the Content-Type header based on the configured contentType and charset
     * the charset will only be set in the header if the response is of type text/*
     *
     * @return void
     */
    protected function _setContentType() {
        if (in_array($this->_status, array(304, 204))) {
            return;
        }
        $listeBlanche = array(
            'application/javascript', 'application/json', 'application/xml', 'application/rss+xml'
        );

        $charset = false;
        if ($this->_charset &&
            (strpos($this->_contentType, 'text/') === 0 || in_array($this->_contentType, $listeBlanche))
        ) {
            $charset = true;
        }

        if ($charset) {
            $this->header('Content-Type', "{$this->_contentType}; charset={$this->_charset}");
        } else {
            $this->header('Content-Type', "{$this->_contentType}");
        }
    }

    /**
     * Sets the response body to an empty text if the status code is 204 or 304
     *
     * @return void
     */
    protected function _setContent() {
        if (in_array($this->_status, array(304, 204))) {
            $this->body('');
        }
    }

    /**
     * Sends a header to the client.
     *
     * @param string $name the header name
     * @param string|null $value the header value
     * @return void
     */
    protected function _sendHeader($nom, $value = null) {
        if (!headers_sent()) {
            if ($value === null) {
                header($nom);
            } else {
                header("{$nom}: {$value}");
            }
        }
    }

    /**
     * Sends a content string to the client.
     *
     * @param string $content string to send as response body
     * @return void
     */
    protected function _sendContent($content) {
        echo $content;
    }

    /**
     * Buffers a header string to be sent
     * Returns the complete list of buffered headers
     *
     * ### Single header
     * e.g `header('Location', 'http://example.com');`
     *
     * ### Multiple headers
     * e.g `header(array('Location' => 'http://example.com', 'X-Extra' => 'My header'));`
     *
     * ### String header
     * e.g `header('WWW-Authenticate: Negotiate');`
     *
     * ### Array of string headers
     * e.g `header(array('WWW-Authenticate: Negotiate', 'Content-type: application/pdf'));`
     *
     * Multiple calls for setting the same header nom will have the same effect as setting the header once
     * with the last value sent for it
     *  e.g `header('WWW-Authenticate: Negotiate'); header('WWW-Authenticate: Not-Negotiate');`
     * will have the same effect as only doing `header('WWW-Authenticate: Not-Negotiate');`
     *
     * @param string|array $header An array of header strings or a single header string
     *    - an associative array of "header nom" => "header value" is also accepted
     *    - an array of string headers is also accepted
     * @param string|array $value The header value(s)
     * @return array list of headers to be sent
     */
    public function header($header = null, $value = null) {
        if ($header === null) {
            return $this->_headers;
        }
        $headers = is_array($header) ? $header : array($header => $value);
        foreach ($headers as $header => $value) {
            if (is_numeric($header)) {
                list($header, $value) = array($value, null);
            }
            if ($value === null) {
                list($header, $value) = explode(':', $header, 2);
            }
            $this->_headers[$header] = is_array($value) ? array_map('trim', $value) : trim($value);
        }
        return $this->_headers;
    }

    /**
     * Accessor for the location header.
     *
     * Get/Set the Location header value.
     *
     * @param null|string $url Either null to get the current location, or a string to set one.
     * @return string|null When setting the location null will be returned. When reading the location
     *    a string of the current location header value (if any) will be returned.
     */
    public function location($url = null) {
        if ($url === null) {
            $headers = $this->header();
            return isset($headers['Location']) ? $headers['Location'] : null;
        }
        $this->header('Location', $url);
        $this->statusCode(302);
        return null;
    }

    /**
     * Buffers the response message to be sent
     * if $content is null the current buffer is returned
     *
     * @param string|null $content the string message to be sent
     * @return string Current message buffer if $content param is passed as null
     */
    public function body($content = null) {
        if ($content === null) {
            return $this->_body;
        }
        return $this->_body = $content;
    }

    /**
     * Sets the HTTP status code to be sent
     * if $code is null the current code is returned
     *
     * @param int|null $code the HTTP status code
     * @return int Current status code
     * @throws \InvalidArgumentException When an unknown status code is reached.
     */
    public function statusCode($code = null) {
        if ($code === null) {
            return $this->_status;
        }
        if (!isset($this->_statusCodes[$code])) {
            throw new MerciKIException('Unknown status code');
        }
        return $this->_status = $code;
    }

    /**
     * Queries & sets valid HTTP response codes & messages.
     *
     * @param int|array $code If $code is an integer, then the corresponding code/message is
     *        returned if it exists, null if it does not exist. If $code is an array, then the
     *        keys are used as codes and the values as messages to add to the default HTTP
     *        codes. The codes must be integers greater than 99 and less than 1000. Keep in
     *        mind that the HTTP specification outlines that status codes begin with a digit
     *        between 1 and 5, which defines the class of response the clinkt is to expect.
     *        Example:
     *
     *        httpCodes(404); // returns array(404 => 'Not Found')
     *
     *        httpCodes(array(
     *            381 => 'Unicorn Moved',
     *            555 => 'Unexpected Minotaur'
     *        )); // sets these new values, and returns true
     *
     *        httpCodes(array(
     *            0 => 'Nothing Here',
     *            -1 => 'Reverse Infinity',
     *            12345 => 'Universal Password',
     *            'Hello' => 'World'
     *        )); // throws an exception due to invalid codes
     *
     *        For more on HTTP status codes see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6.1
     *
     * @return mixed associative array of the HTTP codes as keys, and the message
     *    strings as values, or null of the given $code does not exist.
     * @throws CakeException If an attempt is made to add an invalid status code
     */
    public function httpCodes($code = null) {
        if (empty($code)) {
            return $this->_statusCodes;
        }
        if (is_array($code)) {
            $codes = array_keys($code);
            $min = min($codes);
            if (!is_int($min) || $min < 100 || max($codes) > 999) {
                throw new CakeException(__d('cake_dev', 'Invalid status code'));
            }
            $this->_statusCodes = $code + $this->_statusCodes;
            return true;
        }
        if (!isset($this->_statusCodes[$code])) {
            return null;
        }
        return array($code => $this->_statusCodes[$code]);
    }

    /**
     * Sets the response content type. It can be either a file extension
     * which will be mapped internally to a mime-type or a string representing a mime-type
     * if $contentType is null the current content type is returned
     * if $contentType is an associative array, content type definitions will be stored/replaced
     *
     * ### Setting the content type
     *
     * e.g `type('jpg');`
     *
     * ### Returning the current content type
     *
     * e.g `type();`
     *
     * ### Storing content type definitions
     *
     * e.g `type(array('keynote' => 'application/keynote', 'bat' => 'application/bat'));`
     *
     * ### Replacing a content type definition
     *
     * e.g `type(array('jpg' => 'text/plain'));`
     *
     * @param string $contentType Content type key.
     * @return mixed current content type or false if supplied an invalid content type
     */
    public function type($contentType = null) {
        if ($contentType === null) {
            return $this->_contentType;
        }
        if (is_array($contentType)) {
            foreach ($contentType as $type => $definition) {
                $this->_mimeTypes[$type] = $definition;
            }
            return $this->_contentType;
        }
        if (isset($this->_mimeTypes[$contentType])) {
            $contentType = $this->_mimeTypes[$contentType];
            $contentType = is_array($contentType) ? current($contentType) : $contentType;
        }
        if (strpos($contentType, '/') === false) {
            return false;
        }
        return $this->_contentType = $contentType;
    }

    /**
     * Returns the mime type definition for an alias
     *
     * e.g `getMimeType('pdf'); // returns 'application/pdf'`
     *
     * @param string $alias the content type alias to map
     * @return mixed string mapped mime type or false if $alias is not mapped
     */
    public function getMimeType($alias) {
        if (isset($this->_mimeTypes[$alias])) {
            return $this->_mimeTypes[$alias];
        }
        return false;
    }

    /**
     * Maps a content-type back to an alias
     *
     * e.g `mapType('application/pdf'); // returns 'pdf'`
     *
     * @param string|array $ctype Either a string content type to map, or an array of types.
     * @return mixed Aliases for the types provided.
     */
    public function mapType($ctype) {
        if (is_array($ctype)) {
            return array_map(array($this, 'mapType'), $ctype);
        }

        foreach ($this->_mimeTypes as $alias => $types) {
            if (in_array($ctype, (array)$types)) {
                return $alias;
            }
        }
        return null;
    }

    /**
     * Sets the response charset
     * if $charset is null the current charset is returned
     *
     * @param string|null $charset Character set string.
     * @return string Current charset
     */
    public function charset($charset = null) {
        if ($charset === null) {
            return $this->_charset;
        }
        return $this->_charset = $charset;
    }

    /**
     * Convert this response as a String.
     * Headers aren't sent.
     *
     * @return string
     */
    public function __toString() {
        return (string)$this->_body;
    }

}
