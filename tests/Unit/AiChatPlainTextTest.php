<?php

namespace Tests\Unit;

use App\Support\AiChat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AiChatPlainTextTest extends TestCase
{
    #[DataProvider('replies')]
    public function test_replies_are_converted_to_plain_text(string $reply, string $expected): void
    {
        $this->assertSame($expected, AiChat::plainText($reply));
    }

    /** @return array<string, array{string, string}> */
    public static function replies(): array
    {
        return [
            'em dash between words' => ['We fix networks — quickly and reliably.', 'We fix networks, quickly and reliably.'],
            'unspaced em dash' => ['Wi-Fi—cabling—servers', 'Wi-Fi, cabling, servers'],
            'en dash range' => ['Open 9 – 5, Monday–Friday.', 'Open 9-5, Monday to Friday.'],
            'dash before punctuation' => ['That is all —.', 'That is all.'],
            'bold and italic' => ['**Network audit** is *fast* and __thorough__.', 'Network audit is fast and thorough.'],
            'snake_case is kept' => ['Set max_retries and file_name.', 'Set max_retries and file_name.'],
            'headings' => ["## Services\n### Support", "Services\nSupport"],
            'bullets' => ["- Wi-Fi\n* Servers\n+ Backups", "• Wi-Fi\n• Servers\n• Backups"],
            'numbered list kept' => ["1. Call us\n2. Book", "1. Call us\n2. Book"],
            'inline code and fences' => ["Run `ipconfig`:\n```bash\nping 8.8.8.8\n```", "Run ipconfig:\nping 8.8.8.8"],
            'links and images' => ['See [contact](https://example.test/contact) ![logo](https://example.test/l.png)', 'See contact (https://example.test/contact) logo'],
            'block quote and rule' => ["> Note this\n---\nDone", "Note this\n\nDone"],
            'table' => ["| Service | Price |\n|---|---|\n| Audit | Quote |", "Service, Price\n\nAudit, Quote"],
            'extra blank lines' => ["Hello\n\n\n\nThere", "Hello\n\nThere"],
        ];
    }
}
