```shell
mysql -u root laravel_smarter_groups -p
```

```sql
  ALTER USER 'mysql'@'%' IDENTIFIED WITH mysql_native_password BY 'thepasswordofmysqluser';
  FLUSH PRIVILEGES;
  EXIT;
```
