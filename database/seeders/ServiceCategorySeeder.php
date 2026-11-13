<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The full set of IT service categories, in the order they appear on the Services page.
 *
 * Safe to run more than once: categories are matched on their slug and existing ones are
 * left as they are, so names, descriptions, order and visibility edited in the admin are
 * never overwritten. The Services page only shows a category once it has active services.
 */
class ServiceCategorySeeder extends Seeder
{
    /** @var list<array{name: string, description: string}> */
    public const CATEGORIES = [
        ['name' => 'IT Consulting & Strategy', 'description' => 'Independent advice on technology decisions, IT roadmaps, budgets and digital transformation.'],
        ['name' => 'Managed IT Services', 'description' => 'Ongoing monitoring, maintenance and management of your IT systems for a predictable monthly cost.'],
        ['name' => 'IT Support & Help Desk', 'description' => 'On-site and remote support for users, devices and everyday technical problems.'],
        ['name' => 'Network Design & Installation', 'description' => 'Planning, setting up and troubleshooting wired and wireless networks, routers, switches and firewalls.'],
        ['name' => 'Structured Cabling', 'description' => 'Data and fibre cabling, network cabinets, patching and labelling for offices and buildings.'],
        ['name' => 'Cybersecurity', 'description' => 'Security assessments, firewall and endpoint protection, hardening and incident response.'],
        ['name' => 'Cloud Services', 'description' => 'Moving to and managing cloud platforms such as Microsoft Azure, AWS and Google Cloud.'],
        ['name' => 'Backup & Disaster Recovery', 'description' => 'Reliable backups, tested restores and business continuity plans to keep you running.'],
        ['name' => 'Servers & Systems Administration', 'description' => 'Installing, configuring and maintaining Windows and Linux servers, virtualisation and storage.'],
        ['name' => 'Email & Collaboration', 'description' => 'Setting up and managing Microsoft 365, Google Workspace, email, file sharing and Teams.'],
        ['name' => 'Software Development', 'description' => 'Custom web applications, business systems, integrations and APIs built to fit your processes.'],
        ['name' => 'Mobile App Development', 'description' => 'Android and iOS apps designed, built and published for your business.'],
        ['name' => 'Web Design & Hosting', 'description' => 'Websites, domains, hosting, SSL certificates and ongoing site maintenance.'],
        ['name' => 'Database Management', 'description' => 'Database design, performance tuning, migration and maintenance for MySQL, PostgreSQL and SQL Server.'],
        ['name' => 'Data Analytics & Business Intelligence', 'description' => 'Reports, dashboards and data insights that help you make informed decisions.'],
        ['name' => 'DevOps & Automation', 'description' => 'Deployment pipelines, infrastructure as code and automating repetitive IT tasks.'],
        ['name' => 'Business Software & ERP', 'description' => 'Choosing, implementing and supporting ERP, CRM, accounting and other business software.'],
        ['name' => 'VoIP & Unified Communications', 'description' => 'Business phone systems, VoIP, video conferencing and call centre solutions.'],
        ['name' => 'CCTV & Access Control', 'description' => 'Security cameras, door access systems and remote monitoring for your premises.'],
        ['name' => 'Hardware Repair & Maintenance', 'description' => 'Diagnosis, repair and upgrades for computers, laptops, printers and other equipment.'],
        ['name' => 'IT Procurement & Asset Management', 'description' => 'Sourcing the right hardware and software, licensing, and keeping track of IT assets.'],
        ['name' => 'IT Audit & Compliance', 'description' => 'Reviewing systems against policies and regulations such as data protection laws and ISO 27001.'],
        ['name' => 'IT Training', 'description' => 'Practical training for staff on software, security awareness and good IT practice.'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $category) {
            ServiceCategory::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['sort_order' => ($index + 1) * 10, 'is_active' => true],
            );
        }
    }
}
