<?php
// GENERATED CODE -- DO NOT EDIT!

namespace App\UserRpc;

/**
 */
class UserClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \App\UserRpc\LoginInfo $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UserLogin(\App\UserRpc\LoginInfo $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/App.UserRpc.User/UserLogin',
        $argument,
        ['\App\UserRpc\UserInfo', 'decode'],
        $metadata, $options);
    }

}
