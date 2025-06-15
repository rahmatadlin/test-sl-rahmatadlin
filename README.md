# Test SL Rahmatadlin

## Getting Started

1. Clone the repository:
```bash
git clone https://github.com/rahmatadlin/test-sl-rahmatadlin.git
cd test-sl-rahmatadlin
```

## Screenshots

![Screenshot 2](Screenshot%20from%202025-06-15%2022-57-29.png)
![Screenshot 1](Screenshot%20from%202025-06-15%2022-57-17.png)

## Backend Setup

1. Install PHP dependencies:
```bash
composer install
composer update
```

2. Run database migrations:
```bash
php vendor/bin/doctrine-migrations migrate
```

3. Run server:
```bash
composer start
```

## Frontend Setup

1. Install Node.js dependencies:
```bash
npm install
```

2. Start the development server:
```bash
npm run dev
```

## Development

- Backend runs on: http://localhost:8000
- Frontend runs on: http://localhost:5173

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js 16 or higher
- npm or yarn
- MySQL/MariaDB
