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
