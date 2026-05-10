# Booking AI

**Booking AI** is a Laravel-based appointment and voice assistant application built for physiotherapy clinics. It connects AI assistant profiles, Twilio phone numbers, and VAPI voice call flows to manage bookings, cancellations, and call summaries.

## Features

- Create AI-driven assistant profiles for physiotherapy booking workflows
- Purchase and assign Twilio phone numbers for new clinic assistants
- Initiate outbound voice calls using VAPI
- Store appointment bookings and monitor scheduled appointments
- Stream voice recordings from the public storage path
- Admin dashboard, calendar and call monitor views
- Database-backed sessions and cache support

## Project Structure

- `app/Http/Controllers` — core controllers for assistants, appointments, call flows, and VoIP integration
- `app/Models` — domain models including `Appointment`, `AiAssistantsProfile`, `CallSummary`, and `PhoneNumber`
- `routes/web.php` — application UI and admin routes
- `routes/api.php` — voice assistant webhook endpoint
- `resources/views` — welcome page, admin pages, and shared UI templates
- `database/migrations` — initial tables for users, jobs, cache, sessions and application data

## Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL (or another supported database)
- `pdo_mysql` PHP extension enabled
- Laravel-compatible web server / Laragon / Valet

## Local Setup

From the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

If you want to run development assets and the local server:

```bash
npm run dev
php artisan serve
```

## Environment Variables

Update `.env` with your local database and AI/call integration credentials.

Required values include:

```env
APP_NAME=Booking AI
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking-ai
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database

# VAPI credentials
VAPI_API_KEY=your-vapi-api-key
VAPI_ASSISTANT_ID=your-vapi-assistant-id
VAPI_PHONE_NUMBER_ID=your-vapi-phone-number-id
```

> Note: `VAPI_API_KEY`, `VAPI_ASSISTANT_ID`, and `VAPI_PHONE_NUMBER_ID` are used by the voice call controller and VAPI assistant creation flow.

## Common Commands

```bash
php artisan key:generate
php artisan migrate
php artisan migrate:status
php artisan serve
npm install
npm run build
npm run dev
```

## Important Routes

- `GET /` — public welcome page
- `GET /admin-page` — admin dashboard
- `GET /calendar` — calendar view (auth required)
- `GET /appointments` — user appointments list (auth required)
- `POST /ai-assistants-profiles` — create new AI assistant profile
- `POST /initiate-call` — start a VAPI voice call
- `GET /call-monitor` — call monitoring page
- `GET /call-summaries` — list call summaries
- `GET /recordings/{filename}` — stream stored voice recordings
- `POST /api/assistant-calls` — VAPI assistant webhook endpoint

## Notes

- The app uses database sessions by default, so migrations must run before the application can store session data.
- The admin home redirect is set to `admin-page` in `app/Providers/RouteServiceProvider.php`.
- `resources/views/welcome.blade.php` contains the public-facing landing page.

## Troubleshooting

- If you see `could not find driver`, enable `pdo_mysql` in your active PHP `php.ini`
- If `sessions` table is missing, run `php artisan migrate`
- If the VAPI call fails, verify the `.env` credentials and the VAPI service settings

## License

This project is released under the MIT License.
