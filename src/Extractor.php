<?php

namespace Panlatent\Translator;

class Extractor
{
    private string $defaultCategory = 'site';

    private array $ignoreCategories = [];

    private array $nodes = [];

    public function __construct(private readonly ?string $basePath, private readonly array $keywords)
    {
    }

    public function setDefaultCategory(string $category): self
    {
        $this->defaultCategory = $category;
        return $this;
    }

    public function setIgnoreCategories(array $categories): self
    {
        $this->ignoreCategories = $categories;
        return $this;
    }

    /**
     * Parse message sources from PHP code.
     * Example: Yii::t('category', 'message') | Craft::t('category', 'message')
     * @param string $path
     * @return void
     */
    public function fromPhpFile(string $path): void
    {
        $keywords = implode('|', array_map('preg_quote', $this->keywords));
        $this->parseFile($path, str_replace('KEYWORDS', $keywords, '#(?:KEYWORDS)\((["\'])(?<category>[^\1]+?)\1\s*,\s*(["\'])(?<message>[\w \d_-]*?)\3#'));
    }

    /**
     * Parse message sources from Twig template.
     * Example: "message|t(category)"
     * @param string $path
     * @return void
     */
    public function fromTwigFile(string $path): void
    {
        $this->parseFile($path, '#(["\'])(?<message>(?:(?!\1).)+?)\1\|t(\((["\'])(?<category>(?:(?!\4).)+?)\4\s*[,)]|(?!\w))#');
    }

    public function extract(): array
    {
        $data = [];
        foreach ($this->formatNodes($this->nodes) as $category => $messages) {
            foreach ($messages as $message => $files) {
                $data[] = new Message($category, $message, $this->getPositions($files));
            }
        }
        return $data;
    }

    protected function parseFile(string $path, string $pattern): void
    {
        foreach (file($path) as $offset => $content) {
            preg_match_all($pattern, $content, $match);
            foreach ($match['message'] as $key => $message) {
                $category = $match['category'][$key];
                if ($category === '') {
                    $category = $this->defaultCategory;
                }
                if ($this->isIgnore($category)) {
                    continue;
                }
                $this->nodes[] = new class($category, $message, $path, $offset + 1) {
                    public function __construct(public string $category, public string $message, public string $file, public int $line) {}
                };
            }
        }
    }

    private function formatNodes(array $nodes): array
    {
        $arr = [];
        foreach ($nodes as $node) {
            if (!isset($arr[$node->category][$node->message])) {
                $arr[$node->category][$node->message] = [
                    $node->file => [$node->line],
                ];
            } elseif (!isset($arr[$node->category][$node->message][$node->file])) {
                $arr[$node->category][$node->message][$node->file] = [$node->line];
            } elseif (!in_array($node->line, $arr[$node->category][$node->message][$node->file], true)) {
                $arr[$node->category][$node->message][$node->file][] = $node->line;
            }
        }
        return $arr;
    }

    private function getPositions(array $files): array
    {
        $positions = [];
        foreach ($files as $file => $lines) {
            if ($this->basePath !== null && strncmp($this->basePath, $file, strlen($this->basePath)) === 0) {
                $file = str_replace($this->basePath, '', $file);
            }
            foreach ($lines as $line) {
                $positions[] = $file . ':' . $line;
            }
        }
        return $positions;
    }

    private function isIgnore(string $category): bool
    {
        return in_array($category, $this->ignoreCategories, true);
    }
}