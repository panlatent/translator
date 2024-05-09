<?php

namespace panlatent\translator;

class Message
{
    public function __construct(public string $category, public string $message, public array $positions)
    {
    }
}