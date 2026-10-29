<?php

namespace App\Filament\Widgets;

use App\Enums\EnquiryStatus;
use App\Enums\TicketStatus;
use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\EnquiryResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\SupportTicketResource;
use App\Models\Article;
use App\Models\Enquiry;
use App\Models\Project;
use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $newEnquiries = Enquiry::query()->where('status', EnquiryStatus::New)->count();
        $openTickets = SupportTicket::query()->where('status', TicketStatus::Open)->count();

        return [
            Stat::make('New enquiries', $newEnquiries)
                ->description($newEnquiries > 0 ? 'Waiting for a response' : 'All caught up')
                ->color($newEnquiries > 0 ? 'warning' : 'success')
                ->url(EnquiryResource::getUrl('index')),
            Stat::make('Tickets waiting on you', $openTickets)
                ->description(SupportTicket::query()->where('status', TicketStatus::Answered)->count().' waiting on customers')
                ->color($openTickets > 0 ? 'warning' : 'success')
                ->url(SupportTicketResource::getUrl('index')),
            Stat::make('Published projects', Project::query()->published()->count())
                ->description(Project::query()->count().' in total, including drafts')
                ->url(ProjectResource::getUrl('index')),
            Stat::make('Published articles', Article::query()->published()->count())
                ->description(Article::query()->count().' in total, including drafts')
                ->url(ArticleResource::getUrl('index')),
        ];
    }
}
