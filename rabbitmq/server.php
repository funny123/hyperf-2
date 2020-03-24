<?php
//配置信息
$conn_args = array(
    'host' => '127.0.0.1',
    'port' => '5672',
    'login' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
);
//首先要做的事情就是建立一个到RabbitMQ服务器的连接。
$conn = new AMQPConnection($conn_args);
if (!$conn->connect()) {
    die('Not connected ' . PHP_EOL);
}
/**
 * 现在我们已经连接上服务器了，
 * 那么，在发送消息之前我们需要确认队列是存在的。
 * 如果我们把消息发送到一个不存在的队列，RabbitMQ会丢弃这条消息。
 * 我门先创建一个名为hello的队列，然后把消息发送到这个队列中。
 */
$queueName = 'hello';
$channel = new AMQPChannel($conn);
$exchange = new AMQPExchange($channel);
/**
 * 这时候我们就可以发送消息了，我们第一条消息只包含了 Hello World!字符串，我们打算把它发送到我们的hello队列。
 * 在RabbitMQ中，消息是不能直接发送到队列，它需要发送到交换器（exchange）中
 * 现在我们所需要了解的是如何使用默认的交换器（exchange），它使用一个空字符串来标识。
 * 交换器允许我们指定某条消息需要投递到哪个队列，
 * $routeKey参数必须指定为队列的名称：publish（message,$routekey）
 */

$queue = new AMQPQueue($channel);
$queue->setName($queueName);
//我们需要确认队列是存在的。使用$queue->declare()创建一个队列——我们可以运行这个命令很多次，但是只有一个队列会创建。
$queue->declare();
$message = [
    'name' => 'hello',
    'args' => ["0", "1", "2", "3"],
];
//生产者，向RabbitMQ发送消息
$state = $exchange->publish(json_encode($message), 'hello');
if (!$state) {
    echo 'Message not sent', PHP_EOL;
} else {
    echo 'Message sent!', PHP_EOL;
}
/**
 * 这里就在这个页面获取了 ；
 * 或者可以自己定义一个received.php来接受生产者发送的消息（死循环，有消息就接受）
 */
//消费者获得消息内容
while ($envelope = $queue->get(AMQP_AUTOACK)) {
    echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New Message';
    echo PHP_EOL;
    echo $envelope->getBody(), PHP_EOL;
}

?>