<?php

namespace panlatent\translator;

class TemplateTransform
{
    public function transform(string $content): string
    {
        $res = '';

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $res .= $this->parse($line) . "\n";
        }

        return substr($res, 0, -1);
    }

    private function parse(string $line): string
    {
        return preg_replace_callback('#(<(\w+)(\s+\w+(="[^"]*"|))*>)(?!.*<\w+)(?<text>.*?)(</\2>)#', function ($matches) {
            $text = trim($matches['text']);
            if ($text === '') {
                return sprintf("%s%s%s", $matches[1], $matches['text'], $matches[6]);
            } elseif (preg_match('#^{{.*}}$#', $text)) {
                return sprintf("%s%s%s", $matches[1], $matches['text'], $matches[6]);
            }
            return sprintf("%s{{ \"%s\"|t }}%s", $matches[1], ucfirst($text), $matches[6]);
        }, $line);
    }
}