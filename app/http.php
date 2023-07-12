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

$user = '';

$server->on('request', function(Request $request, Response $response) use ($user) {
    Coroutine::create(function() use ($request, $response, $user){
        $uri = $request->server['request_uri'] ?? '';

        if($uri == '/hello') {
            $user = 'user1';
            sleep(15);
            $response->end($user);
            return;
        }
    
        if($uri == '/hello/world') {
            $user = 'user2';
            $response->end($user);
            return;
        }
        $response->header('content-type', 'text/plain');
        $response->end('Hello world\n');
    });
});

$server->start();