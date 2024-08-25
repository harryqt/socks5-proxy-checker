<?php

declare(strict_types=1);

$http = new Swoole\Http\Server('0.0.0.0', 9211);
$http->set(['worker_num' => swoole_cpu_num()]);

$http->on('Request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    if ($request->get === null || !isset($request->get['proxy']) || !isset($request->get['port'])) {
        $response->setStatusCode(400);
        $response->end();
        return;
    }

    $client = new Swoole\Coroutine\Http\Client('example.com', 80);
    $client->set([
        'timeout' => 3,
        'socks5_host' => $request->get['proxy'],
        'socks5_port' => $request->get['port'],
    ]);
    $client->setMethod('HEAD');
    $client->execute('/');
    $client->close();

    $response->setStatusCode($client->getStatusCode() === 200 ? 200 : 502);
    $response->end();
});

$http->start();
