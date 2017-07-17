<?php

namespace RunMyBusiness\Pardot;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Class Client.
 */
class Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config = [
        'response_format' => 'json',
        'base_uri'        => 'https://pi.pardot.com/api',
        'timeout'         => 5,
    ];

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $userKey;

    /**
     * @var
     */
    protected $apiKey;

    /**
     * Client constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->connection = new GuzzleClient($this->config);
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $userKey
     *
     * @return $this
     */
    public function setAuth($email, $password, $userKey)
    {
        $this->email = $email;
        $this->password = $password;
        $this->userKey = $userKey;

        return $this;
    }

    /**
     * @return $this
     */
    public function authenticate()
    {
        $result = $this->connection->get(
            $this->makeUri('login', null, [
                'email'    => $this->email,
                'password' => $this->password,
                'user_key' => $this->userKey,
                'format'   => $this->config['response_format'],
            ])
        );

        $this->apiKey = json_decode($result->getBody()->getContents(), true)['api_key'];

        return $this;
    }

    /**
     * @param string $objectType
     * @param int    $id
     * @param array  $options
     *
     * @return array
     */
    public function read($objectType, $id, array $options = [])
    {
        $options['id'] = $id;
        return $this->makeGetRequest(
          $this->makeUri($objectType, 'read', $this->makeFields($options))
        );
    }

    /**
     * @param string $objectType
     * @param int    $id
     * @param array  $options
     *
     * @return bool
     */
    public function delete($objectType, $id, array $options = [])
    {
        $options['id'] = $id;

        return $this->makeDeleteRequest(
            $this->makeUri($objectType, 'delete', $this->makeFields($options))
        );
    }

    /**
     * @param string $url
     *
     * @return array
     */
    protected function makeGetRequest($url)
    {
        $result = $this->connection->get($url);

        return (array) json_decode($result->getBody()->getContents(), true);
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    protected function makeDeleteRequest($url)
    {
        $result = $this->connection->delete($url);

        return $result->getStatusCode() == 204;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function makeFields(array $fields = [])
    {
        $fields['api_key'] = $this->apiKey;
        $fields['user_key'] = $this->userKey;
        $fields['output'] = 'full';
        $fields['format'] = $this->config['response_format'];

        return $fields;
    }

    /**
     * @param string $objectType
     * @param string $operation
     * @param array  $attr
     *
     * @return string
     */
    protected function makeUri($objectType, $operation = null, array $attr = [])
    {
        $uri = "/{$objectType}/version/4";

        if (!empty($operation)) {
            $uri .= "/do/{$operation}";
        }

        if (!empty($attr['id'])) {
            $uri .= "/id/{$attr['id']}";
            unset($attr['id']);
        }

        if (!empty($attr)) {
            $uri .= '?'.http_build_query($attr);
        }

        return $this->config['base_uri'].$uri;
    }
}
