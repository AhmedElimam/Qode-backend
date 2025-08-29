<?php

namespace App\Console\Commands;

use App\Services\NewsAggregationService;
use Illuminate\Console\Command;

class FetchInitialArticles extends Command
{
    protected $signature = 'articles:fetch-initial';

    protected $description = 'Fetch initial articles from all news sources';

    public function handle(NewsAggregationService $newsAggregationService): int
    {
        $this->info('Starting to fetch articles from all sources...');

        try {
            $articles = $newsAggregationService->fetchArticlesFromAllSources();

            $this->info("Successfully fetched " . count($articles) . " articles from:");
            $this->line('- The Guardian');
            $this->line('- New York Times');
            $this->line('- MediaStack');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
