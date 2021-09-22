<?php

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PublishRenderQueueMessage {

    public function __construct($projectId, $orderId, $messageQuantity) {
        $this->publish($projectId, $orderId, $messageQuantity);
    }

    public function publish($projectId, $orderId, $messageQuantity) {
        try {
            $message = '{"projectId": "' . $projectId . '", "orderId": "' . $orderId . '"}';
            $parameters = [
                'x-max-priority' => ['I', 10],
            ];

            $connection = new AMQPStreamConnection('mq.auryn.com.br', 5672, 'mqevent', 'vv7wtKZXKL', 'auryn-dev');
            $channel = $connection->channel();

            $channel->queue_declare('renderer_projects_with_priority', false, true, false, false, false, $parameters);
            
            $msg = new AMQPMessage($message);
            print_r($message);

            for ($i = 1; $i <= $messageQuantity; $i++) {
                $message = 'message'.$i;

                echo $message."\n";

                $channel->basic_publish($msg, '', 'renderer_projects_with_priority');
            }
        } catch (\Exception $e) {
            print_r($e);
        }

        $channel->close();
        $connection->close();
    }
}

new PublishRenderQueueMessage($argv[1], $argv[2], $argv[3]);