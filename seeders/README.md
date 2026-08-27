Seeders usage

Files:

- seeder_refresh.sql — Truncates non-auth/domain tables and reinserts minimal baseline rows. Preserves `user`, `systemadmin`, `usertype`, `loginlog`, `ci_sessions`.
- seeder_demo.sql — Inserts demo data with explicit IDs (teachers, classes, sections, parents, students, transport, tmember).

How to run (MySQL):

1. Backup your database first.

2. From project root run (adjust credentials and database name):

```bash
mysql -u username -p database_name < seeders/seeder_refresh.sql
mysql -u username -p database_name < seeders/seeder_demo.sql
```

Notes:

- These scripts use explicit IDs starting at 100 to reduce collisions. If your target DB already has rows with these IDs, either change the IDs in the SQL files or run the refresh seeder first to clear the domain tables.
- Always test on a staging copy before applying to production.
- If you need different ID ranges or extra demo rows, tell me which tables to expand.
