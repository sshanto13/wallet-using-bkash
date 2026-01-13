# Wallet Integration Architecture

A resilient wallet system using bKash Tokenized Checkout with Laravel backend and Vue.js frontend.

## Features

- ✅ **Secure Authentication** - Laravel Sanctum API authentication
- ✅ **User Registration & Login** - Complete authentication system with registration form
- ✅ **i18n Support** - English and Bangla language support with persistence
- ✅ **Agreement Binding** - Create and store encrypted bKash agreements
- ✅ **Balance Injection** - Add balance with Redis atomic locks to prevent double-submissions
- ✅ **Refund Logic** - Partial refund support with transaction tracking
- ✅ **Transaction History** - Paginated transaction history with Vue.js
- ✅ **Beautiful UI** - Modern, responsive design with Tailwind CSS

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue.js 3
- **Styling**: Tailwind CSS
- **Database**: MySQL 8.0
- **Cache/Locks**: Redis (optional, can use file cache)
- **Authentication**: Laravel Sanctum
- **Build Tool**: Vite

## Architecture Decisions

### 1. Authentication & Security
- **Laravel Sanctum** for API token authentication
- Wallet agreement tokens are **encrypted at rest** using Laravel's Crypt facade
- User-scoped wallet operations ensure data isolation

### 2. Atomic Transactions & Idempotency
- **Redis locks** using Laravel's Cache::lock() to prevent double-submissions (optional)
- Database transactions ensure atomicity for balance updates
- Payment operations are locked per user during processing

### 3. Localization
- Laravel's built-in localization system with JSON files
- Language preference stored in user model and localStorage
- Supports English (en) and Bangla (bn)
- Default language is English

### 4. Frontend Architecture
- **Vue.js 3** with Composition API
- **Axios** for API communication
- **i18n Service** for translations
- **Responsive Design** with Tailwind CSS

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0
- Redis (optional, for distributed locks - can use file cache instead)

### Step 1: Clone the Repository

```bash
git clone <your-repository-url>
cd bkash_wallet-main
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Environment Configuration

Copy `.env.example` to `.env` and configure:

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database and bKash credentials:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:mk6NWc6cpD5pa74CdE0SZNCheGwvii8ZKJzJ/cC7GIA=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://127.0.0.1:8000/
SESSION_DOMAIN=127.0.0.1
SANCTUM_STATEFUL_DOMAINS=127.0.0.1,localhost


APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_using_bkash
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
#bkash sandbox integration by suman
BKASH_SANDBOX_BASE_URL=https://tokenized.sandbox.bka.sh/v1.2.0-beta
BKASH_AGREEMENT_CALLBACK_URL=http://localhost:8000/api/v1/bkash/agreement/callback
BKASH_PAYMENT_CALLBACK_URL=http://localhost:8000/api/v1/bkash/payment/callback
BKASH_SANDBOX_APP_KEY=4f6o0cjiki2rfm34kfdadl1eqq
BKASH_SANDBOX_APP_SECRET=2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b
BKASH_SANDBOX_USERNAME=sandboxTokenizedUser02
BKASH_SANDBOX_PASSWORD=sandboxTokenizedUser02@12345
BKASH_USE_DEMO_CALLBACK=true
#BKASH_SANDBOX_MERCHANT_NO=your_merchant_no

VITE_APP_NAME="${APP_NAME}"

GOTENBERG_URL=http://127.0.0.1:3000
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
```

### Step 4: Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE wallet_system;"

# Or using MySQL command line:
mysql -u root -p
CREATE DATABASE wallet_system;
EXIT;
```

### Step 5: Run Migrations

```bash
# Run all migrations
php artisan migrate

# Or with fresh database (drops all tables and re-runs migrations)
php artisan migrate:fresh
```

### Step 6: Run Seeders

```bash
# Run database seeders to create test user
php artisan db:seed

# Or run specific seeder
php artisan db:seed --class=UserSeeder
```

**Default Test User Credentials:**
- Email: `test@example.com`
- Password: `password123`
- Phone: `01952499680`

**Note:** You can also register a new user through the registration form on the login page.

### Step 7: Install Node Dependencies

```bash
npm install
```

### Step 8: Build Frontend Assets

```bash
# Development mode (with hot reload)
npm run dev

# Production build
npm run build
```

### Step 9: Start Laravel Server

```bash
# In a new terminal
php artisan serve
```

The application will be available at `http://localhost:8000`

### Step 10: Access the Application

1. Open your browser and navigate to `http://localhost:8000`
2. You'll see the login page with demo credentials displayed
3. Use the credentials shown on the page or register a new account

## Database Migrations

The following migrations are included:

1. `0001_01_01_000000_create_users_table.php` - Creates users table
2. `0001_01_01_000001_create_cache_table.php` - Creates cache table
3. `0001_01_01_000002_create_jobs_table.php` - Creates jobs table
4. `2026_01_11_181603_create_wallets_table.php` - Creates wallets table
5. `2026_01_11_181703_create_transactions_table.php` - Creates transactions table
6. `2026_01_11_183118_create_personal_access_tokens_table.php` - Creates personal access tokens table (for Sanctum)
7. `2026_01_13_100132_add_agreement_fields_to_wallets_table.php` - Adds agreement fields to wallets table

## Database Seeders

### UserSeeder

Creates a default test user:
- **Name**: Test User
- **Email**: test@example.com
- **Phone**: 01952499680
- **Password**: password123

To run seeders:
```bash
php artisan db:seed
```

## API Endpoints

### Authentication (Public)
- `POST /api/v1/register` - Register new user
  - Body: `{ "name": "string", "email": "string", "phone": "string", "password": "string", "password_confirmation": "string" }`
- `POST /api/v1/login` - Login user
  - Body: `{ "email": "string", "password": "string" }`

### Authentication (Protected)
- `POST /api/v1/logout` - Logout user (requires auth)

### Language (Public)
- `GET /api/v1/language/{lang}` - Get translations for a language (en/bn)

### Language (Protected)
- `POST /api/v1/language/preference` - Save language preference (requires auth)
- `GET /api/v1/language/preference` - Get user's language preference (requires auth)

### Wallet (Protected)
- `GET /api/v1/wallet` - Get wallet information (requires auth)
- `POST /api/v1/wallet/bind` - Create bKash agreement/bind wallet (requires auth)
- `POST /api/v1/wallet/topup` - Add balance to wallet (requires auth)
  - Body: `{ "amount": number }`
- `POST /api/v1/wallet/payment/check` - Check payment status (requires auth)
  - Body: `{ "payment_id": "string" }`
- `POST /api/v1/wallet/refund` - Process refund (requires auth)
  - Body: `{ "transaction_id": number, "amount": number, "reason": "string" }`
- `GET /api/v1/wallet/transactions` - Get transaction history (requires auth)
  - Query params: `page`, `per_page`, `type`, `status`, `from_date`, `to_date`
- `GET /api/v1/wallet/statement` - Generate wallet statement (requires auth)

### bKash Callbacks (Public)
- `GET /api/v1/bkash/agreement/callback` - bKash agreement callback
- `GET /api/v1/bkash/payment/callback` - bKash payment callback

## Usage Flow

### 1. User Registration/Login
- Register a new account through the registration form
- Or login with existing credentials
- Demo credentials are displayed on the login page:
  - Username: `suman.fintech@gmail.com`
  - Password: `123456`
- Language preference is stored and persisted

### 2. Create Agreement (Bind Wallet)
- Click "Bind Wallet" button
- System creates bKash agreement
- User completes OTP/PIN verification on bKash popup
- Agreement ID is encrypted and stored in database
- Wallet status becomes "Active"

### 3. Add Balance (Top Up)
- Enter amount to add (minimum 10 BDT)
- Click "Top Up" button
- System creates payment with stored agreement (no OTP needed)
- Redis/file lock prevents double-submissions
- Balance is updated atomically on success
- Transaction is recorded in history

### 4. View Transactions
- View paginated transaction history
- Filter by type (credit/debit/refund) and status (pending/success/failed)
- Filter by date range
- View transaction details including payment ID, amount, status, and date

### 5. Process Refund
- Select a successful credit transaction
- Click "Refund" button
- Enter refund amount (partial refunds supported)
- Provide refund reason
- System processes refund and updates balance
- Refund transaction is recorded

## Testing with bKash Sandbox

### Whitelisted Phone Numbers
- 01929918378
- 01619777283
- 01619777282
- 01823074817
- 01770618575
- 01952499680

### Test Credentials
- **OTP**: 123456
- **PIN**: 12121

### Important Notes
- Only whitelisted phone numbers work in sandbox
- Use the phone number during wallet binding
- OTP and PIN are fixed for sandbox testing

## Project Structure

```
bkash_wallet-main/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── BkashCallbackController.php
│   │   │   │   ├── LanguageController.php
│   │   │   │   └── WalletController.php
│   │   │   └── Controller.php
│   │   └── Middleware/
│   │       └── SetLocale.php
│   ├── Models/
│   │   ├── Transaction.php
│   │   ├── User.php
│   │   └── Wallet.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       ├── BkashService.php
│       └── BkashService_1.php
├── bootstrap/
│   ├── app.php
│   ├── cache/
│   └── providers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── cors.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_01_11_181603_create_wallets_table.php
│   │   ├── 2026_01_11_181703_create_transactions_table.php
│   │   ├── 2026_01_11_183118_create_personal_access_tokens_table.php
│   │   └── 2026_01_13_100132_add_agreement_fields_to_wallets_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
├── lang/
│   ├── en.json
│   └── bn.json
├── public/
│   ├── index.php
│   ├── favicon.ico
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   ├── components/
│   │   │   └── LanguageSwitcher.vue
│   │   ├── dashboard/
│   │   │   └── WalletDashboard.vue
│   │   └── services/
│   │       ├── api.js
│   │       └── i18n.js
│   └── views/
│       ├── bkash/
│       │   ├── callback-error.blade.php
│       │   └── callback-success.blade.php
│       ├── wallet.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── TestCase.php
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── postcss.config.js
├── tailwind.config.js
├── vite.config.js
└── README_SUMAN.md
```

## Security Considerations

1. **Encryption**: Agreement tokens are encrypted at rest using Laravel's encryption
2. **Atomic Locks**: Redis/file locks prevent race conditions in payment processing
3. **User Scoping**: All wallet operations are scoped to authenticated user
4. **Input Validation**: All API endpoints validate input data
5. **CSRF Protection**: Laravel's built-in CSRF protection for web routes
6. **Token Authentication**: Laravel Sanctum for secure API authentication

## Error Handling

- API errors return JSON responses with appropriate HTTP status codes
- Frontend displays user-friendly error messages via toast notifications
- Server-side logging for debugging (Laravel logs in `storage/logs/`)

## Troubleshooting

### Issue: Callback URL Error
- **Problem**: "Invalid Merchant Callback URL" error
- **Solution**: 
  1. Set `BKASH_CALLBACK_URL` in `.env` with full absolute URL
  2. For production, use `https://yourdomain.com/api/v1/bkash/agreement/callback`
  3. For local development, use ngrok or similar tool to expose localhost
  4. Run `php artisan config:clear` after updating `.env`

### Issue: Translations Not Working
- **Problem**: Text shows as keys instead of translations
- **Solution**: 
  1. Ensure `lang/en.json` and `lang/bn.json` exist
  2. Clear browser cache
  3. Check browser console for errors
  4. Default language is English - should work without changing language

### Issue: Database Connection Error
- **Problem**: Cannot connect to database
- **Solution**:
  1. Check database credentials in `.env`
  2. Ensure MySQL is running
  3. Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
  4. Run migrations: `php artisan migrate`

### Issue: Frontend Not Loading
- **Problem**: Blank page or JavaScript errors
- **Solution**:
  1. Run `npm install` to install dependencies
  2. Run `npm run build` to build assets
  3. Or run `npm run dev` for development with hot reload
  4. Clear browser cache

## Development Commands

```bash
# Start Laravel development server
php artisan serve

# Start Vite dev server (for hot reload)
npm run dev

# Build production assets
npm run build

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run tests
php artisan test
```

## Future Enhancements

- Webhook support for bKash callbacks
- Automated reconciliation jobs
- Transaction export (CSV/Excel)
- Multi-currency support
- Admin dashboard
- Email notifications
- SMS notifications

## License

MIT License

## Author

TestAg Systems - Laravel Test Project
