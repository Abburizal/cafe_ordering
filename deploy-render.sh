#!/bin/bash

################################################################################
#                                                                              #
#  🚀 AUTOMATED DEPLOY SCRIPT UNTUK RENDER.COM                               #
#  Cafe Ordering System - One-Click Deployment                               #
#                                                                              #
#  Usage: ./deploy-render.sh                                                 #
#                                                                              #
################################################################################

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Print header
print_header() {
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════════════════════════════╗"
    echo "║                                                                ║"
    echo "║        🚀 RENDER.COM AUTOMATED DEPLOYMENT SCRIPT 🚀           ║"
    echo "║                   Cafe Ordering System                         ║"
    echo "║                                                                ║"
    echo "╚════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

# Print step
print_step() {
    echo -e "${GREEN}✓${NC} $1"
}

# Print error
print_error() {
    echo -e "${RED}✗ ERROR:${NC} $1"
}

# Print warning
print_warning() {
    echo -e "${YELLOW}⚠ WARNING:${NC} $1"
}

# Print info
print_info() {
    echo -e "${BLUE}ℹ INFO:${NC} $1"
}

################################################################################
# STEP 1: Check Prerequisites
################################################################################

check_prerequisites() {
    print_header
    echo ""
    print_info "Checking prerequisites..."
    echo ""
    
    # Check git
    if ! command -v git &> /dev/null; then
        print_error "Git not installed. Please install Git first."
        exit 1
    fi
    print_step "Git is installed"
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_warning "Not in a git repository. Initializing..."
        git init
        git config user.email "admin@cafe-ordering.com"
        git config user.name "Cafe Ordering Admin"
    else
        print_step "Git repository found"
    fi
    
    # Check for GitHub remote
    if ! git remote get-url origin &> /dev/null; then
        print_warning "No GitHub remote found"
        echo ""
        echo "You need to:"
        echo "1. Create a repository on GitHub"
        echo "2. Run: git remote add origin https://github.com/YOUR_USERNAME/cafe-ordering.git"
        echo "3. Run this script again"
        exit 1
    fi
    
    print_step "GitHub remote configured"
    echo ""
}

################################################################################
# STEP 2: Prepare Files
################################################################################

prepare_files() {
    print_info "Preparing project files..."
    echo ""
    
    # Create .gitignore if not exists
    if [ ! -f .gitignore ]; then
        cat > .gitignore << 'EOF'
# Dependencies
vendor/
node_modules/
composer.lock

# Logs
*.log
logs/

# Database
*.sql
*.db

# Configuration (keep as template only)
config/config-local.php
config/secrets.php

# OS
.DS_Store
Thumbs.db
.vscode/
.idea/

# Uploads (optional)
public/uploads/*
!public/uploads/.gitkeep

# Temporary
tmp/
temp/
cache/*
!cache/.gitkeep

# Environment
.env.local
.env.*.local
EOF
        print_step "Created .gitignore"
    else
        print_step ".gitignore already exists"
    fi
    
    # Create .env.example if not exists
    if [ ! -f .env.example ]; then
        cat > .env.example << 'EOF'
# Database Configuration
DB_HOST=mysql-xxxxx.render.com
DB_USER=cafe_admin
DB_PASS=your_password_here
DB_NAME=cafe_ordering

# Application
APP_ENV=production
APP_DEBUG=false

# Session
SESSION_NAME=CAFE_ORDERING_SESSION
SESSION_LIFETIME=86400
EOF
        print_step "Created .env.example"
    else
        print_step ".env.example already exists"
    fi
    
    # Create render.yaml for deployment
    if [ ! -f render.yaml ]; then
        cat > render.yaml << 'EOF'
services:
  - type: web
    name: cafe-ordering
    env: php
    startCommand: php -S 0.0.0.0:${PORT:-8000}
    buildCommand: |
      if [ -f composer.json ]; then
        curl -sS https://getcomposer.org/installer | php
        php composer.phar install --no-dev
      fi
    routes:
      - path: /
        matchType: prefix
    envVars:
      - key: DB_HOST
        fromDatabase:
          name: cafe-ordering
          property: host
      - key: DB_USER
        fromDatabase:
          name: cafe-ordering
          property: user
      - key: DB_PASS
        fromDatabase:
          name: cafe-ordering
          property: password
      - key: DB_NAME
        fromDatabase:
          name: cafe-ordering
          property: database

databases:
  - name: cafe-ordering
    engine: mysql
    version: "8.0"
    plan: free
    databaseName: cafe_ordering
    user: cafe_admin
EOF
        print_step "Created render.yaml"
    else
        print_step "render.yaml already exists"
    fi
    
    # Update config.php to use environment variables
    if [ -f config/config.php ]; then
        print_warning "Backing up config/config.php"
        cp config/config.php config/config.php.backup
    fi
    
    cat > config/config.php << 'EOF'
<?php
/**
 * Database Configuration - Render.com Compatible
 */

// Get environment variables with fallbacks
$host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$db = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'cafe_ordering');
$user = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root');
$pass = getenv('DB_PASS') ?: (getenv('MYSQL_PASSWORD') ?: '');

// Error handling
if (!$host || !$db || !$user) {
    error_log("Database configuration incomplete. Check environment variables.");
    die("Database configuration error. Contact administrator.");
}

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Log successful connection (only in dev)
    if (getenv('APP_ENV') !== 'production') {
        error_log("Database connected: $host / $db");
    }
    
} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die("Database connection failed. Contact administrator.");
}
?>
EOF
    print_step "Updated config.php with environment variable support"
    
    # Create Composer.json if not exists
    if [ ! -f composer.json ]; then
        cat > composer.json << 'EOF'
{
    "name": "cafe-ordering/system",
    "description": "Cafe Ordering System with Real-time Updates",
    "type": "project",
    "require": {
        "php": ">=7.4.0"
    },
    "autoload": {
        "psr-4": {
            "CafeOrdering\\": "app/"
        }
    }
}
EOF
        print_step "Created composer.json"
    fi
    
    echo ""
}

################################################################################
# STEP 3: Git Operations
################################################################################

git_operations() {
    print_info "Preparing Git repository..."
    echo ""
    
    # Check git status
    if [ -z "$(git status --porcelain)" ]; then
        print_step "Working directory is clean"
    else
        print_warning "Uncommitted changes detected"
        echo ""
        git status
        echo ""
        
        read -p "Add and commit changes? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            git add -A
            git commit -m "Deploy preparation: Update config and deployment files"
            print_step "Changes committed"
        fi
    fi
    
    # Get current branch
    BRANCH=$(git rev-parse --abbrev-ref HEAD)
    print_step "Current branch: $BRANCH"
    
    # Get remote info
    REMOTE=$(git remote get-url origin)
    print_step "Remote URL: $REMOTE"
    
    echo ""
}

################################################################################
# STEP 4: Push to GitHub
################################################################################

push_to_github() {
    print_info "Pushing to GitHub..."
    echo ""
    
    read -p "Ready to push to GitHub? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Push cancelled"
        return
    fi
    
    git push -u origin $BRANCH
    if [ $? -eq 0 ]; then
        print_step "Successfully pushed to GitHub"
    else
        print_error "Failed to push to GitHub"
        exit 1
    fi
    
    echo ""
}

################################################################################
# STEP 5: Display Render Deployment Instructions
################################################################################

display_render_instructions() {
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}                    📋 RENDER.COM DEPLOYMENT GUIDE              ${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"
    echo ""
    
    cat << 'EOF'
🔑 STEP 1: Create Render Account
  1. Go to render.com
  2. Sign up (or login)
  3. Connect your GitHub account

🗄️  STEP 2: Create MySQL Database
  1. Dashboard → New → MySQL
  2. Name: cafe-ordering
  3. Choose Free plan
  4. Create
  5. SAVE the credentials shown!
  
  Example:
  - Host: mysql-xxxxx.render.com
  - User: cafe_admin
  - Password: RandomPassword123!
  - Database: cafe_ordering

📦 STEP 3: Create Web Service
  1. Dashboard → New → Web Service
  2. Select your cafe-ordering GitHub repo
  3. Name: cafe-ordering
  4. Environment: Docker (recommended)
  5. Build Command: (auto-detect)
  6. Start Command: (auto-detect from render.yaml)
  
🔐 STEP 4: Add Environment Variables (IMPORTANT!)
  In the Web Service Settings:
  
  - DB_HOST = [MySQL Host from Step 2]
  - DB_USER = [MySQL User from Step 2]
  - DB_PASS = [MySQL Password from Step 2]
  - DB_NAME = cafe_ordering
  
  Example:
  - DB_HOST=mysql-xxxxx.render.com
  - DB_USER=cafe_admin
  - DB_PASS=RandomPassword123!
  - DB_NAME=cafe_ordering

📊 STEP 5: Import Database
  Option A: Via MySQL Client
    mysql -h mysql-xxxxx.render.com \
          -u cafe_admin \
          -p'RandomPassword123!' \
          cafe_ordering < cafe_ordering.sql
  
  Option B: Via Render Dashboard
    - Go to MySQL Database in Render
    - Open in GUI
    - Import tab
    - Upload cafe_ordering.sql
    - Execute

✅ STEP 6: Deploy
  1. Click "Deploy" button
  2. Wait 5-10 minutes for build
  3. Check deployment logs
  4. Get your URL (https://cafe-ordering.onrender.com)

🔗 STEP 7: Test Website
  - Admin: https://cafe-ordering.onrender.com/admin/login.php
  - Menu: https://cafe-ordering.onrender.com/public/menu.php
  - Orders: https://cafe-ordering.onrender.com/public/order_status.php?order_id=1

📝 STEP 8: Setup Custom Domain (Optional)
  1. In Web Service Settings
  2. Add custom domain
  3. Update DNS at your domain registrar
  4. Wait for SSL certificate (automatic)

EOF
    
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"
    echo ""
}

################################################################################
# STEP 6: Create Quick Reference Card
################################################################################

create_quick_reference() {
    cat > RENDER_DEPLOYMENT_GUIDE.md << 'EOF'
# 🚀 Render Deployment Quick Guide

## Current Status
- ✅ GitHub repository prepared
- ✅ render.yaml created
- ✅ config.php updated for Render
- ✅ .env.example provided
- ⏳ Waiting for Render setup

## Database Credentials
Save these from Render MySQL:
```
HOST: ___________________
USER: ___________________
PASS: ___________________
DB: cafe_ordering
```

## Environment Variables to Add
```
DB_HOST = [from MySQL]
DB_USER = [from MySQL]
DB_PASS = [from MySQL]
DB_NAME = cafe_ordering
```

## Quick Checklist
- [ ] GitHub repo created
- [ ] Code pushed to GitHub
- [ ] Render account created
- [ ] MySQL database created
- [ ] Web Service created
- [ ] Environment variables set
- [ ] Database imported
- [ ] Website tested

## Useful Commands

### Push new changes
```bash
git add .
git commit -m "Your message"
git push origin main
```

### Monitor Render logs
```
https://dashboard.render.com → Web Service → Logs
```

### Update database after code changes
```bash
mysql -h HOST -u USER -p'PASS' cafe_ordering < cafe_ordering.sql
```

## Troubleshooting

### Website shows error
1. Check Render logs: Dashboard → Logs
2. Check database connection
3. Verify environment variables

### Database connection failed
1. Check credentials in environment variables
2. Verify MySQL database is running
3. Test with MySQL client:
   ```bash
   mysql -h HOST -u USER -p'PASS' -e "USE cafe_ordering; SELECT 1;"
   ```

### Real-time features not working
1. Check browser console for errors (F12)
2. Verify API endpoints work:
   - /admin/api/get_orders_realtime.php
   - /public/api/get_order_status_realtime.php

## Support Resources
- Render Docs: https://render.com/docs
- MySQL Docs: https://dev.mysql.com/doc/
- PHP Documentation: https://www.php.net/docs.php
EOF
    
    print_step "Created RENDER_DEPLOYMENT_GUIDE.md"
}

################################################################################
# STEP 7: Generate Render API Script
################################################################################

create_render_api_script() {
    cat > deploy-render-cli.sh << 'EOF'
#!/bin/bash

################################################################################
# RENDER.COM CLI DEPLOYMENT HELPER
# Requires: Render CLI (npm install -g @renderinc/cli)
################################################################################

set -e

echo "🚀 Render CLI Deployment Helper"
echo ""

# Check if Render CLI is installed
if ! command -v render &> /dev/null; then
    echo "❌ Render CLI not installed"
    echo ""
    echo "Install with: npm install -g @renderinc/cli"
    exit 1
fi

echo "ℹ️  Render CLI is installed"
echo ""

# Login to Render
echo "Logging in to Render..."
render login

# Create or update service
echo ""
echo "Would you like to:"
echo "1. Create new service"
echo "2. Update existing service"
read -p "Choose (1 or 2): " choice

if [ "$choice" = "1" ]; then
    render create --name cafe-ordering --repo https://github.com/YOUR_USERNAME/cafe-ordering
elif [ "$choice" = "2" ]; then
    render deploy cafe-ordering
else
    echo "Invalid choice"
    exit 1
fi

echo ""
echo "✅ Done! Check your Render dashboard for status."
EOF
    
    chmod +x deploy-render-cli.sh
    print_step "Created deploy-render-cli.sh (CLI helper)"
}

################################################################################
# MAIN EXECUTION
################################################################################

main() {
    check_prerequisites
    prepare_files
    git_operations
    push_to_github
    create_quick_reference
    create_render_api_script
    display_render_instructions
    
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}           ✅ DEPLOYMENT PREPARATION COMPLETE! ✅               ${NC}"
    echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
    echo ""
    
    echo "📋 Generated files:"
    echo "  - render.yaml (deployment config)"
    echo "  - config/config.php (updated for Render)"
    echo "  - .env.example (environment template)"
    echo "  - .gitignore (git ignore rules)"
    echo "  - RENDER_DEPLOYMENT_GUIDE.md (quick reference)"
    echo "  - deploy-render-cli.sh (CLI helper)"
    echo ""
    
    echo "📍 Next Steps:"
    echo "  1. Go to render.com"
    echo "  2. Create MySQL database"
    echo "  3. Create Web Service from GitHub"
    echo "  4. Add environment variables"
    echo "  5. Import database"
    echo "  6. Click Deploy"
    echo ""
    
    echo "📝 See RENDER_DEPLOYMENT_GUIDE.md for detailed instructions"
    echo ""
}

main "$@"
