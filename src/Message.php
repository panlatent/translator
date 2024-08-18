<?php

namespace Panlatent\Translator;

readonly class Message
{
    public function __construct(public string $category, public string $message, public array $positions)
    {
    }
}