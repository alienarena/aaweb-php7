# aaweb-php7

# Test data

[aa-mysql-dump.sql.gz](./aa-mysql-dump.sql.gz) contains a test database with
real data. Load it and use it like this:

* Create the database

```
$ echo "create database testaa;" | mysql -u root -p
$ echo "grant all on testaa.* to 'your_db_user'@'localhost';" | mysql -u root -p
```

* Load the database

```
$ zcat aa-mysql-dump.sql.gz | mysql -u your_db_user -p
```

* Set the database in [config.php](./config.php)

```
...
$CONFIG['dbHost'] = 'localhost';
$CONFIG['dbName'] = 'testaa'; ###
$CONFIG['dbUser'] = 'your_db_username_here';
$CONFIG['dbPass'] = 'your_db_password_here';
...
```
* Should work.

# Troubleshooting

You may get "NO DATA" appearing in the usage graphs.

The 'stats' table may be unitialized. There should be a single row in 'stats', which
represents the time of the last update.

From mysql, try
```
insert stats (id, lastupdated) values (0,0);
```
and refresh the browser page.

If that doesn't help:

Check the Apache2 (or other server's) error log. Example location:
/var/log/apache2/error.log. If you see something like:

```
[Sat Nov 25 19:41:32.805657 2017] [:error] [pid 13236] [client
108.51.38.236:55034] PHP Notice: error:Expression #2 of SELECT list is
not in GROUP BY clause and contains nonaggregated column
'aa.serverlog.realplayers' which is not functionally dependent on
columns in GROUP BY clause; this is incompatible with
sql_mode=only_full_group_by in .../aaweb/browser/graph.php on line 108, referer: ...
```

you need to eliminate the `only_full_group_by` setting in MySql.

* Add the following lines to your my.cnf (try /etc/mysql/my.cnf) file

```
[mysqld]
sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
```

* Restart Mysql

```
$ sudo /etc/init.d/mysql restart

# or maybe

$ sudo systemctl restart mysql
```


