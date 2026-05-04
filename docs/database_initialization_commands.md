# Database Initialization Commands

## Current setup

- **Production and development both run MySQL.**
- **PostgreSQL is the longer-term preference** — there was an unresolved issue that blocked switching, so it's parked for now. Some local setups may already be running Postgres in the meantime; production is MySQL either way.

## One-time MySQL user setup

Connect to the database:

```shell
mysql -u root laravel_smarter_groups -p
```

If MySQL 8+ rejects the application user with an auth-plugin error, switch the user to `mysql_native_password`:

```sql
ALTER USER 'mysql'@'%' IDENTIFIED WITH mysql_native_password BY 'thepasswordofmysqluser';
FLUSH PRIVILEGES;
EXIT;
```

Replace `mysql` with the username from your `.env` (`DB_USERNAME`) and use the corresponding password.
