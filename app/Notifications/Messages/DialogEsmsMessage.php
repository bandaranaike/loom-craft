<?php

namespace App\Notifications\Messages;

class DialogEsmsMessage
{
    public function __construct(
        public readonly string $content,
        public readonly ?string $sourceAddress = null,
    ) {}
}
