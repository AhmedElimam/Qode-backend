<?php

namespace App\Enums;

enum NewsCategory: string
{
    case BUSINESS = 'business';
    case TECHNOLOGY = 'technology';
    case SPORTS = 'sports';
    case ENTERTAINMENT = 'entertainment';
    case HEALTH = 'health';
    case SCIENCE = 'science';
    case POLITICS = 'politics';
    case WORLD = 'world';
    case NATIONAL = 'national';
    case LOCAL = 'local';
    case OPINION = 'opinion';
    case ARTS = 'arts';
    case FOOD = 'food';
    case TRAVEL = 'travel';
    case EDUCATION = 'education';

    public function getDisplayName(): string
    {
        return match($this) {
            self::BUSINESS => 'Business',
            self::TECHNOLOGY => 'Technology',
            self::SPORTS => 'Sports',
            self::ENTERTAINMENT => 'Entertainment',
            self::HEALTH => 'Health',
            self::SCIENCE => 'Science',
            self::POLITICS => 'Politics',
            self::WORLD => 'World',
            self::NATIONAL => 'National',
            self::LOCAL => 'Local',
            self::OPINION => 'Opinion',
            self::ARTS => 'Arts',
            self::FOOD => 'Food',
            self::TRAVEL => 'Travel',
            self::EDUCATION => 'Education',
        };
    }
}
