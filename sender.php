<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$listenerName = 'dummy_sender';
$connection = new AMQPStreamConnection('localhost', 5672, 'user', 'bitnami');
$channel = $connection->channel();
$channel->exchange_declare('test_q', 'fanout', false, false, false);

$envelope = [
    'type' => 'customer_update',
    'source' => $listenerName,
    'payload' => [
        'id' => 23,
        'name' => 'Test Customer',
        'custom_last_ERP_synced' => 'last week'
    ],
];
$msg = new AMQPMessage(json_encode($envelope));
$channel->basic_publish($msg, 'test_q');
sleep(2);
echo " [x] Sent 'customer_update'\n";
$channel->close();
$connection->close();