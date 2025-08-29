# News Aggregation Backend

A comprehensive Laravel-based news aggregation system that fetches articles from multiple news sources and provides personalized news feeds.

## Features

- **User Authentication & Registration**: Secure user management with Laravel Sanctum
- **Multi-Source News Aggregation**: Integrates with NewsAPI, The Guardian, and New York Times
- **Advanced Search & Filtering**: Search by keywords, filter by source, category, and date range
- **Personalized News Feed**: Customizable preferences for sources and categories
- **Role-Based Access Control**: Built with Spatie Laravel Permission
- **RESTful API**: Complete API with proper validation and resources
- **Docker Support**: Full containerization with Docker Compose

## Tech Stack

- **Framework**: Laravel 12
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **API Documentation**: RESTful API with JSON responses
- **Containerization**: Docker & Docker Compose

## Prerequisites

- Docker and Docker Compose
- API keys for news services:
  - [NewsAPI](https://newsapi.org/)
  - [The Guardian](https://open-platform.theguardian.com/)
  - [New York Times](https://developer.nytimes.com/)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd qode-backend
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Configure environment variables**
   Edit `.env` file and add your API keys:
   ```env
   APP_NAME="News Aggregation"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=qode_news
   DB_USERNAME=qode_user
   DB_PASSWORD=qode_password

   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis

   REDIS_HOST=redis
   REDIS_PASSWORD=null
   REDIS_PORT=6379

   NEWS_API_KEY=your_news_api_key
   GUARDIAN_API_KEY=your_guardian_api_key
   NYTIMES_API_KEY=your_nytimes_api_key
   ```

4. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

5. **Install dependencies**
   ```bash
   docker-compose exec app composer install
   ```

6. **Generate application key**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

7. **Run migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

8. **Setup roles and permissions**
   ```bash
   docker-compose exec app php artisan app:setup-roles-and-permissions
   ```

9. **Fetch initial articles**
   ```bash
   docker-compose exec app php artisan app:fetch-initial-articles
   ```

## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout (requires auth)
- `GET /api/auth/me` - Get current user (requires auth)

### Articles
- `GET /api/articles` - Get latest articles
- `GET /api/articles/search` - Search articles with filters
- `GET /api/articles/personalized` - Get personalized feed (requires auth)
- `GET /api/articles/source/{source}` - Get articles by source
- `GET /api/articles/category/{category}` - Get articles by category
- `GET /api/articles/{id}` - Get specific article
- `POST /api/articles/refresh` - Refresh articles from sources (requires auth)

### User Preferences
- `GET /api/user/preferences` - Get user preferences (requires auth)
- `PUT /api/user/preferences` - Update user preferences (requires auth)
- `POST /api/user/sources/{source}/toggle` - Toggle news source (requires auth)
- `POST /api/user/categories/{category}/toggle` - Toggle news category (requires auth)
- `GET /api/user/sources` - Get user's active sources (requires auth)
- `GET /api/user/categories` - Get user's active categories (requires auth)

## API Usage Examples

### Register a new user
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Search articles
```bash
curl -X GET "http://localhost:8000/api/articles/search?keyword=technology&category=technology&per_page=10"
```

### Get personalized feed
```bash
curl -X GET http://localhost:8000/api/articles/personalized \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Update preferences
```bash
curl -X PUT http://localhost:8000/api/user/preferences \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sources": ["news_api", "the_guardian"],
    "categories": ["technology", "business"],
    "articles_per_page": 15
  }'
```

## Available News Sources

- `news_api` - NewsAPI
- `the_guardian` - The Guardian
- `new_york_times` - New York Times
- `bbc_news` - BBC News
- `open_news` - Open News
- `newscred` - NewsCred

## Available Categories

- `business` - Business news
- `technology` - Technology news
- `sports` - Sports news
- `entertainment` - Entertainment news
- `health` - Health news
- `science` - Science news
- `politics` - Political news
- `world` - World news
- `national` - National news
- `local` - Local news
- `opinion` - Opinion pieces
- `arts` - Arts and culture
- `food` - Food and dining
- `travel` - Travel news
- `education` - Education news

## Development

### Running tests
```bash
docker-compose exec app php artisan test
```

### Code formatting
```bash
docker-compose exec app ./vendor/bin/pint
```

### Database seeding
```bash
docker-compose exec app php artisan db:seed
```

### View logs
```bash
docker-compose logs -f app
```

## Architecture

The application follows Laravel best practices with:

- **Enums**: For type-safe constants (NewsSource, NewsCategory)
- **Repositories**: For data access abstraction
- **Services**: For business logic and external API integration
- **Resources**: For consistent API responses
- **Requests**: For input validation
- **Controllers**: For handling HTTP requests
- **Models**: For Eloquent ORM with relationships

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License.
