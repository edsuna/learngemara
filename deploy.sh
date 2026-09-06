#!/bin/bash
set -e

#######################################################################
# Deploy script for LearnGemara
#
# Usage:
#   ./deploy.sh                           # Deploy master to test
#   ./deploy.sh test                      # Deploy master to test
#   ./deploy.sh test design/bold-colorful # Deploy a branch to test
#   ./deploy.sh test master setup         # First-time setup on test
#   ./deploy.sh production                # Deploy master to production
#   ./deploy.sh production master setup   # First-time setup on production
#   ./deploy.sh copydb                    # Copy production DB to dev DB (on server)
#   ./deploy.sh copydb local              # Copy production DB to local prod-parity DB
#
# Prerequisites:
#   - SSH key-based access to the server
#   - Git repo cloned on the server (one-time: git clone)
#   - Node.js installed locally (server doesn't need it)
#
# One-time server setup (before first deploy):
#   ssh howtolearn@howtolearngemara.org
#   cd /home/howtolearn/www
#   git clone git@github.com:edsuna/learngemara.git dev
#   cd dev
#   cp .env.example .env
#   nano .env  # fill in DB, APP_URL, Google OAuth, etc.
#
# Production environment notes (already configured as of the L8->L13 cutover):
#   - App lives in /home/howtolearn/www/app/learngemara (docroot: .../public)
#   - Runs PHP 8.3 (set per-subdomain in the ICDSoft panel) + MySQL 8
#     (127.0.0.1:3308, db howtolearn_laravel)
#   - The CLI ioncube line in /home/howtolearn/php.ini is commented out so
#     composer/artisan run clean; the app does not use ioncube.
#######################################################################

# ── Config ───────────────────────────────────────────────────────────
SSH_USER="howtolearn"
SSH_HOST="howtolearngemara.org"
GIT_REPO="git@github.com:edsuna/learngemara.git"

# Test environment
TEST_PATH="/home/howtolearn/www/dev"
TEST_URL="https://dev.howtolearngemara.org"

# Production environment
# NOTE: the prod docroot is /home/howtolearn/www/app/learngemara/public; the
# Laravel app (git repo) lives in .../app/learngemara, NOT .../app (which is the
# subdomain docroot holding only an ICDSoft placeholder).
PROD_PATH="/home/howtolearn/www/app/learngemara"
PROD_URL="https://app.howtolearngemara.org"

# Local prod-parity database (see: ./deploy.sh copydb local)
LOCAL_PROD_DB="learngemara_prod"

# ── Parse arguments ──────────────────────────────────────────────────
ENV="${1:-test}"
BRANCH="${2:-master}"
ACTION="${3:-deploy}"

SSH_TARGET="$SSH_USER@$SSH_HOST"

# ── Handle "copydb local" (production → local prod-parity DB) ────────
if [ "$ENV" = "copydb" ] && [ "$BRANCH" = "local" ]; then
    LOCAL_DB="$LOCAL_PROD_DB"

    # Connection details come from this checkout's .env; only the database
    # name is overridden, so the prod-parity DB sits on the same local server
    # as the dev DB.
    get_local_env() {
        grep "^$1=" .env | head -1 | cut -d '=' -f2- | tr -d '"' | tr -d "'"
    }
    LOCAL_USER=$(get_local_env DB_USERNAME)
    LOCAL_PASS=$(get_local_env DB_PASSWORD)
    LOCAL_HOST=$(get_local_env DB_HOST)
    LOCAL_PORT=$(get_local_env DB_PORT)
    MYSQL_LOCAL="mysql -h$LOCAL_HOST -P$LOCAL_PORT -u$LOCAL_USER -p$LOCAL_PASS"

    echo "═══════════════════════════════════════════════════"
    echo "  Copying PRODUCTION database to local '$LOCAL_DB'"
    echo "═══════════════════════════════════════════════════"
    read -p "  This will OVERWRITE the local '$LOCAL_DB' database. Continue? [y/N] " confirm
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        echo "Aborted."
        exit 0
    fi

    DUMP_FILE=$(mktemp /tmp/learngemara_prod_XXXXXX.sql)
    trap 'rm -f "$DUMP_FILE"' EXIT

    echo ""
    echo ">> Dumping production database..."
    ssh "$SSH_TARGET" bash -s "$PROD_PATH" > "$DUMP_FILE" << 'PRODDUMP_EOF'
        PROD_PATH="$1"
        get_env() { grep "^$2=" "$1/.env" | head -1 | cut -d '=' -f2- | tr -d '"' | tr -d "'"; }
        mysqldump -h"$(get_env "$PROD_PATH" DB_HOST)" \
                  -P"$(get_env "$PROD_PATH" DB_PORT)" \
                  -u"$(get_env "$PROD_PATH" DB_USERNAME)" \
                  -p"$(get_env "$PROD_PATH" DB_PASSWORD)" \
                  --single-transaction --no-tablespaces \
                  "$(get_env "$PROD_PATH" DB_DATABASE)"
PRODDUMP_EOF
    echo "   Dump complete ($(wc -c < "$DUMP_FILE") bytes)"

    echo ""
    echo ">> Creating local database '$LOCAL_DB' if needed..."
    if ! $MYSQL_LOCAL -e "CREATE DATABASE IF NOT EXISTS \`$LOCAL_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; then
        echo ""
        echo "   Could not create '$LOCAL_DB' — the app DB user likely lacks CREATE rights."
        echo "   Create it once as an admin, then re-run:"
        echo "     sudo mysql -e \"CREATE DATABASE \\\`$LOCAL_DB\\\`; GRANT ALL ON \\\`$LOCAL_DB\\\`.* TO '$LOCAL_USER'@'localhost';\""
        exit 1
    fi

    echo ">> Importing into '$LOCAL_DB'..."
    $MYSQL_LOCAL "$LOCAL_DB" < "$DUMP_FILE"
    echo "   Import complete"

    # ── Verify: real COUNT(*) per table, not information_schema estimates ──
    echo ""
    echo ">> Verifying row counts (production vs local)..."
    PROD_COUNTS=$(ssh "$SSH_TARGET" bash -s "$PROD_PATH" << 'PRODCOUNT_EOF'
        PROD_PATH="$1"
        get_env() { grep "^$2=" "$1/.env" | head -1 | cut -d '=' -f2- | tr -d '"' | tr -d "'"; }
        DB=$(get_env "$PROD_PATH" DB_DATABASE)
        M="mysql -h$(get_env "$PROD_PATH" DB_HOST) -P$(get_env "$PROD_PATH" DB_PORT) -u$(get_env "$PROD_PATH" DB_USERNAME) -p$(get_env "$PROD_PATH" DB_PASSWORD)"
        for t in $($M -N -B -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$DB' AND table_type='BASE TABLE' ORDER BY table_name" 2>/dev/null); do
            echo "$t $($M -N -B -e "SELECT COUNT(*) FROM \`$t\`" "$DB" 2>/dev/null)"
        done
PRODCOUNT_EOF
)
    LOCAL_COUNTS=$(for t in $($MYSQL_LOCAL -N -B -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$LOCAL_DB' AND table_type='BASE TABLE' ORDER BY table_name" 2>/dev/null); do
        echo "$t $($MYSQL_LOCAL -N -B -e "SELECT COUNT(*) FROM \`$t\`" "$LOCAL_DB" 2>/dev/null)"
    done)

    if [ "$PROD_COUNTS" = "$LOCAL_COUNTS" ]; then
        echo "   OK — every table matches:"
        echo "$PROD_COUNTS" | sed 's/^/     /'
    else
        echo "   MISMATCH between production and local (< prod, > local):"
        diff <(echo "$PROD_COUNTS") <(echo "$LOCAL_COUNTS") | sed 's/^/     /'
        echo ""
        echo "   Do not trust this copy. mysqldump has silently under-dumped on"
        echo "   this host before — see the L8->L13 cutover notes."
        exit 1
    fi

    echo ""
    echo "═══════════════════════════════════════════════════"
    echo "  Production DB copied to local '$LOCAL_DB'"
    echo "═══════════════════════════════════════════════════"
    exit 0
fi

# ── Handle copydb command (production → dev, both on the server) ─────
if [ "$ENV" = "copydb" ]; then
    echo "═══════════════════════════════════════════════════"
    echo "  Copying production DB to dev DB on server"
    echo "═══════════════════════════════════════════════════"
    read -p "  This will OVERWRITE the dev database. Continue? [y/N] " confirm
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        echo "Aborted."
        exit 0
    fi

    ssh "$SSH_TARGET" bash -s "$PROD_PATH" "$TEST_PATH" << 'COPYDB_EOF'
        PROD_PATH="$1"
        TEST_PATH="$2"

        # Read DB credentials from each .env
        get_env() {
            grep "^$2=" "$1/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'"
        }

        PROD_DB=$(get_env "$PROD_PATH" DB_DATABASE)
        PROD_USER=$(get_env "$PROD_PATH" DB_USERNAME)
        PROD_PASS=$(get_env "$PROD_PATH" DB_PASSWORD)

        DEV_DB=$(get_env "$TEST_PATH" DB_DATABASE)
        DEV_USER=$(get_env "$TEST_PATH" DB_USERNAME)
        DEV_PASS=$(get_env "$TEST_PATH" DB_PASSWORD)

        echo "   Production DB: $PROD_DB -> Dev DB: $DEV_DB"
        echo ""
        echo ">> Dumping production database..."
        mysqldump -u"$PROD_USER" -p"$PROD_PASS" "$PROD_DB" > /tmp/learngemara_prod_dump.sql
        echo "   Dump complete ($(wc -c < /tmp/learngemara_prod_dump.sql) bytes)"

        echo ""
        echo ">> Importing into dev database..."
        mysql -u"$DEV_USER" -p"$DEV_PASS" "$DEV_DB" < /tmp/learngemara_prod_dump.sql
        echo "   Import complete"

        rm -f /tmp/learngemara_prod_dump.sql
COPYDB_EOF

    echo ""
    echo "═══════════════════════════════════════════════════"
    echo "  Production DB copied to dev DB"
    echo "═══════════════════════════════════════════════════"
    exit 0
fi

# ── Standard deploy ──────────────────────────────────────────────────
if [ "$ENV" = "production" ]; then
    REMOTE_PATH="$PROD_PATH"
    REMOTE_URL="$PROD_URL"
elif [ "$ENV" = "test" ]; then
    REMOTE_PATH="$TEST_PATH"
    REMOTE_URL="$TEST_URL"
else
    echo "Usage: $0 [test|production] [branch] [setup]"
    echo "       $0 copydb          # production DB -> dev DB (on the server)"
    echo "       $0 copydb local    # production DB -> local $LOCAL_PROD_DB"
    exit 1
fi

# ── Safety: deploy only from the main checkout ───────────────────────
# The prod-parity checkout is a linked worktree on the 'production' branch.
# It exists to reproduce what is live, never to publish from.
if [ "$(cd "$(git rev-parse --git-common-dir)" && pwd)" != "$(cd "$(git rev-parse --git-dir)" && pwd)" ]; then
    echo "ERROR: this is a linked git worktree, not the main checkout."
    echo "       Deploy from the main checkout instead."
    exit 1
fi
if [ "$(git branch --show-current)" = "production" ]; then
    echo "ERROR: refusing to deploy while on the 'production' branch."
    echo "       That branch records what is live; it is not a source to deploy from."
    exit 1
fi

echo "═══════════════════════════════════════════════════"
echo "  Environment: $ENV ($REMOTE_URL)"
echo "  Branch:      $BRANCH"
echo "  Server:      $SSH_TARGET:$REMOTE_PATH"
echo "═══════════════════════════════════════════════════"

# ── Safety check for production ──────────────────────────────────────
if [ "$ENV" = "production" ]; then
    read -p "  You are deploying to PRODUCTION. Continue? [y/N] " confirm
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        echo "Aborted."
        exit 0
    fi
fi

# ── Step 1: Build frontend locally ──────────────────────────────────
echo ""
echo ">> Checking out $BRANCH locally..."
ORIGINAL_BRANCH=$(git branch --show-current)
git checkout "$BRANCH"
git pull origin "$BRANCH" || true

echo ""
echo ">> Building frontend assets locally..."
source "$HOME/.nvm/nvm.sh" 2>/dev/null || true
npm run build
echo "   Build complete"

# ── Step 2: Pull branch on server ───────────────────────────────────
echo ""
echo ">> Pulling $BRANCH on server..."
ssh "$SSH_TARGET" bash -s "$REMOTE_PATH" "$BRANCH" << 'PULL_EOF'
    REMOTE_PATH="$1"
    BRANCH="$2"
    cd "$REMOTE_PATH"
    eval "$(ssh-agent -s)"
    ssh-add ~/.ssh/github2026

    git fetch origin
    git checkout "$BRANCH"
    # Hard-reset to the remote so the deploy is deterministic and never
    # blocks on local changes to tracked files (e.g. storage/.gitignore).
    # Gitignored files (.env, vendor, storage, public/build) are preserved.
    git reset --hard "origin/$BRANCH"
PULL_EOF
echo "   Pull complete"

# ── Step 3: Upload build artifacts ──────────────────────────────────
echo ""
echo ">> Uploading build artifacts..."
rsync -avz --delete \
    public/build/ "$SSH_TARGET:$REMOTE_PATH/public/build/"
echo "   Upload complete"

# ── Step 4: Run remote commands ──────────────────────────────────────
if [ "$ACTION" = "setup" ]; then
    echo ""
    echo ">> Running first-time setup on server..."
    ssh "$SSH_TARGET" bash -s "$REMOTE_PATH" << 'SETUP_EOF'
        REMOTE_PATH="$1"
        cd "$REMOTE_PATH"

        # Install composer dependencies
        composer install --no-dev --optimize-autoloader

        # Create storage directories if missing
        mkdir -p storage/logs
        mkdir -p storage/framework/{cache/data,sessions,views,testing}
        mkdir -p storage/app/{private,public}
        mkdir -p bootstrap/cache

        # Set permissions
        chmod -R 775 storage bootstrap/cache

        # Generate app key if .env exists but has no key
        if [ -f .env ] && grep -q "APP_KEY=$" .env; then
            php artisan key:generate
        fi

        # Run migrations
        php artisan migrate --force

        # Seed the database
        php artisan db:seed --force

        # Create storage symlink
        php artisan storage:link 2>/dev/null || true

        # Clear and cache
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache

        echo "   Setup complete"
SETUP_EOF

else
    echo ""
    echo ">> Running post-deploy commands on server..."
    ssh "$SSH_TARGET" bash -s "$REMOTE_PATH" << 'DEPLOY_EOF'
        REMOTE_PATH="$1"
        cd "$REMOTE_PATH"

        # Install/update composer dependencies
        composer install --no-dev --optimize-autoloader

        # Run any new migrations
        php artisan migrate --force

        # Clear and rebuild caches
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache

        echo "   Deploy complete"
DEPLOY_EOF

fi

# ── Step 5: Record what is now live ─────────────────────────────────
if [ "$ENV" = "production" ]; then
    echo ""
    echo ">> Recording deployed commit on the 'production' branch..."
    DEPLOYED_SHA=$(git rev-parse "$BRANCH")
    git push --force-with-lease origin "$DEPLOYED_SHA:refs/heads/production"
    if git branch -f production "$DEPLOYED_SHA" 2>/dev/null; then
        echo "   production -> $(git rev-parse --short production)"
    else
        echo "   origin/production updated. The local branch is checked out in the"
        echo "   prod-parity worktree; refresh it there with:"
        echo "     git -C ~/learngemara-prod pull --ff-only"
    fi
fi

# ── Step 6: Restore local branch ────────────────────────────────────
if [ "$ORIGINAL_BRANCH" != "$BRANCH" ]; then
    echo ""
    echo ">> Restoring local branch to $ORIGINAL_BRANCH..."
    git checkout "$ORIGINAL_BRANCH"
fi

echo ""
echo "═══════════════════════════════════════════════════"
echo "  Deployed $BRANCH to $REMOTE_URL"
echo "═══════════════════════════════════════════════════"
