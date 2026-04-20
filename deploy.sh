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
#   ./deploy.sh copydb                    # Copy production DB to dev DB
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
#######################################################################

# ── Config ───────────────────────────────────────────────────────────
SSH_USER="howtolearn"
SSH_HOST="howtolearngemara.org"
GIT_REPO="git@github.com:edsuna/learngemara.git"

# Test environment
TEST_PATH="/home/howtolearn/www/dev"
TEST_URL="https://dev.howtolearngemara.org"

# Production environment
PROD_PATH="/home/howtolearn/www/app"
PROD_URL="https://app.howtolearngemara.org"

# ── Parse arguments ──────────────────────────────────────────────────
ENV="${1:-test}"
BRANCH="${2:-master}"
ACTION="${3:-deploy}"

SSH_TARGET="$SSH_USER@$SSH_HOST"

# ── Handle copydb command ────────────────────────────────────────────
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
    echo "       $0 copydb"
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
    git pull origin "$BRANCH"
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

# ── Step 5: Restore local branch ────────────────────────────────────
if [ "$ORIGINAL_BRANCH" != "$BRANCH" ]; then
    echo ""
    echo ">> Restoring local branch to $ORIGINAL_BRANCH..."
    git checkout "$ORIGINAL_BRANCH"
fi

echo ""
echo "═══════════════════════════════════════════════════"
echo "  Deployed $BRANCH to $REMOTE_URL"
echo "═══════════════════════════════════════════════════"
