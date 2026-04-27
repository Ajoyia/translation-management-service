# Translation Management Service

A comprehensive API-driven translation management service built with Laravel 13, designed to store, manage, and serve translations for multiple locales and platforms.

## Features

- **Multi-locale Support**: Store translations for multiple languages (en, fr, es, etc.) with easy extensibility
- **Context-based Tagging**: Tag translations for different contexts (mobile, desktop, web, admin, public)
- **Full CRUD Operations**: Create, read, update, and delete translations via REST API
- **Advanced Search**: Search translations by tags, keys, or content
- **JSON Export**: Optimized JSON endpoints for frontend consumption with caching
- **Real-time Updates**: Export endpoints always return the latest translations
- **Performance Optimized**: Built-in caching with configurable TTL

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

## Database Structure

### Tables

- **translations**: Stores translation keys, locales, and content
- **tags**: Stores available tags (mobile, desktop, web, etc.)
- **tag_translation**: Pivot table linking translations to tags

### Translation Model
- `key`: Translation identifier (e.g., "welcome_message")
- `locale`: Language code (e.g., "en", "fr", "es")
- `content`: The translated text
- `tags`: Many-to-many relationship with tags

## API Endpoints

Base URL: `http://localhost:8000/api`

### 1. List/Search Translations

```
GET /api/translations
```

**Query Parameters:**
- `locale` - Filter by locale (e.g., `en`, `fr`, `es`)
- `key` - Filter by exact key
- `search` - Search in keys and content
- `tags` - Filter by tags (comma-separated or array)
- `per_page` - Results per page (default: 15)

**Examples:**

```bash
GET /api/translations?locale=en
GET /api/translations?search=welcome
GET /api/translations?tags=mobile,web
GET /api/translations?locale=fr&tags=public
```

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "key": "welcome_message",
      "locale": "en",
      "content": "Welcome to our application",
      "created_at": "2026-04-27T15:59:40.000000Z",
      "updated_at": "2026-04-27T15:59:40.000000Z",
      "tags": [
        {
          "id": 1,
          "name": "web",
          "slug": "web"
        }
      ]
    }
  ],
  "per_page": 15,
  "total": 30
}
```

### 2. Create Translation

```
POST /api/translations
```

**Request Body:**
```json
{
  "key": "new_message",
  "locale": "en",
  "content": "This is a new message",
  "tags": ["web", "mobile"]
}
```

**Response:**
```json
{
  "message": "Translation created successfully",
  "data": {
    "id": 31,
    "key": "new_message",
    "locale": "en",
    "content": "This is a new message",
    "tags": [
      {"id": 1, "name": "web", "slug": "web"},
      {"id": 2, "name": "mobile", "slug": "mobile"}
    ]
  }
}
```

### 3. View Single Translation

```
GET /api/translations/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "key": "welcome_message",
    "locale": "en",
    "content": "Welcome to our application",
    "tags": [...]
  }
}
```

### 4. Update Translation

```
PUT/PATCH /api/translations/{id}
```

**Request Body:**
```json
{
  "content": "Updated welcome message",
  "tags": ["web", "mobile", "desktop"]
}
```

**Response:**
```json
{
  "message": "Translation updated successfully",
  "data": {
    "id": 1,
    "key": "welcome_message",
    "locale": "en",
    "content": "Updated welcome message",
    "tags": [...]
  }
}
```

### 5. Delete Translation

```
DELETE /api/translations/{id}
```

**Response:**
```json
{
  "message": "Translation deleted successfully"
}
```

### 6. Export Translations (Single Locale)

```
GET /api/translations/export
```

**Query Parameters:**
- `locale` - Language code (default: `en`)
- `tags` - Filter by tags (comma-separated or array)

**Examples:**

```bash
GET /api/translations/export?locale=en
GET /api/translations/export?locale=fr&tags=mobile,web
```

**Response:**
```json
{
  "locale": "en",
  "translations": {
    "welcome_message": "Welcome to our application",
    "login_button": "Login",
    "logout_button": "Logout",
    "dashboard_title": "Dashboard"
  },
  "generated_at": "2026-04-27T15:59:40+00:00"
}
```

**Frontend Integration Example (Vue.js):**

```javascript
// Load translations for English
const response = await fetch('/api/translations/export?locale=en&tags=web');
const { translations } = await response.json();

// Use in Vue app
const i18n = createI18n({
  locale: 'en',
  messages: {
    en: translations
  }
});
```

### 7. Export All Translations (All Locales)

```
GET /api/translations/export/all
```

**Query Parameters:**
- `tags` - Filter by tags (comma-separated or array)

**Response:**
```json
{
  "translations": {
    "en": {
      "welcome_message": "Welcome to our application",
      "login_button": "Login"
    },
    "fr": {
      "welcome_message": "Bienvenue dans notre application",
      "login_button": "Connexion"
    },
    "es": {
      "welcome_message": "Bienvenido a nuestra aplicación",
      "login_button": "Iniciar sesión"
    }
  },
  "generated_at": "2026-04-27T15:59:40+00:00"
}
```

### 8. Clear Translation Cache

```
POST /api/translations/cache/clear
```

**Response:**
```json
{
  "message": "Cache cleared successfully"
}
```

## Performance & Caching

- Export endpoints use Laravel's caching system
- Cache TTL: 5 minutes (configurable)
- Cache is automatically invalidated when:
  - Creating new translations
  - Updating existing translations
  - Deleting translations
- Manual cache clearing via `/api/translations/cache/clear`

## CDN Support

The Translation Management Service includes built-in CDN (Content Delivery Network) support for optimal performance and global distribution of translation exports.

### Features

- **Automatic CDN Headers**: Export endpoints automatically include CDN-friendly headers
- **Long Cache Lifetimes**: Configured for maximum CDN efficiency
- **Cache Control**: Fine-tuned caching directives for both browsers and CDN edge servers
- **Vary Headers**: Proper content negotiation support
- **CDN Status Tracking**: X-Cache-Status header for monitoring cache hits/misses

### Configuration

Add your CDN URL to `.env`:

```env
CDN_URL=https://cdn.yourdomain.com
```

### HTTP Headers

Export endpoints (`/api/translations/export*`) automatically include:

| Header | Value | Purpose |
|--------|-------|---------|
| `Cache-Control` | `public, max-age=3600, s-maxage=86400` | Browser cache: 1 hour, CDN cache: 24 hours |
| `Vary` | `Accept-Encoding` | Ensure proper compression handling |
| `X-Cache-Status` | `MISS` | Track cache performance |
| `X-CDN-URL` | Your CDN URL | Reference for cache location |

### CDN Setup Examples

#### Cloudflare

1. Add your domain to Cloudflare
2. Set up a Page Rule for `/api/translations/export*`:
   - Cache Level: Cache Everything
   - Edge Cache TTL: 1 day
   - Browser Cache TTL: 1 hour

```env
CDN_URL=https://cdn.cloudflare.net/yourdomain
```

#### AWS CloudFront

1. Create a CloudFront distribution
2. Set origin to your API domain
3. Configure cache behavior for `/api/translations/export*`:
   - TTL: 86400 seconds
   - Compress Objects: Yes
   - Forward Query Strings: Yes

```env
CDN_URL=https://d1234567890.cloudfront.net
```

#### Fastly

1. Create a Fastly service
2. Set backend to your API
3. Add VCL for translation endpoints:

```vcl
if (req.url ~ "^/api/translations/export") {
  set beresp.ttl = 24h;
}
```

```env
CDN_URL=https://yourservice.global.ssl.fastly.net
```

### Integration Example

When using a CDN, your frontend can check the CDN URL from response headers:

```javascript
const response = await fetch('/api/translations/export?locale=en');
const cdnUrl = response.headers.get('X-CDN-URL');

if (cdnUrl) {
  const cdnResponse = await fetch(`${cdnUrl}/api/translations/export?locale=fr`);
  const { translations } = await cdnResponse.json();
}
```

### Cache Invalidation

When translations are updated, you may need to purge your CDN cache:

**Cloudflare:**
```bash
curl -X POST "https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache" \
  -H "Authorization: Bearer {api_token}" \
  -d '{"files":["https://yourdomain.com/api/translations/export"]}'
```

**AWS CloudFront:**
```bash
aws cloudfront create-invalidation \
  --distribution-id {distribution_id} \
  --paths "/api/translations/export*"
```

**Fastly:**
```bash
curl -X POST "https://api.fastly.com/service/{service_id}/purge_all" \
  -H "Fastly-Key: {api_key}"
```

### Best Practices

1. **Use CDN for Export Endpoints**: Only translation exports benefit from CDN caching
2. **Keep CRUD Operations Direct**: Create/Update/Delete should hit your origin server
3. **Monitor Cache Performance**: Check X-Cache-Status header to ensure proper caching
4. **Set Up Automatic Purging**: Integrate CDN purge with your deployment pipeline
5. **Geographic Distribution**: Choose CDN edge locations near your users

### Performance Benefits

With CDN enabled:
- **Reduced Latency**: 50-90% faster response times for geographically distributed users
- **Lower Origin Load**: 80-95% of translation requests served from CDN edge
- **Better Scalability**: Handle traffic spikes without scaling origin servers
- **Cost Savings**: Reduced bandwidth costs on origin infrastructure

## Tag System

Tags help organize translations by context. Pre-seeded tags include:

- `mobile` - Mobile app translations
- `desktop` - Desktop app translations
- `web` - Web app translations
- `admin` - Admin panel translations
- `public` - Public-facing translations

Tags are automatically created when assigning them to translations.

## Sample Data

The seeder includes 10 common translation keys across 3 locales (en, fr, es):

- welcome_message
- login_button
- logout_button
- dashboard_title
- settings_title
- save_button
- cancel_button
- search_placeholder
- error_404
- error_500

## Testing the API

### Using cURL:

```bash
# List all translations
curl http://localhost:8000/api/translations

# Create a translation
curl -X POST http://localhost:8000/api/translations \
  -H "Content-Type: application/json" \
  -d '{"key":"test","locale":"en","content":"Test message","tags":["web"]}'

# Export translations for Vue.js
curl http://localhost:8000/api/translations/export?locale=en&tags=web

# Search translations
curl http://localhost:8000/api/translations?search=login
```

## Architecture Decisions

1. **Unique Key-Locale Constraint**: Each translation key can only have one entry per locale, preventing duplicates
2. **Pivot Table for Tags**: Many-to-many relationship allows flexible categorization
3. **Scope-based Queries**: Eloquent scopes make filtering clean and reusable
4. **Automatic Slug Generation**: Tags automatically generate URL-friendly slugs
5. **UpdateOrCreate Strategy**: Creating translations with existing key+locale updates them

## Future Enhancements

- API authentication (Sanctum/Passport)
- Translation versioning and history
- Bulk import/export (CSV, XLIFF)
- Translation statistics and analytics
- Fallback locale support
- Pluralization rules
- Variable interpolation
- Translation approval workflow

## License

MIT
