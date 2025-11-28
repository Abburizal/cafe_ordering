#!/usr/bin/env python3

"""
╔════════════════════════════════════════════════════════════════════════════════╗
║                                                                                ║
║  🚀 AUTOMATED DEPLOY SCRIPT UNTUK RENDER.COM - PYTHON VERSION                ║
║  Cafe Ordering System - One-Click Deployment                                  ║
║                                                                                ║
║  Usage: python3 deploy-render.py                                              ║
║                                                                                ║
╚════════════════════════════════════════════════════════════════════════════════╝
"""

import os
import sys
import subprocess
import json
from pathlib import Path
from datetime import datetime

# Colors
class Colors:
    HEADER = '\033[95m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'

def print_header():
    """Print script header"""
    print(f"{Colors.BLUE}")
    print("╔════════════════════════════════════════════════════════════════╗")
    print("║                                                                ║")
    print("║        🚀 RENDER.COM AUTOMATED DEPLOYMENT SCRIPT 🚀           ║")
    print("║                   Cafe Ordering System                         ║")
    print("║                                                                ║")
    print("╚════════════════════════════════════════════════════════════════╝")
    print(f"{Colors.ENDC}\n")

def print_step(message):
    """Print success step"""
    print(f"{Colors.GREEN}✓{Colors.ENDC} {message}")

def print_error(message):
    """Print error message"""
    print(f"{Colors.RED}✗ ERROR:{Colors.ENDC} {message}")
    sys.exit(1)

def print_warning(message):
    """Print warning message"""
    print(f"{Colors.YELLOW}⚠ WARNING:{Colors.ENDC} {message}")

def print_info(message):
    """Print info message"""
    print(f"{Colors.BLUE}ℹ INFO:{Colors.ENDC} {message}")

def run_command(command, check=True):
    """Run shell command"""
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True, check=check)
        return result.returncode == 0, result.stdout, result.stderr
    except subprocess.CalledProcessError as e:
        return False, e.stdout, e.stderr

def check_git_installed():
    """Check if Git is installed"""
    success, _, _ = run_command("git --version")
    if not success:
        print_error("Git is not installed. Please install Git first.")
    print_step("Git is installed")

def check_git_repository():
    """Check if we're in a git repository"""
    success, _, _ = run_command("git rev-parse --git-dir", check=False)
    if not success:
        print_warning("Not in a git repository. Initializing...")
        run_command("git init")
        run_command('git config user.email "admin@cafe-ordering.com"')
        run_command('git config user.name "Cafe Ordering Admin"')
    print_step("Git repository ready")

def check_github_remote():
    """Check if GitHub remote is configured"""
    success, _, _ = run_command("git remote get-url origin", check=False)
    if not success:
        print_warning("No GitHub remote found")
        print("\nYou need to:")
        print("1. Create a repository on GitHub")
        print("2. Run: git remote add origin https://github.com/YOUR_USERNAME/cafe-ordering.git")
        print("3. Run this script again")
        sys.exit(1)
    print_step("GitHub remote configured")

def check_prerequisites():
    """Check all prerequisites"""
    print_info("Checking prerequisites...")
    print()
    
    check_git_installed()
    check_git_repository()
    check_github_remote()
    
    print()

def create_gitignore():
    """Create .gitignore file"""
    gitignore_content = """# Dependencies
vendor/
node_modules/
composer.lock

# Logs
*.log
logs/

# Database
*.sql
*.db

# Configuration
config/config-local.php
config/secrets.php

# OS
.DS_Store
Thumbs.db
.vscode/
.idea/

# Uploads
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
"""
    
    if not Path(".gitignore").exists():
        with open(".gitignore", "w") as f:
            f.write(gitignore_content)
        print_step("Created .gitignore")
    else:
        print_step(".gitignore already exists")

def create_env_example():
    """Create .env.example file"""
    env_content = """# Database Configuration
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
"""
    
    if not Path(".env.example").exists():
        with open(".env.example", "w") as f:
            f.write(env_content)
        print_step("Created .env.example")
    else:
        print_step(".env.example already exists")

def create_render_yaml():
    """Create render.yaml for deployment"""
    render_yaml_content = """services:
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
"""
    
    if not Path("render.yaml").exists():
        with open("render.yaml", "w") as f:
            f.write(render_yaml_content)
        print_step("Created render.yaml")
    else:
        print_step("render.yaml already exists")

def create_config_php():
    """Create updated config.php with environment variable support"""
    config_content = """<?php
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
?>"""
    
    config_path = Path("config/config.php")
    if config_path.exists():
        print_warning("Backing up config/config.php to config/config.php.backup")
        os.rename(str(config_path), str(config_path) + ".backup")
    
    with open(str(config_path), "w") as f:
        f.write(config_content)
    print_step("Updated config/config.php with environment variable support")

def create_composer_json():
    """Create composer.json if not exists"""
    composer_content = {
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
    
    if not Path("composer.json").exists():
        with open("composer.json", "w") as f:
            json.dump(composer_content, f, indent=2)
        print_step("Created composer.json")

def prepare_files():
    """Prepare all necessary files"""
    print_info("Preparing project files...")
    print()
    
    create_gitignore()
    create_env_example()
    create_render_yaml()
    create_config_php()
    create_composer_json()
    
    print()

def git_operations():
    """Git operations"""
    print_info("Preparing Git repository...")
    print()
    
    # Check git status
    success, stdout, _ = run_command("git status --porcelain", check=False)
    
    if not stdout:
        print_step("Working directory is clean")
    else:
        print_warning("Uncommitted changes detected")
        print()
        run_command("git status")
        print()
        
        response = input("Add and commit changes? (y/n): ")
        if response.lower() == 'y':
            run_command("git add -A")
            run_command('git commit -m "Deploy preparation: Update config and deployment files"')
            print_step("Changes committed")
    
    # Get current branch
    _, branch, _ = run_command("git rev-parse --abbrev-ref HEAD")
    branch = branch.strip()
    print_step(f"Current branch: {branch}")
    
    # Get remote
    _, remote, _ = run_command("git remote get-url origin")
    remote = remote.strip()
    print_step(f"Remote URL: {remote}")
    
    print()
    return branch

def push_to_github(branch):
    """Push to GitHub"""
    print_info("Pushing to GitHub...")
    print()
    
    response = input("Ready to push to GitHub? (y/n): ")
    if response.lower() != 'y':
        print_warning("Push cancelled")
        return
    
    success, _, _ = run_command(f"git push -u origin {branch}")
    if success:
        print_step("Successfully pushed to GitHub")
    else:
        print_error("Failed to push to GitHub")
    
    print()

def create_quick_reference():
    """Create RENDER_DEPLOYMENT_GUIDE.md"""
    guide_content = """# 🚀 Render Deployment Quick Guide

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
Visit: https://dashboard.render.com → Web Service → Logs

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
3. Test with MySQL client

### Real-time features not working
1. Check browser console for errors (F12)
2. Verify API endpoints work

## Support
- Render Docs: https://render.com/docs
- MySQL Docs: https://dev.mysql.com/doc/
- PHP Documentation: https://www.php.net/docs.php
"""
    
    with open("RENDER_DEPLOYMENT_GUIDE.md", "w") as f:
        f.write(guide_content)
    print_step("Created RENDER_DEPLOYMENT_GUIDE.md")

def display_instructions():
    """Display deployment instructions"""
    print()
    print(f"{Colors.BLUE}════════════════════════════════════════════════════════════════{Colors.ENDC}")
    print(f"{Colors.BLUE}                    📋 RENDER.COM DEPLOYMENT GUIDE              {Colors.ENDC}")
    print(f"{Colors.BLUE}════════════════════════════════════════════════════════════════{Colors.ENDC}")
    print()
    
    instructions = """
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

📦 STEP 3: Create Web Service
  1. Dashboard → New → Web Service
  2. Select your cafe-ordering GitHub repo
  3. Name: cafe-ordering
  4. Environment: Docker (recommended)
  5. Build Command: auto-detect
  6. Start Command: auto-detect

🔐 STEP 4: Add Environment Variables
  DB_HOST = [MySQL Host]
  DB_USER = [MySQL User]
  DB_PASS = [MySQL Password]
  DB_NAME = cafe_ordering

📊 STEP 5: Import Database
  Option A: Via MySQL Client
    mysql -h HOST -u USER -p'PASS' cafe_ordering < cafe_ordering.sql
  
  Option B: Via Render Dashboard
    - Go to MySQL Database
    - Open in GUI
    - Import tab
    - Upload cafe_ordering.sql
    - Execute

✅ STEP 6: Deploy
  1. Click "Deploy" button
  2. Wait 5-10 minutes
  3. Check logs
  4. Get your URL

🔗 STEP 7: Test Website
  - Admin: https://cafe-ordering.onrender.com/admin/login.php
  - Menu: https://cafe-ordering.onrender.com/public/menu.php
"""
    
    print(instructions)
    print(f"{Colors.BLUE}════════════════════════════════════════════════════════════════{Colors.ENDC}")
    print()

def main():
    """Main execution"""
    print_header()
    
    check_prerequisites()
    prepare_files()
    branch = git_operations()
    push_to_github(branch)
    create_quick_reference()
    display_instructions()
    
    print(f"{Colors.GREEN}════════════════════════════════════════════════════════════════{Colors.ENDC}")
    print(f"{Colors.GREEN}           ✅ DEPLOYMENT PREPARATION COMPLETE! ✅               {Colors.ENDC}")
    print(f"{Colors.GREEN}════════════════════════════════════════════════════════════════{Colors.ENDC}")
    print()
    
    print("📋 Generated files:")
    print("  - render.yaml (deployment config)")
    print("  - config/config.php (updated for Render)")
    print("  - .env.example (environment template)")
    print("  - .gitignore (git ignore rules)")
    print("  - RENDER_DEPLOYMENT_GUIDE.md (quick reference)")
    print("  - composer.json (PHP dependencies)")
    print()
    
    print("📍 Next Steps:")
    print("  1. Go to render.com")
    print("  2. Create MySQL database")
    print("  3. Create Web Service from GitHub")
    print("  4. Add environment variables")
    print("  5. Import database")
    print("  6. Click Deploy")
    print()
    
    print(f"{Colors.CYAN}📝 See RENDER_DEPLOYMENT_GUIDE.md for detailed instructions{Colors.ENDC}")
    print()

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n⚠️  Script interrupted by user")
        sys.exit(0)
    except Exception as e:
        print_error(f"Unexpected error: {str(e)}")
