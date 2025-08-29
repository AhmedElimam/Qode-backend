<?php

namespace App\Enums;

enum NewsSource: string
{
    case THE_GUARDIAN = 'the_guardian';
    case NEW_YORK_TIMES = 'new_york_times';
    case MEDIASTACK = 'mediastack';

    public function getDisplayName(): string
    {
        return match($this) {
            self::THE_GUARDIAN => 'The Guardian',
            self::NEW_YORK_TIMES => 'New York Times',
            self::MEDIASTACK => 'MediaStack',
        };
    }

    public function getApiEndpoint(): string
    {
        return match($this) {
            self::THE_GUARDIAN => 'https://content.guardianapis.com',
            self::NEW_YORK_TIMES => 'https://api.nytimes.com/svc',
            self::MEDIASTACK => 'http://api.mediastack.com/v1',
        };
    }
}
