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
