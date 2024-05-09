<?php

namespace panlatent\translator\formatters;

use panlatent\translator\Message;
use yii\base\Component;

class GettextPo extends Component
{
    /**
     * @param Message[] $messages
     * @return string
     */
    public function format(array $messages): string
    {
        $contents = 'msgid ""
msgstr ""
"Content-Type: text/plain; charset=UTF-8\n"

';
        foreach ($messages as $message) {
            $contents .= sprintf(<<<EOF
#: %s
msgctxt "%s"
msgid "%s"
msgstr "%s"

EOF
                        , implode(' ', $message->positions)
                        , $message->category, $message->message, $message->message);
        }

        return $contents;
    }
}