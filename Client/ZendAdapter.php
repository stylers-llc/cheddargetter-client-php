<?php

/**
 * CheddarGetter
 * @category CheddarGetter
 * @package CheddarGetter
 * @author Marc Guyer <marc@cheddargetter.com>
 */
/**
 * Adapter implementation based on Zend_Http_Client for requesting the
 * CheddarGetter service
 * @category CheddarGetter
 * @package CheddarGetter
 * @author Marc Guyer <marc@cheddargetter.com>
 * @author Christophe Coevoet <stof@notk.org>
 * @example example/example.php
 */

class CheddarGetter_Client_ZendAdapter implements CheddarGetter_Client_AdapterInterface
{

    /**
     * Http client object
     * @var Zend_Http_Client|null
     */
    protected $_client;

    /**
     * Constructor
     *
     * Accepts a Zend_Http_Client argument enabling the implementer to use
     * a custom client (custom stream context, etc). Unless specified, a
     * default client is used with some common stream context options
     *
     * @param Zend_Http_Client $client
     * @throws CheddarGetter_Client_Exception Throws an exception
     * if Zend_Http_Client is not available.
     */
    public function __construct(
        Zend_Http_Client $client = null
    ) {
        if (!class_exists('Zend_Http_Client', false)) {
            throw new CheddarGetter_Client_Exception(
                'Zend_Http_Client is not available.',
                CheddarGetter_Client_Exception::USAGE_INVALID
            );
        }

        // default client
        if (!$client) {
            $userAgent = (isset($_SERVER['SERVER_NAME'])) ?
            $_SERVER['SERVER_NAME'] . ' - CheddarGetter_Client PHP' :
            'CheddarGetter_Client PHP';

            // socket adapter with custom stream context
            $options = array(
                'http' => array(
                    'follow_location' => 0, // do not follow location header
                    'max_redirects' => 0, // do not follow redirects
                    'timeout' => 100, // read timeout seconds
                    'user_agent' => $userAgent,
                ),
                'ssl' => array(
                    'verify_peer' => true,
                    'allow_self_signed' => false,
                )
            );

            $adapter = new Zend_Http_Client_Adapter_Socket();
            $adapter->setStreamContext($options);

            $client = new Zend_Http_Client(
                null,
                array(
                    'userAgent' => $options['http']['user_agent'],
                    'timeout'        => 120 // connection timeout
                )
            );

            $client->setAdapter($adapter);
        }

        $this->_client = $client;
    }

    /**
     * Execute CheddarGetter API request
     *
     * @param string $url Url to the API action
     * @param string $username Username
     * @param string $password Password
     * @param array|null $args HTTP post key value pairs
     * @return string Body of the response from the CheddarGetter API
     * @throws Zend_Http_Client_Exception A Zend_Http_Client_Exception may
     * be thrown under a number of conditions but most likely if the tcp socket
     * fails to connect.
     */
    public function request($url, $username, $password, array $args = null)
    {

        // reset
        $this->_client->setUri($url);
        $this->_client->resetParameters();
        $this->_client->setMethod(Zend_Http_Client::GET);

        $this->_client->setAuth($username, $password);

        if ($args) {
            $this->_client->setMethod(Zend_Http_Client::POST);
            $this->_client->setParameterPost($args);
        }

        $response = $this->_client->request();

        return $response->getBody();
    }

    /**
     * Get the http client object
     * @return null|Zend_Http_Client
     */
    public function getClient()
    {
        return $this->_client;
    }
}
