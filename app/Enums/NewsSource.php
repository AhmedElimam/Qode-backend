<?php

namespace App\Enums;

enum NewsSource: string
{
    case NEWS_API = 'news_api';
    case THE_GUARDIAN = 'the_guardian';
    case NEW_YORK_TIMES = 'new_york_times';
    case BBC_NEWS = 'bbc_news';
    case OPEN_NEWS = 'open_news';
    case NEWSCRED = 'newscred';
    case MEDIASTACK = 'mediastack';

    public function getDisplayName(): string
    {
        return match($this) {
            self::NEWS_API => 'News API',
            self::THE_GUARDIAN => 'The Guardian',
            self::NEW_YORK_TIMES => 'New York Times',
            self::BBC_NEWS => 'BBC News',
            self::OPEN_NEWS => 'Open News',
            self::NEWSCRED => 'NewsCred',
            self::MEDIASTACK => 'MediaStack',
        };
    }

    public function getApiEndpoint(): string
    {
        return match($this) {
            self::NEWS_API => 'https://newsapi.org/v2',
            self::THE_GUARDIAN => 'https://content.guardianapis.com',
            self::NEW_YORK_TIMES => 'https://api.nytimes.com/svc',
            self::BBC_NEWS => 'https://newsapi.org/v2',
            self::OPEN_NEWS => 'https://newsapi.org/v2',
            self::NEWSCRED => 'https://api.newscred.com',
            self::MEDIASTACK => 'http://api.mediastack.com/v1',
        };
    }
}
