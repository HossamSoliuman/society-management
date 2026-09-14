<?php

namespace App\Contracts;

interface SmsGateway
{
    /**
     * Send a text message. Returns true when the gateway accepted the message.
     */
    public function send(string $mobile, string $message): bool;
}
