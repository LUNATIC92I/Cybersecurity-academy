<?php

declare(strict_types=1);

namespace App\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Renders lesson/lab Markdown safely: raw HTML in content is escaped and
 * unsafe links (javascript:, data:) are removed. Adds platform-specific
 * blocks written as:
 *
 *   :::solution Optional title
 *   Markdown...
 *   :::
 *
 * Supported block types: solution, hint, note, tip, warning, scenario, check.
 * Solutions and hints are rendered as collapsible <details> elements.
 */
final class ContentRenderer
{
    private const BLOCKS = ['solution', 'hint', 'note', 'tip', 'warning', 'scenario', 'check', 'diagram'];

    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new AutolinkExtension());
        $this->converter = new MarkdownConverter($environment);
    }

    public function render(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }
        $html = '';
        foreach ($this->segments($markdown) as [$type, $title, $body]) {
            if ($type === null) {
                $html .= $this->markdown($body);
                continue;
            }
            $inner = $this->markdown($body);
            $label = $title !== '' ? $title : __('content.block.' . $type);
            if (in_array($type, ['solution', 'hint'], true)) {
                $html .= '<details class="content-block content-block--' . $type . '"><summary>'
                    . e($label) . '</summary><div class="content-block__body">' . $inner . '</div></details>';
            } elseif ($type === 'diagram') {
                $html .= '<figure class="content-diagram" role="img" aria-label="' . e($label) . '"><figcaption>'
                    . e($label) . '</figcaption>' . $inner . '</figure>';
            } else {
                $html .= '<aside class="content-block content-block--' . $type . '"><p class="content-block__title">'
                    . e($label) . '</p>' . $inner . '</aside>';
            }
        }
        return $this->decorate($html);
    }

    public function markdown(string $markdown): string
    {
        return (string) $this->converter->convert($markdown);
    }

    /**
     * Split Markdown into plain and custom-block segments, ignoring ":::"
     * lines that appear inside fenced code blocks.
     *
     * @return array<int, array{0: ?string, 1: string, 2: string}>
     */
    public function segments(string $markdown): array
    {
        $segments = [];
        $buffer = [];
        $current = null;
        $title = '';
        $inFence = false;
        foreach (preg_split('/\R/', $markdown) as $line) {
            if (preg_match('/^\s*(```|~~~)/', $line)) {
                $inFence = !$inFence;
            }
            if (!$inFence && $current === null && preg_match('/^:::(\w+)\s*(.*)$/', trim($line), $m)
                && in_array(strtolower($m[1]), self::BLOCKS, true)) {
                $segments[] = [null, '', implode("\n", $buffer)];
                $buffer = [];
                $current = strtolower($m[1]);
                $title = trim($m[2]);
                continue;
            }
            if (!$inFence && $current !== null && trim($line) === ':::') {
                $segments[] = [$current, $title, implode("\n", $buffer)];
                $buffer = [];
                $current = null;
                $title = '';
                continue;
            }
            $buffer[] = $line;
        }
        $segments[] = [$current, $title, implode("\n", $buffer)];
        return array_values(array_filter($segments, fn ($s) => trim($s[2]) !== ''));
    }

    /**
     * Post-process trusted renderer output: responsive tables, external
     * links opened safely, heading anchors for the table of contents.
     */
    private function decorate(string $html): string
    {
        $html = str_replace('<table>', '<div class="table-wrap"><table>', $html);
        $html = str_replace('</table>', '</table></div>', $html);
        $html = preg_replace(
            '/<a href="(https?:\/\/[^"]+)">/',
            '<a href="$1" target="_blank" rel="noopener noreferrer">',
            $html
        ) ?? $html;
        return preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function ($m) {
            $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(self::ascii($text))), '-');
            return '<h' . $m[1] . ' id="' . e($slug ?: 'section') . '">' . $m[2] . '</h' . $m[1] . '>';
        }, $html) ?? $html;
    }

    /**
     * Extract h2 headings for an in-page table of contents.
     */
    public function toc(string $html): array
    {
        preg_match_all('/<h2 id="([^"]+)">(.*?)<\/h2>/s', $html, $matches, PREG_SET_ORDER);
        return array_map(fn ($m) => ['id' => $m[1], 'text' => html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')], $matches);
    }

    public static function ascii(string $text): string
    {
        if (function_exists('transliterator_transliterate')) {
            $converted = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
            if (is_string($converted)) {
                return $converted;
            }
        }
        return (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    }
}
