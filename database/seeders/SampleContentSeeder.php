<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tag;
use App\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sample services and articles, each with a generated featured image, so a new site has
 * realistic content to look at. Edit or delete them in the admin like any other record.
 *
 * Safe to run more than once: records are matched on their slug and existing ones are left
 * as they are. A missing featured image is generated again; an existing one is never replaced.
 */
class SampleContentSeeder extends Seeder
{
    /**
     * Icons are Font Awesome solid code points.
     *
     * @var list<array<string, mixed>>
     */
    public const SERVICES = [
        [
            'category' => 'it-consulting-strategy', 'icon' => 'f0ae',
            'name' => 'IT Health Check & Assessment',
            'summary' => 'A clear, independent review of your systems with a prioritised list of what to fix first.',
            'description' => "I review your network, devices, servers, backups, security and software licences, and talk to the people who use them every day.\n\nYou receive a plain-language report that explains what is working, what is at risk and what each fix will cost, ranked so you can act on the most important items first.",
            'delivery_method' => 'On-site and remote', 'estimated_duration' => '3 to 5 days', 'indicative_price' => 450,
        ],
        [
            'category' => 'managed-it-services', 'icon' => 'f085',
            'name' => 'Managed IT Support Plan',
            'summary' => 'Proactive monitoring, maintenance and support for a fixed monthly fee.',
            'description' => "I look after your computers, servers and network before problems reach your staff: updates, security patches, monitoring and regular health checks.\n\nWhen something does go wrong, your team has one person to call, and I already know your setup.",
            'delivery_method' => 'Remote with on-site visits', 'estimated_duration' => 'Monthly', 'indicative_price' => 300,
        ],
        [
            'category' => 'it-support-help-desk', 'icon' => 'f590',
            'name' => 'Remote & On-site Help Desk',
            'summary' => 'Fast, friendly help for everyday IT problems, by phone, remote session or a visit.',
            'description' => "From a laptop that will not connect to a printer that has stopped working, I diagnose and fix the problem, usually in a single remote session.\n\nIf it needs hands on, I come to you. Every request is logged so recurring issues are spotted and fixed at the root.",
            'delivery_method' => 'Remote and on-site', 'estimated_duration' => 'Same or next day', 'indicative_price' => 40,
        ],
        [
            'category' => 'network-design-installation', 'icon' => 'f6ff',
            'name' => 'Office Network Setup',
            'summary' => 'A reliable, secure wired and wireless network designed around how your office works.',
            'description' => "I plan and install routers, switches, firewalls and access points, separate guest and staff traffic, and document everything so it is easy to support later.\n\nSuitable for new offices, moves and networks that have grown without a plan.",
            'delivery_method' => 'On-site', 'estimated_duration' => '1 to 3 days', 'indicative_price' => 800,
        ],
        [
            'category' => 'network-design-installation', 'icon' => 'f1eb',
            'name' => 'Wi-Fi Coverage Survey',
            'summary' => 'Find and fix dead spots, slow connections and interference across your building.',
            'description' => "I measure signal strength and interference room by room, then recommend where access points should go and how they should be configured.\n\nYou get a heat map of the current coverage and a clear plan for consistent Wi-Fi everywhere it is needed.",
            'delivery_method' => 'On-site', 'estimated_duration' => '1 day', 'indicative_price' => 250,
        ],
        [
            'category' => 'cybersecurity', 'icon' => 'f3ed',
            'name' => 'Security Audit & Hardening',
            'summary' => 'Find the gaps attackers look for and close them before they are used.',
            'description' => "I check passwords and sign-in settings, firewalls, remote access, software updates, email protection and user permissions against current good practice.\n\nThe most urgent issues are fixed during the audit, and the rest are set out in a practical plan with costs.",
            'delivery_method' => 'Remote and on-site', 'estimated_duration' => '2 to 4 days', 'indicative_price' => 600,
        ],
        [
            'category' => 'cloud-services', 'icon' => 'f0c2',
            'name' => 'Cloud Migration',
            'summary' => 'Move files, email and applications to the cloud without disrupting your work.',
            'description' => "I assess what should move, choose the right platform, and plan the migration in stages so your team keeps working throughout.\n\nAfter the move I tidy up permissions, set up backups and make sure costs stay predictable.",
            'delivery_method' => 'Remote', 'estimated_duration' => '1 to 4 weeks', 'indicative_price' => null,
        ],
        [
            'category' => 'backup-disaster-recovery', 'icon' => 'f1c0',
            'name' => 'Backup & Recovery Setup',
            'summary' => 'Automatic backups that are checked, kept off-site and proven to restore.',
            'description' => "I set up backups for servers, computers and cloud services following the 3-2-1 rule, with copies stored safely away from your office.\n\nWe test a restore before handing over and give you a short recovery plan that says exactly what to do if something is lost.",
            'delivery_method' => 'Remote and on-site', 'estimated_duration' => '1 to 2 days', 'indicative_price' => 350,
        ],
        [
            'category' => 'servers-systems-administration', 'icon' => 'f233',
            'name' => 'Server Installation & Maintenance',
            'summary' => 'Windows and Linux servers installed, secured and kept running smoothly.',
            'description' => "I install and configure physical and virtual servers for file sharing, applications and databases, with monitoring and updates from day one.\n\nFor existing servers I can take over routine maintenance, plan replacements and handle upgrades out of hours.",
            'delivery_method' => 'On-site and remote', 'estimated_duration' => '1 to 3 days', 'indicative_price' => 700,
        ],
        [
            'category' => 'email-collaboration', 'icon' => 'f0e0',
            'name' => 'Microsoft 365 Setup & Migration',
            'summary' => 'Business email, Teams and file sharing set up properly from the start.',
            'description' => "I move your email and files to Microsoft 365, configure Teams and SharePoint, and switch on the security settings that are often left off.\n\nStaff get a short guide and a walk-through so the change is smooth on day one.",
            'delivery_method' => 'Remote', 'estimated_duration' => '2 to 5 days', 'indicative_price' => 400,
        ],
        [
            'category' => 'software-development', 'icon' => 'f121',
            'name' => 'Custom Web Application Development',
            'summary' => 'Web applications built around your processes, not the other way round.',
            'description' => "I turn spreadsheets and manual processes into secure web applications for bookings, records, reporting or customer portals.\n\nWork is delivered in small stages you can try as I go, with clear documentation and ongoing support available.",
            'delivery_method' => 'Remote', 'estimated_duration' => '4 to 12 weeks', 'indicative_price' => null,
        ],
        [
            'category' => 'web-design-hosting', 'icon' => 'f0ac',
            'name' => 'Business Website Design',
            'summary' => 'A fast, accessible website that explains what you do and brings in enquiries.',
            'description' => "I design and build websites that work well on phones, load quickly and are easy for you to update.\n\nHosting, domain, SSL certificate and regular updates can all be included so you do not have to manage them yourself.",
            'delivery_method' => 'Remote', 'estimated_duration' => '2 to 4 weeks', 'indicative_price' => 900,
        ],
    ];

    /**
     * @var list<array<string, mixed>>
     */
    public const ARTICLES = [
        [
            'category' => 'networking', 'tags' => ['network-design', 'wi-fi', 'small-business', 'performance'], 'icon' => 'f6ff', 'days_ago' => 3,
            'title' => 'Five signs your office network needs attention',
            'excerpt' => 'Slow file transfers, dropped calls and Wi-Fi that works in some rooms but not others usually point to the same few problems.',
            'image_alt' => 'Illustration of a network diagram',
            'body' => "Most office networks grow one device at a time. A new printer here, an extra switch there, and after a few years nobody is quite sure how it all fits together. These five signs suggest it is time for a closer look.\n\n## 1. Everything slows down at the same time of day\n\nIf the network is fine in the morning but crawls after lunch, you are probably running out of capacity somewhere: the internet connection, an old switch or a single overloaded access point.\n\n## 2. Wi-Fi works in some rooms but not others\n\nDead spots are rarely fixed by a stronger router. They usually need access points in the right places and channels set so they do not interfere with each other.\n\n## 3. Video calls drop or freeze\n\nCalls are sensitive to delay and packet loss. Without basic traffic prioritisation, a large download can disrupt every meeting in the building.\n\n## 4. Nobody knows the admin passwords\n\nIf the person who set up the network has left, you may not be able to change settings or apply security updates. That is a risk in itself.\n\n## 5. Guests and staff share the same network\n\nVisitors should never be able to see your file server or printers. A separate guest network is simple to set up and closes an easy way in.\n\n## What to do next\n\nStart with a short survey: list your devices, check the age of your equipment and measure Wi-Fi coverage. The fixes are often smaller and cheaper than expected.",
        ],
        [
            'category' => 'cloud-infrastructure', 'tags' => ['backups', 'disaster-recovery', 'data-protection', 'small-business'], 'icon' => 'f1c0', 'days_ago' => 10,
            'title' => 'The 3-2-1 backup rule explained for small businesses',
            'excerpt' => 'Three copies, two types of storage, one copy off-site. A simple rule that protects you from hardware failure, theft and ransomware.',
            'image_alt' => 'Illustration of a database',
            'body' => "Backups only matter on the day you need them, and that is the worst day to find out they do not work. The 3-2-1 rule is an easy way to check you are covered.\n\n## Three copies of your data\n\nKeep the original plus two backups. If one backup fails, you still have another.\n\n## Two different types of storage\n\nFor example, a backup drive in the office and a cloud backup service. Different storage fails in different ways, so one problem is unlikely to affect both.\n\n## One copy off-site\n\nFire, flood and theft can take out everything in one building. At least one copy should live somewhere else, and ideally it should be protected so ransomware on your network cannot change or delete it.\n\n## Test your restores\n\nA backup you have never restored is a hope, not a plan. Pick a file or a whole computer every few months and restore it. Write down how long it took.\n\n## A simple starting point\n\n1. List the data you cannot afford to lose.\n2. Check where it is backed up today, and how often.\n3. Add an off-site copy if you do not have one.\n4. Schedule a test restore and put it in the calendar.",
        ],
        [
            'category' => 'cybersecurity', 'tags' => ['security-audit', 'microsoft-365', 'data-protection'], 'icon' => 'f3ed', 'days_ago' => 18,
            'title' => 'How to protect your team from phishing emails',
            'excerpt' => 'Most cyber attacks on small organisations start with an email. A few settings and habits stop the majority of them.',
            'image_alt' => 'Illustration of a security shield',
            'body' => "Phishing emails pretend to come from someone you trust, like a supplier, a bank or your own manager, to get you to click a link, open an attachment or share a password.\n\n## Turn on multi-factor sign-in\n\nIf a password is stolen, multi-factor authentication stops the attacker from using it. It is the single most effective protection you can add, and it is included with most business email services.\n\n## Switch on email filtering\n\nMicrosoft 365 and Google Workspace both include protection against known phishing and malware. Check it is enabled and that warning banners for external emails are turned on.\n\n## Teach the warning signs\n\n- Urgent requests to pay an invoice or change bank details\n- Links that do not match the sender's real website\n- Unexpected attachments, even from people you know\n- Requests to sign in again to read a shared document\n\n## Make reporting easy\n\nStaff should know exactly who to tell when something looks wrong, and feel comfortable doing it even if they already clicked. Fast reporting turns a breach into a near miss.\n\n## Check payment changes by phone\n\nAny request to change bank details should be confirmed with a phone call to a number you already have, never one given in the email.",
        ],
        [
            'category' => 'cloud-infrastructure', 'tags' => ['microsoft-365', 'cloud', 'remote-work'], 'icon' => 'f0c2', 'days_ago' => 26,
            'title' => 'Moving to Microsoft 365: a practical checklist',
            'excerpt' => 'Plan the move in stages and switch on the right settings, and your team will barely notice the change.',
            'image_alt' => 'Illustration of a cloud',
            'body' => "Microsoft 365 gives you business email, Teams, OneDrive and SharePoint in one subscription. The move is straightforward if you plan it.\n\n## Before the move\n\n1. List every mailbox, shared mailbox and email alias.\n2. Decide where shared files will live: Teams, SharePoint or OneDrive.\n3. Choose the right licence for each person.\n4. Check who controls your domain name, because you will need to update its records.\n\n## During the move\n\nMove email first, then files. Keep the old system running until everything is copied and checked. Plan the switch-over for the end of the day or a weekend.\n\n## Settings worth switching on\n\n- Multi-factor sign-in for every account\n- External email warning banners\n- Retention so deleted items can be recovered\n- Sharing limits for files sent outside the organisation\n\n## After the move\n\nGive staff a one-page guide covering how to sign in, where files are and who to ask for help. Then remove old accounts and cancel the previous service.",
        ],
        [
            'category' => 'troubleshooting-guides', 'tags' => ['wi-fi', 'routers-switches', 'performance'], 'icon' => 'f1eb', 'days_ago' => 34,
            'title' => 'Why is my Wi-Fi slow? A step-by-step guide',
            'excerpt' => 'Before paying for a faster connection, work through these checks. The cause is often closer than you think.',
            'image_alt' => 'Illustration of a Wi-Fi signal',
            'body' => "Slow Wi-Fi is one of the most common complaints I hear. Work through these steps in order and you will usually find the cause.\n\n## 1. Test with a cable\n\nPlug a laptop directly into the router and run a speed test. If it is still slow, the problem is your internet connection, not the Wi-Fi.\n\n## 2. Check how far you are from the access point\n\nWalls, floors, mirrors and metal cabinets all weaken the signal. If speed drops sharply in certain rooms, you need better coverage rather than a faster plan.\n\n## 3. Look for interference\n\nNeighbouring networks on the same channel compete for airtime. Most modern access points can choose channels automatically, but older routers often stay on the default.\n\n## 4. Count the devices\n\nPhones, laptops, TVs, cameras and smart devices all share the same capacity. A home router struggles in a busy office.\n\n## 5. Restart and update\n\nRouters run software that needs updates. A restart clears temporary problems, and an update can fix bugs and security issues.\n\n## When to call for help\n\nIf the cabled test is fast but Wi-Fi is slow everywhere, a coverage survey will show exactly where access points should go.",
        ],
        [
            'category' => 'it-strategy', 'tags' => ['small-business', 'business-continuity'], 'icon' => 'f201', 'days_ago' => 45,
            'title' => 'Planning an IT budget that holds up all year',
            'excerpt' => 'Separate running costs from replacements, plan for the expected and keep a little aside for the unexpected.',
            'image_alt' => 'Illustration of a rising chart',
            'body' => "IT spending often feels unpredictable because it is planned one emergency at a time. A simple budget turns most of those surprises into scheduled work.\n\n## Start with what you already pay for\n\nList every subscription, licence, support contract and internet connection. You will often find services nobody uses any more.\n\n## Plan replacements on a cycle\n\nLaptops typically last four to five years, servers five to seven and network equipment around five. Spread replacements over several years so no single year is a shock.\n\n## Budget for security and backups\n\nThese are easy to cut and expensive to skip. Include backup storage, security software and an annual review.\n\n## Keep a contingency\n\nSet aside around ten percent of the total for the unexpected: a failed drive, an urgent fix or a new starter who needs equipment quickly.\n\n## Review it quarterly\n\nA short check every three months keeps the budget realistic and gives you time to adjust before small problems become big ones.",
        ],
        [
            'category' => 'case-studies', 'tags' => ['windows-server', 'backups', 'virtualisation'], 'icon' => 'f233', 'days_ago' => 60,
            'title' => 'Case study: rescuing a failing file server',
            'excerpt' => 'How an accounting office avoided data loss when its eight-year-old server began to fail during the busiest month of the year.',
            'image_alt' => 'Illustration of a server',
            'body' => "## The problem\n\nAn accounting office of twelve people relied on a single file server bought eight years earlier. In the middle of tax season, staff began to see slow file access and occasional errors. The server's disks were reporting failures.\n\n## What I found\n\nTwo of the four disks were failing, the backups had silently stopped three months earlier and the server's operating system was no longer receiving security updates.\n\n## What I did\n\n1. Took an immediate full copy of the data to a new backup drive.\n2. Moved the server onto temporary hardware as a virtual machine so staff could keep working.\n3. Set up automatic off-site backups and tested a full restore.\n4. Planned the move to a new, supported server for the quieter month that followed.\n\n## The result\n\nNo data was lost and the office stayed open throughout. Files now open faster than before, backups are checked every day, and the firm has a five-year plan for its equipment.\n\n## The lesson\n\nBackups need checking, not just setting up. A short monthly review would have spotted the problem months earlier.",
        ],
    ];

    public function run(): void
    {
        $this->call([ServiceCategorySeeder::class, ArticleTaxonomySeeder::class]);

        foreach (self::SERVICES as $index => $data) {
            $service = Service::query()->firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'service_category_id' => ServiceCategory::query()->where('slug', $data['category'])->value('id'),
                    'name' => $data['name'],
                    'summary' => $data['summary'],
                    'description' => $data['description'],
                    'delivery_method' => $data['delivery_method'],
                    'estimated_duration' => $data['estimated_duration'],
                    'indicative_price' => $data['indicative_price'],
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ],
            );

            if (! $this->hasImage($service->sample_image)) {
                $service->update(['sample_image' => PlaceholderImage::create('service-samples', $data['icon'], $service->slug)]);
            }
        }

        foreach (self::ARTICLES as $data) {
            $article = Article::query()->firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'article_category_id' => ArticleCategory::query()->where('slug', $data['category'])->value('id'),
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => $data['body'],
                    'status' => PublicationStatus::Published,
                    'published_at' => now()->subDays($data['days_ago'])->setTime(9, 0),
                ],
            );

            if ($article->wasRecentlyCreated) {
                $article->tags()->sync(Tag::query()->whereIn('slug', $data['tags'])->pluck('id'));
            }

            if (! $this->hasImage($article->featured_image)) {
                $article->update([
                    'featured_image' => PlaceholderImage::create('article-images', $data['icon'], $article->slug),
                    'featured_image_alt' => $article->featured_image_alt ?: $data['image_alt'],
                ]);
            }
        }
    }

    private function hasImage(?string $path): bool
    {
        return filled($path) && Storage::disk('public')->exists($path);
    }
}
