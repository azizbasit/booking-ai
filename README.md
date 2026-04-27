# Booking AI

Booking AI is a Laravel-based appointment booking application that combines AI-assisted scheduling with call summaries and appointment management.

## Project Overview

- Built with Laravel 12 and PHP 8.2.
- Uses Tailwind CSS, Vite, and Alpine.js for the frontend.
- Supports appointment booking, AI assistant profiles, phone number management, and call summaries.
- Includes user authentication and configurable working hours.

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- Database: SQLite, MySQL, or compatible

## Setup

1. Copy the environment example:
   ```bash
   cp .env.example .env
   ```
2. Do not commit your local `.env` file or real credentials to GitHub.
   - `.env` is ignored by `.gitignore`
   - only `.env.example` should be tracked in the repository
3. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JavaScript dependencies:
   ```bash
   npm install
   ```
4. Generate the application key:
   ```bash
   php artisan key:generate
   ```
5. Run database migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   npm run dev
   php artisan serve
   ```

## Running Tests

- Run the Laravel test suite:
  ```bash
  php artisan test
  ```

## Available Scripts

- `npm run dev` - Start Vite development server
- `npm run build` - Build frontend assets for production
- `composer test` - Runs the Laravel test suite via Composer script

## License

This project is open source and licensed under the MIT License.
