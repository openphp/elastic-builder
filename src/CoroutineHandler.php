<?php

namespace Openphp\ElasticBuilder;


use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Exception\RingException;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;

/**
 * Http handler that uses Swoole Coroutine as a transport layer.
 */
class CoroutineHandler
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * @param $request
     * @return array|CompletedFutureArray
     */
    public function __invoke($request)
    {
        $method       = $request['http_method'] ?? 'GET';
        $scheme       = $request['scheme'] ?? 'http';
        $ssl          = $scheme === 'https';
        $uri          = $request['uri'] ?? '/';
        $body         = $request['body'] ?? '';
        $effectiveUrl = Core::url($request);
        $params       = parse_url($effectiveUrl);
        $host         = $params['host'];
        if (!isset($params['port'])) {
            $params['port'] = $ssl ? 443 : 80;
        }
        $port = $params['port'];
        $path = $params['path'] ?? '/';
        if (isset($params['query']) && is_string($params['query'])) {
            $path .= '?' . $params['query'];
        }

        $client = new Client($host, $port, $ssl);
        $client->setMethod($method);
        $client->setData($body);

        // 初始化Headers
        $this->initHeaders($client, $request);
        $settings = $this->getSettings($this->options);

        // 设置客户端参数
        if (!empty($settings)) {
            $client->set($settings);
        }

        $btime = microtime(true);
        $this->execute($client, $path);

        $ex = $this->checkStatusCode($client, $request);
        if ($ex !== true) {
            return [
                'status'  => null,
                'reason'  => null,
                'headers' => [],
                'error'   => $ex,
            ];
        }

        return $this->getResponse($client, $btime, $effectiveUrl);
    }

    /**
     * @param Client $client
     * @param $path
     * @return void
     */
    protected function execute(Client $client, $path)
    {
        $client->execute($path);
    }

    /**
     * @param $options
     * @return array
     */
    protected function getSettings($options): array
    {
        $settings = [];
        if (isset($options['delay'])) {
            Coroutine::sleep((float)$options['delay'] / 1000);
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $settings['timeout'] = $options['timeout'];
        }

        return $settings;
    }

    /**
     * @param Client $client
     * @param $request
     * @return void
     */
    protected function initHeaders(Client $client, $request)
    {
        $headers = [];
        foreach ($request['headers'] ?? [] as $name => $value) {
            $headers[$name] = implode(',', $value);
        }

        $clientConfig = $request['client']['curl'] ?? [];
        if (isset($clientConfig[CURLOPT_USERPWD])) {
            $userInfo                 = $clientConfig[CURLOPT_USERPWD];
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($userInfo));
        }
        unset($headers['Content-Length']);
        $client->setHeaders($headers);
    }

    /**
     * @param Client $client
     * @param $btime
     * @param $effectiveUrl
     * @return CompletedFutureArray
     */
    protected function getResponse(Client $client, $btime, $effectiveUrl)
    {
        return new CompletedFutureArray([
            'transfer_stats' => [
                'total_time' => microtime(true) - $btime,
            ],
            'effective_url'  => $effectiveUrl,
            'headers'        => isset($client->headers) ? $client->headers : [],
            'status'         => $client->statusCode,
            'body'           => $this->getStream($client->body),
        ]);
    }

    /**
     * @param $client
     * @param $request
     * @return bool|RingException
     */
    protected function checkStatusCode($client, $request)
    {
        $statusCode = $client->statusCode;
        $errCode    = $client->errCode;

        if ($statusCode === -1) {
            return new RingException(sprintf('Connection timed out errCode=%s', $errCode));
        }
        if ($statusCode === -2) {
            return new RingException('Request timed out');
        }

        return true;
    }

    /**
     * @param string $resource
     * @return false|resource
     */
    protected function getStream(string $resource)
    {
        $stream = fopen('php://temp', 'r+');
        if ($resource !== '') {
            fwrite($stream, $resource);
            fseek($stream, 0);
        }
        return $stream;
    }
}