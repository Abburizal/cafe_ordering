# ⚡ QUICK START - Deploy ke Render Gratis (5 Menit)

## 🎯 TL;DR (Too Long; Didn't Read)

### Untuk Linux/Mac:
```bash
chmod +x deploy-render.py
python3 deploy-render.py
```

### Untuk Windows:
```bash
python deploy-render.py
```

### Kemudian ikuti step-by-step di Render dashboard

---

## 📋 3 Langkah Utama

### LANGKAH 1: Run Script Deployment (2 menit)

```bash
# Navigate ke project folder
cd /path/to/cafe_ordering

# Run script (pilih salah satu)
python3 deploy-render.py    # Recommended
# atau
./deploy-render.sh          # Alternative
```

**Apa yang script lakukan:**
- ✅ Check Git prerequisites
- ✅ Create render.yaml
- ✅ Update config.php
- ✅ Push code ke GitHub
- ✅ Display setup instructions

### LANGKAH 2: Setup Render Resources (2 menit)

**A. Create Database:**
```
1. render.com → Dashboard
2. New → MySQL
3. Name: cafe-ordering
4. Create
5. SAVE credentials
```

**B. Create Web Service:**
```
1. New → Web Service
2. Select cafe-ordering repository
3. Deploy
```

**C. Add Environment Variables:**
```
DB_HOST = mysql-xxxxx.render.com
DB_USER = cafe_admin
DB_PASS = [from MySQL]
DB_NAME = cafe_ordering
```

**D. Import Database:**
```bash
mysql -h HOST -u USER -p'PASS' cafe_ordering < cafe_ordering.sql
```

### LANGKAH 3: Test Website (1 menit)

```
✅ Admin: https://cafe-ordering.onrender.com/admin/login.php
✅ Menu: https://cafe-ordering.onrender.com/public/menu.php
✅ Status: https://cafe-ordering.onrender.com/public/order_status.php?order_id=1
```

---

## 🔧 Apa yang Script Buat?

Setelah run script, project Anda akan punya:

```
cafe_ordering/
├── deploy-render.py          ← Script deployment
├── deploy-render.sh          ← Alternative script
├── render.yaml              ← Render config (PENTING!)
├── config/config.php        ← Updated with env vars
├── composer.json            ← PHP dependencies
├── .gitignore               ← Git ignore rules
├── .env.example             ← Environment template
└── RENDER_DEPLOY_README.md  ← Full documentation
```

---

## 🚀 Command Reference

### Run Python Script
```bash
python3 deploy-render.py
```

### Run Bash Script
```bash
chmod +x deploy-render.sh
./deploy-render.sh
```

### Manual Git Push (Jika script gagal)
```bash
git add .
git commit -m "Deploy prep"
git push origin main
```

### Import Database
```bash
mysql -h mysql-xxxxx.render.com -u cafe_admin -p'PASSWORD' cafe_ordering < cafe_ordering.sql
```

---

## ❓ FAQ

### Q: Script tidak bisa jalan?
**A:** Gunakan Python alternative:
```bash
python3 deploy-render.py
```

### Q: Python tidak install?
**A:** Install dari https://www.python.org/downloads/

### Q: Bagaimana setup database?
**A:** Lihat LANGKAH 2.D atau RENDER_DEPLOY_README.md

### Q: Website error setelah deploy?
**A:** 
1. Check Render logs
2. Verify environment variables
3. Ensure database imported

### Q: Mau buat custom domain?
**A:** Lihat bagian "Custom Domain" di RENDER_DEPLOY_README.md

---

## 📞 Butuh Help?

1. **Baca:** RENDER_DEPLOY_README.md (full guide)
2. **Check:** Render logs di dashboard
3. **Search:** Render docs https://render.com/docs

---

## ✅ Success Indicators

Website Anda berhasil jika:

- ✅ Bisa akses admin login
- ✅ Bisa lihat menu produk
- ✅ Database terkoneksi
- ✅ Real-time features work

---

## 🎉 SELESAI!

```
Your website is now LIVE on Render! 🚀
```

---

**Created**: 28 November 2025
**Version**: 1.0
**Status**: Ready to Deploy ✅
