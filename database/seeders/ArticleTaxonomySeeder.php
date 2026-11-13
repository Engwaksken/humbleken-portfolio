<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample article categories and tags for an IT engineering and consulting site.
 *
 * Safe to run more than once: records are matched on their slug and existing ones are
 * left as they are, so edits made in the admin are never overwritten.
 */
class ArticleTaxonomySeeder extends Seeder
{
    /** @var list<array{name: string, description: string}> */
    public const CATEGORIES = [
        ['name' => 'Networking', 'description' => 'Designing, securing and troubleshooting wired and wireless networks.'],
        ['name' => 'Cybersecurity', 'description' => 'Practical steps to protect systems, data and people from common threats.'],
        ['name' => 'Cloud & Infrastructure', 'description' => 'Servers, hosting, virtualisation and moving workloads to the cloud.'],
        ['name' => 'Software Development', 'description' => 'Building and maintaining reliable web and mobile applications.'],
        ['name' => 'IT Strategy', 'description' => 'Planning technology decisions, budgets and roadmaps.'],
        ['name' => 'Troubleshooting Guides', 'description' => 'Step-by-step help for diagnosing recurring technical problems.'],
        ['name' => 'Case Studies', 'description' => 'How real problems were assessed and solved.'],
        ['name' => 'News & Updates', 'description' => 'Announcements, new services and updates from me.'],
    ];

    /** @var list<string> */
    public const TAGS = [
        'Backups', 'Business Continuity', 'Cloud', 'Data Protection', 'DevOps', 'Disaster Recovery',
        'Firewalls', 'Laravel', 'Linux', 'Microsoft 365', 'Mobile Apps', 'Monitoring',
        'Network Design', 'Performance', 'Remote Work', 'Routers & Switches', 'Security Audit',
        'Small Business', 'Virtualisation', 'VPN', 'Web Hosting', 'Windows Server', 'Wi-Fi',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $category) {
            ArticleCategory::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['sort_order' => ($index + 1) * 10],
            );
        }

        foreach (self::TAGS as $name) {
            Tag::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
