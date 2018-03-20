<?php

namespace App;

/**
* 
*/
class WordpressClient
{
	private $endpoint;
	private $username;
	private $password;
	private $request;
    private $responseHeader = array();
    private $error;
    private $proxyConfig = false;
    private $authConfig = false;
    private $userAgent;

    private $_callbacks = array();
	
	function __construct($xmlrpcEndPoint = null, $username = null, $password = null, $logger = null)
    {
        $this->setCredentials($xmlrpcEndPoint, $username, $password);
        $this->userAgent = $this->getDefaultUserAgent();
    }

	public function onError($callback)
    {
        $this->_callbacks['error'][] = $callback;
    }

    public function onSending($callback)
    {
        $this->_callbacks['sending'][] = $callback;
    }

    function setCredentials($xmlrpcEndPoint, $username, $password)
    {
       
        $scheme = parse_url($xmlrpcEndPoint, PHP_URL_SCHEME);
        if (!$scheme) {
            $xmlrpcEndPoint = "http://{$xmlrpcEndPoint}";
        }
        
        $host = parse_url($xmlrpcEndPoint, PHP_URL_HOST);
        if (substr($host, -14) == '.wordpress.com') {
            $xmlrpcEndPoint = preg_replace('|http://|', 'https://', $xmlrpcEndPoint, 1);
        }
       
        $this->endPoint = $xmlrpcEndPoint;
        $this->username = $username;
        $this->password = $password;
    }

    function getEndPoint()
    {
        return $this->endPoint;
    }

    function getDefaultUserAgent(){
    	$phpVersion  = phpversion();
        $curlVersion = curl_version();
        return "XML-RPC client (hieu-le/wordpress-xmlrpc-client 2.4.0) PHP {$phpVersion} cUrl {$curlVersion['version']}";
    }

    function getUserAgent()
    {
        return $this->userAgent;
    }

    function setUserAgent($userAgent)
    {
        if ($userAgent) {
            $this->userAgent = $userAgent;
        } else {
            $this->userAgent = $this->getDefaultUserAgent();
        }
    }

    function getErrorMessage()
    {
        return $this->error;
    }

    function setProxy($proxyConfig)
    {
        if ($proxyConfig === false || is_array($proxyConfig)) {
            $this->proxyConfig = $proxyConfig;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . " only accept boolean 'false' or an array as parameter.");
        }
    }

    function getProxy()
    {
        return $this->proxyConfig;
    }

    function setAuth($authConfig)
    {
        if ($authConfig === false || is_array($authConfig)) {
            $this->authConfig = $authConfig;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . " only accept boolean 'false' or an array as parameter.");
        }
    }

     function getAuth()
    {
        return $this->authConfig;
    }

    function getPost($postId, array $fields = array())
    {
        if (empty($fields)) {
            $params = array(1, $this->username, $this->password, $postId);
        } else {
            $params = array(1, $this->username, $this->password, $postId, $fields);
        }
        return $this->sendRequest('wp.getPost', $params);
    }

    function getPosts(array $filters = array(), array $fields = array())
    {
        $params = array(1, $this->username, $this->password, $filters);
        if (!empty($fields)) {
            $params[] = $fields;
        }
        return $this->sendRequest('wp.getPosts', $params);
    }

    function newPost($title, $body, array $content = array())
    {
        $default                 = array(
            'post_type'   => 'post',
            'post_status' => 'publish',
        );
        $content                 = array_merge($default, $content);
        $content['post_title']   = $title;
        $content['post_content'] = $body;
        $params = array(1, $this->username, $this->password, $content);
        return $this->sendRequest('wp.newPost', $params);
    }

    function editPost($postId, array $content)
    {
        $params = array(1, $this->username, $this->password, $postId, $content);
        return $this->sendRequest('wp.editPost', $params);
    }

    function deletePost($postId)
    {
        $params = array(1, $this->username, $this->password, $postId);
        return $this->sendRequest('wp.deletePost', $params);
    }

    function getPostType($postTypeName, array $fields = array())
    {
        $params = array(1, $this->username, $this->password, $postTypeName, $fields);
        return $this->sendRequest('wp.getPostType', $params);
    }

    function getPostTypes(array $filter = array(), array $fields = array())
    {
        $params = array(1, $this->username, $this->password, $filter, $fields);
        return $this->sendRequest('wp.getPostTypes', $params);
    }

    function getPostFormats()
    {
        $params = array(1, $this->username, $this->password);
        return $this->sendRequest('wp.getPostFormats', $params);
    }

    function getPostStatusList()
    {
        $params = array(1, $this->username, $this->password);
        return $this->sendRequest('wp.getPostStatusList', $params);
    }

    function getTaxonomy($taxonomy)
    {
        $params = array(1, $this->username, $this->password, $taxonomy);
        return $this->sendRequest('wp.getTaxonomy', $params);
    }

    function getTaxonomies()
    {
        $params = array(1, $this->username, $this->password);
        return $this->sendRequest('wp.getTaxonomies', $params);
    }

    function getTerm($termId, $taxonomy)
    {
        $params = array(1, $this->username, $this->password, $taxonomy, $termId);
        return $this->sendRequest('wp.getTerm', $params);
    }

    function getTerms($taxonomy, array $filter = array())
    {
        $params = array(1, $this->username, $this->password, $taxonomy, $filter);
        return $this->sendRequest('wp.getTerms', $params);
    }	

    function newTerm($name, $taxomony, $slug = null, $description = null, $parentId = null)
    {
        $content = array(
            'name'     => $name,
            'taxonomy' => $taxomony,
        );
        if ($slug) {
            $content['slug'] = $slug;
        }
        if ($description) {
            $content['description'] = $description;
        }
        if ($parentId) {
            $content['parent'] = $parentId;
        }
        $params = array(1, $this->username, $this->password, $content);
        return $this->sendRequest('wp.newTerm', $params);
    }

    function editTerm($termId, $taxonomy, array $content = array())
    {
        $content['taxonomy'] = $taxonomy;
        $params              = array(1, $this->username, $this->password, $termId, $content);
        return $this->sendRequest('wp.editTerm', $params);
    }

    function deleteTerm($termId, $taxonomy)
    {
        $params = array(1, $this->username, $this->password, $taxonomy, $termId);
        return $this->sendRequest('wp.deleteTerm', $params);
    }

    function getMediaItem($itemId)
    {
        $params = array(1, $this->username, $this->password, $itemId);
        return $this->sendRequest('wp.getMediaItem', $params);
    }

    function getMediaLibrary(array $filter = array())
    {
        $params = array(1, $this->username, $this->password, $filter);
        return $this->sendRequest('wp.getMediaLibrary', $params);
    }

    function uploadFile($name, $mime, $bits, $overwrite = null, $postId = null)
    {
        xmlrpc_set_type($bits, 'base64');
        $struct = array(
            'name' => $name,
            'type' => $mime,
            'bits' => $bits,
        );
        if ($overwrite !== null) {
            $struct['overwrite'] = $overwrite;
        }
        if ($postId !== null) {
            $struct['post_id'] = (int)$postId;
        }
        $params = array(1, $this->username, $this->password, $struct);
        return $this->sendRequest('wp.uploadFile', $params);
    }

    function getCommentCount($postId)
    {
        $params = array(1, $this->username, $this->password, $postId);
        return $this->sendRequest('wp.getCommentCount', $params);
    }

    function getComment($commentId)
    {
        $params = array(1, $this->username, $this->password, $commentId);
        return $this->sendRequest('wp.getComment', $params);
    }

    function getComments(array $filter = array())
    {
        $params = array(1, $this->username, $this->password, $filter);
        return $this->sendRequest('wp.getComments', $params);
    }

    function newComment($post_id, array $comment)
    {
        $params = array(1, $this->username, $this->password, $post_id, $comment);
        return $this->sendRequest('wp.newComment', $params);
    }

    function editComment($commentId, array $comment)
    {
        $params = array(1, $this->username, $this->password, $commentId, $comment);
        return $this->sendRequest('wp.editComment', $params);
    }

    function deleteComment($commentId)
    {
        $params = array(1, $this->username, $this->password, $commentId);
        return $this->sendRequest('wp.deleteComment', $params);
    }

    function getCommentStatusList()
    {
        $params = array(1, $this->username, $this->password);
        return $this->sendRequest('wp.getCommentStatusList', $params);
    }

    function getOptions(array $options = array())
    {
        if (empty($options)) {
            $params = array(1, $this->username, $this->password);
        } else {
            $params = array(1, $this->username, $this->password, $options);
        }
        return $this->sendRequest('wp.getOptions', $params);
    }

    function setOptions(array $options)
    {
        $params = array(1, $this->username, $this->password, $options);
        return $this->sendRequest('wp.setOptions', $params);
    }

    function getUsersBlogs()
    {
        $params = array($this->username, $this->password);
        return $this->sendRequest('wp.getUsersBlogs', $params);
    }

    function getUser($userId, array $fields = array())
    {
        $params = array(1, $this->username, $this->password, $userId);
        if (!empty($fields)) {
            $params[] = $fields;
        }
        return $this->sendRequest('wp.getUser', $params);
    }

     function getUsers(array $filters = array(), array $fields = array())
    {
        $params = array(1, $this->username, $this->password, $filters);
        if (!empty($fields)) {
            $params[] = $fields;
        }
        return $this->sendRequest('wp.getUsers', $params);
    }

    function getProfile(array $fields = array())
    {
        $params = array(1, $this->username, $this->password);
        if (!empty($fields)) {
            $params[] = $fields;
        }
        return $this->sendRequest('wp.getProfile', $params);
    }

    function editProfile(array $content)
    {
        $params = array(1, $this->username, $this->password, $content);
        return $this->sendRequest('wp.editProfile', $params);
    }

    public function callCustomMethod($method, $params)
    {
        return $this->sendRequest($method, $params);
    }

    public function createXMLRPCDateTime($datetime)
    {
        $value = $datetime->format('Ymd\TH:i:sO');
        xmlrpc_set_type($value, 'datetime');
        return $value;
    }
    protected function performRequest()
    {
        if (function_exists('curl_init')) {
            return $this->requestWithCurl();
        } else {
            return $this->requestWithFile();
        }
    }
    protected function getRequest()
    {
        return $this->request;
    }
    private function sendRequest($method, $params)
    {
        if (!$this->endPoint) {
            $this->error = "Invalid endpoint " . json_encode(array(
                    'endpoint' => $this->endPoint,
                    'username' => $this->username,
                    'password' => $this->password,
                ));
            $this->logError();
            throw new \Exception($this->error);
        }
        $this->responseHeader = array();

        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            $this->setXmlrpcType($params);
        }
        $this->request = xmlrpc_encode_request($method, $params,
            array('encoding' => 'UTF-8', 'escaping' => 'markup', 'version' => 'xmlrpc'));
        $body = "";

        $callbacks = $this->getCallback('sending');
        $event     = array(
            'event'    => 'sending',
            'endpoint' => $this->endPoint,
            'username' => $this->username,
            'password' => $this->password,
            'method'   => $method,
            'params'   => $params,
            'request'  => $this->request,
            'proxy'    => $this->proxyConfig,
            'auth'     => $this->authConfig,
        );
        foreach ($callbacks as $callback) {
            call_user_func($callback, $event);
        }
        $body     = $this->performRequest();
        $response = xmlrpc_decode($body, 'UTF-8');
        if (is_array($response) && xmlrpc_is_fault($response)) {
            $this->error = ("xmlrpc: {$response['faultString']} ({$response['faultCode']})");
            $this->logError();
            throw new Exception\XmlrpcException($response['faultString'], $response['faultCode']);
        }
        return $response;
    }

    private function setXmlrpcType(&$array)
    {
        foreach ($array as $key => $element) {
            if (is_a($element, '\DateTime')) {
                $array[$key] = $element->format("Ymd\TH:i:sO");
                xmlrpc_set_type($array[$key], 'datetime');
            } elseif (is_array($element)) {
                $this->setXmlrpcType($array[$key]);
            }
        }
    }
    private function requestWithCurl()
    {
        $ch = curl_init($this->endPoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        if ($this->proxyConfig != false) {
            if (isset($this->proxyConfig['proxy_ip'])) {
                curl_setopt($ch, CURLOPT_PROXY, $this->proxyConfig['proxy_ip']);
            }
            if (isset($this->proxyConfig['proxy_port'])) {
                curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyConfig['proxy_port']);
            }
            if (isset($this->proxyConfig['proxy_user']) && isset($this->proxyConfig['proxy_pass'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD,
                    "{$this->proxyConfig['proxy_user']}:{$this->proxyConfig['proxy_pass']}");
            }
            if (isset($this->proxyConfig['proxy_mode'])) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, $this->proxyConfig['proxy_mode']);
            }
        }
        if ($this->authConfig) {
            if (isset($this->authConfig['auth_user']) && isset($this->authConfig['auth_pass'])) {
                curl_setopt($ch, CURLOPT_USERPWD,
                    "{$this->authConfig['auth_user']}:{$this->authConfig['auth_pass']}");
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            }
            if (isset($this->authConfig['auth_mode'])) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, $this->authConfig['auth_mode']);
            }
        }
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $message     = curl_error($ch);
            $code        = curl_errno($ch);
            $this->error = "curl: {$message} ({$code})";
            $this->logError();
            curl_close($ch);
            throw new Exception\NetworkException($message, $code);
        }
        $httpStatusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpStatusCode >= 400) {
            $message     = $response;
            $code        = $httpStatusCode;
            $this->error = "http: {$message} ({$code})";
            $this->logError();
            curl_close($ch);
            throw new Exception\NetworkException($message, $code);
        }
        curl_close($ch);
        return $response;
    }
    private function requestWithFile()
    {
        $contextOptions = array(
            'http' => array(
                'method'     => "POST",
                'user_agent' => $this->userAgent,
                'header'     => "Content-Type: text/xml\r\n",
                'content'    => $this->request,
            ),
        );
        if ($this->proxyConfig != false) {
            if (isset($this->proxyConfig['proxy_ip']) && isset($this->proxyConfig['proxy_port'])) {
                $contextOptions['http']['proxy']           = "tcp://{$this->proxyConfig['proxy_ip']}:{$this->proxyConfig['proxy_port']}";
                $contextOptions['http']['request_fulluri'] = true;
            }
            if (isset($this->proxyConfig['proxy_user']) && isset($this->proxyConfig['proxy_pass'])) {
                $auth = base64_encode("{$this->proxyConfig['proxy_user']}:{$this->proxyConfig['proxy_pass']}");
                $contextOptions['http']['header'] .= "Proxy-Authorization: Basic {$auth}\r\n";
            }
            if (isset($this->proxyConfig['proxy_mode'])) {
                throw new \InvalidArgumentException('Cannot use NTLM proxy authorization without cURL extension');
            }
        }
        if ($this->authConfig) {
            if (isset($this->authConfig['auth_user']) && isset($this->authConfig['auth_pass'])) {
                $auth = base64_encode("{$this->authConfig['auth_user']}:{$this->authConfig['auth_pass']}");
                $contextOptions['http']['header'] .= "Authorization: Basic {$auth}\r\n";
            }
            if (isset($this->authConfig['auth_mode'])) {
                throw new \InvalidArgumentException('Cannot use other authentication method without cURL extension');
            }
        }
        $context = stream_context_create($contextOptions);
        $http_response_header = array();
        try {
            $file = @file_get_contents($this->endPoint, false, $context);
            if ($file === false) {
                $error       = error_get_last();
                $error       = $error ? trim($error['message']) : "error";
                $this->error = "file_get_contents: {$error}";
                $this->logError();
                throw new Exception\NetworkException($error, 127);
            }
        } catch (\Exception $ex) {
            $this->error = ("file_get_contents: {$ex->getMessage()} ({$ex->getCode()})");
            $this->logError();
            throw new Exception\NetworkException($ex->getMessage(), $ex->getCode());
        }
        return $file;
    }
    private function logError()
    {
        $callbacks = $this->getCallback('error');
        $event = array(
            'event'    => 'error',
            'endpoint' => $this->endPoint,
            'request'  => $this->request,
            'proxy'    => $this->proxyConfig,
            'auth'     => $this->authConfig,
        );
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this->error, $event);
        }
    }
    private function getCallback($name)
    {
        $callbacks = array();
        if (isset($this->_callbacks[$name]) && is_array($this->_callbacks[$name])) {
            $callbacks = $this->_callbacks[$name];
        }
        return $callbacks;
    }

}


