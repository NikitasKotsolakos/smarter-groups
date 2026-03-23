# Smarter Groups

A web application for intelligently assigning students to workshop groups based on their preferences, classroom constraints, and group capacity limits.

## Overview

Smarter Groups helps educators and workshop organizers automatically assign students to groups while respecting:
- **Student preferences** - Students can rank their preferred groups
- **Group capacity** - Minimum and maximum participant limits per group
- **Classroom distribution** - Optional limits on students from the same classroom in a group
- **Priority levels** - Groups can be prioritized to fill important workshops first

## Features

- **Workshop Management** - Create and manage multiple workshops with their own groups, classrooms, and students
- **CSV Import** - Bulk import students, classrooms, and preferences from CSV files
- **Smart Assignment Algorithm** - Priority-based greedy algorithm with dynamic adjustment
- **Manual Override** - Drag-and-drop interface to manually adjust assignments
- **Export** - Download assignments as CSV or Excel files
- **Multi-user** - Each user manages their own workshops

## Tech Stack

- **Backend**: PHP 8.4, Laravel 11
- **Database**: PostgreSQL
- **Frontend**: Blade templates, Tailwind CSS 3, Alpine.js
- **Testing**: Pest PHP
- **Authentication**: Laravel Breeze

## Requirements

- PHP 8.4+
- Composer
- Node.js & npm
- PostgreSQL

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd smarter-groups
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file and configure your database:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database connection in `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=smarter_groups
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Build frontend assets:
   ```bash
   npm run build
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

## Development

Run the development server with hot reloading:
```bash
composer run dev
```

Or run services separately:
```bash
php artisan serve
npm run dev
```

### Code Style

Format code using Laravel Pint:
```bash
vendor/bin/pint
```

### Testing

Run the test suite:
```bash
php artisan config:clear && php artisan test
```

Run specific tests:
```bash
php artisan test --filter=WorkshopTest
```

## Usage

### Creating a Workshop

1. Register/login to your account
2. Click "Create Workshop" on the dashboard
3. Add groups with capacity limits and priority levels
4. Add classrooms
5. Add students with their classroom assignment and group preferences

### CSV Import Format

Import students using a semicolon-separated CSV file:

```
Classroom;Student Name;Group A;Group B;Group C
Class 1;John Doe;1;;1
Class 1;Jane Smith;;1;
Class 2;Bob Wilson;1;1;
```

- First row: Headers with group names starting from column 3
- Column 1: Classroom name
- Column 2: Student name
- Columns 3+: Put `1` to indicate preference for that group

### Running the Algorithm

1. Ensure you have groups, classrooms, and students configured
2. Click "Run Assignment Algorithm" on the workshop page
3. Review the assignments in the Assignments tab
4. Make manual adjustments if needed
5. Export results to CSV or Excel

## Algorithm

The assignment algorithm uses a priority-based greedy approach:

1. **Sort groups** by priority level (lower number = higher priority)
2. **Reorder student preferences** based on group priority order
3. **Sort students** by preference urgency (students with fewer options processed first)
4. **Assign students** to their highest-preference available group
5. **Dynamic adjustment** - When a group reaches minimum capacity, its priority is lowered to allow other groups to fill

This approach balances filling high-priority groups first while ensuring all groups meet minimum capacity requirements.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
