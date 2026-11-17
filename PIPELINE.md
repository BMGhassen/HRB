# Pipeline: B2B ⇄ iDempiere

| Part | Where | What it does |
|---|---|---|
| Order sending | `app/Jobs/SendCommandeToErp.php` | Validated orders are queued and sent to iDempiere, retried 5 times, resend button in admin |
| Syncs | `app/Services/Idempiere*Sync.php` + scheduler | Stock every 10 min, catalog hourly (changes only), full catalog nightly at 02:00; clients from the admin page |
| Tests | `tests/` | SQLite in memory + fake iDempiere: never touch MySQL or the VM |
| Laravel CI | `.github/workflows/laravel.yml` | Tests and asset build on every push / PR |
| Plugin CI/CD | `.github/workflows/erp-plugins.yml`, `erp-plugins/` | Builds the iDempiere plugins; deploys to the VM on demand |
| Dev setup | `docker-compose.yml` | App, queue worker, scheduler and MySQL with one command |

## Run the tests

```bash
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit
```

(Or enable `pdo_sqlite` and `sqlite3` in `php.ini` and run `vendor/bin/phpunit`.)

## Local dev with Docker

```bash
docker compose up -d                      # http://localhost:8000
docker compose --profile assets up -d     # + Vite hot reload
docker compose restart queue              # after changing a Job or a Service
docker compose logs -f queue scheduler
```

Your `.env` is used for everything except the database (MySQL container, port 3307 on your PC).
To copy your Laragon data into it:

```bash
mysqldump -u root <laragon_db_name> > dump.sql
docker compose exec -T mysql mysql -uhrb2b -phrb2b hrb2b < dump.sql
```

**`IDEMPIERE_URL` in `.env` is the real VM:** validating an order creates real documents there.
Use a client whose `code_client` is a test customer.

## iDempiere plugins

Plugin sources live in `erp-plugins/<Plugin>/` (Eclipse: *File → Import → Existing Projects*, pick `erp-plugins/Forms`).

Build locally against your local iDempiere install:

```bash
bash erp-plugins/build.sh erp-plugins/Forms C:/idempiere/plugins dist
```

### GitHub setup (once)

Repository → Settings → Secrets and variables → Actions:

| Secret | Value |
|---|---|
| `VM_HOST` | VM IP or host name |
| `VM_USER` | SSH user on the VM |
| `VM_SSH_KEY` | private key of a key pair made for GitHub (`ssh-keygen -t ed25519 -f gh_deploy`), public key added to the VM user's `~/.ssh/authorized_keys` |
| `VM_KNOWN_HOSTS` | output of `ssh-keyscan <VM_HOST>` |

| Variable (optional) | Default |
|---|---|
| `IDEMPIERE_HOME` | `/opt/idempiere-server` |
| `IDEMPIERE_SERVICE` | `idempiere` (systemd service) |
| `IDEMPIERE_BUILD` | change it after upgrading iDempiere on the VM, so CI downloads the new jars |

Settings → Environments → create `production` and add yourself as required reviewer, so every deploy waits for your click.

The VM user must be able to write to `IDEMPIERE_HOME` and run `sudo systemctl restart idempiere` without a password
(e.g. `/etc/sudoers.d/idempiere-deploy`: `<user> ALL=(root) NOPASSWD: /bin/systemctl restart idempiere`).

### Deploy

Actions → *iDempiere plugins* → *Run workflow* → tick **Deploy**.
It builds, copies the jar, updates `bundles.info`, restarts iDempiere (≈1 min downtime) and checks `/webui/` answers.
If it doesn't, the previous jar is put back automatically. Backups: `$IDEMPIERE_HOME/plugin-backups/`.

SQL in `erp-plugins/*/sql/` is **not** run automatically.
