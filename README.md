# Translation Management Service

A high-performance API for managing translation strings across multiple locales.

## Features

- Store translations for multiple locales (e.g., en, fr, es)
- Tag translations for context (e.g., mobile, desktop, web)
- API endpoints for CRUD operations
- Search translations by tags, keys, or content
- JSON export endpoint for frontend applications
- Optimized for performance and scalability

## Requirements

- PHP >= 8.2.27
- MySQL 8.0+
- Composer

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/shahrukh862985/translation-service.git
   cd translation-service
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Set up environment variables:
   ```
   cp .env.example .env
   ```
   
4. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=translation_service
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Generate application key:
   ```
   php artisan key:generate
   ```

6. Run migrations:
   ```
   php artisan migrate
   ```

7. Generate test data (optional):
   ```
   php artisan translations:seed 30000 1 //for en lang
   php artisan translations:seed 30000 1 //for es lang
   php artisan translations:seed 30000 1 //for fr lang
   ```

## API Endpoints

### Translations

- `GET /api/translations` - List translations with filtering options
  - Query parameters: `key`, `locale`, `content`, `tags` (array),
- `GET /api/translations/{id}` - Get a single translation
- `POST /api/translations` - Create a new translation
  - Body: `key`, `locale`, `content`, `tags` (array)
- `PUT /api/translations/{id}` - Update a translation
  - Body: `key`, `locale`, `content`, `tags` (array)
- `DELETE /api/translations/{id}` - Delete a translation

### Exports

- `GET /api/export/translations/{locale?}` - Export translations as nested JSON
  - Returns all locales if no locale specified

## Performance Optimizations

- Database indexes on frequently queried columns
- Response caching for export endpoints
- Efficient query building with pagination
- Batch processing for large datasets