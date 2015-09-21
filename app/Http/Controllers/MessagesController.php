<?php
$entryData = array(
    'category' => $_POST['category']
, 'title'    => $_POST['title']
, 'article'  => $_POST['article']
, 'when'     => time()
);


$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://127.0.0.1:5555");

$socket->send(json_encode($entryData));
