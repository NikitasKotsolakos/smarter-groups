# Database Initialization Commands

## Current setup

- **Production and development both run MySQL.**
- **PostgreSQL is the longer-term preference** — there was an unresolved issue that blocked switching, so it's parked for now. Some local setups may already be running Postgres in the meantime; production is MySQL either way.

## One-time MySQL user / database setup

CREATE DATABASE IF NOT EXISTS laravel_smarter_groups;
CREATE USER IF NOT EXISTS 'laravel'@'%' IDENTIFIED BY 'the_password';
GRANT ALL PRIVILEGES ON laravel_smarter_groups to laravel;
