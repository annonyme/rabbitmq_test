<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$listenerName = 'crm_listener';

$connection = new AMQPStreamConnection('localhost', 5672, 'user', 'bitnami');
$channel = $connection->channel();
$channel->exchange_declare('test_q', 'fanout', false, false, false);
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
$channel->queue_bind($queue_name, 'test_q');
echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) use($listenerName, $channel) {
    echo ' [x] Received ', $msg->body, "\n";
    $envelope = json_decode($msg->body, true);
    if($envelope['type'] == 'customer_update' && $listenerName != $envelope['source'] && $listenerName == 'erp_listener') {
        $envelope['payload']['custom_last_ERP_synced'] = 'now';
        $envelope['source'] = $listenerName;
        $nextMsg = new AMQPMessage(json_encode($envelope));
        $channel->basic_publish($nextMsg, 'test_q');
        echo " [x] set new value to custom-field and send message\n";
    }
    else if($listenerName == $envelope['source']) {
        echo " [*] ignoring own message\n";
    }
    else {
        echo " [*] only listening to this message\n";
    }
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();