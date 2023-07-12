<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Utopia\CLI\Console;
use Swoole\Constant;
use Swoole\Coroutine;

$server = new Server('0.0.0.0', 80, SWOOLE_PROCESS);

$server->set([
    Constant::OPTION_WORKER_NUM => '1',
    Constant::OPTION_ENABLE_COROUTINE => true
]);

$server->on('start', function(Server $server) {
    Console::success('Server started at http://0.0.0.0:9501');
});

$server->on('workerStart',function($server, $workerId){
    global $argv;

    Console::info("Worker " . ++$workerId . ' started');
});

$user = [];

$server->on('request', function(Request $request, Response $response) use (&$user) {
    $uri = $request->server['request_uri'] ?? '';

    var_dump(Coroutine::getCid());

    if($uri == '/sleep') {
        $user[Coroutine::getCid()] = 'user1';
        //sleep(15);
        Coroutine::sleep(15);
        $response->end(time().'|'.$user[Coroutine::getCid()]);
        return;
    }

    if($uri == '/nosleep') {
        $user[Coroutine::getCid()] = 'user2';
        $response->end(time().'|'.$user[Coroutine::getCid()]);
        return;
    }
    $response->header('content-type', 'text/plain');
    $response->end('Hello world\n');
});

$server->start();