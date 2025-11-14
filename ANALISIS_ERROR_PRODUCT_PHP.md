# Analisis Error & Masalah pada admin/product.php

## 📋 Ringkasan Analisis
Tanggal: 14 November 2025  
File: `/admin/product.php`  
Status: **DITEMUKAN BEBERAPA MASALAH KRITIS**

---

## 🔴 MASALAH KRITIS YANG DITEMUKAN

### 1. **MISSING KOLOM `is_active` di Database Schema**
**Severity:** 🔴 **CRITICAL**

**Lokasi:** Lines 29, 47, 60, 99, 244, 256, 274, 279

**Masalah:**
- File `product.php` menggunakan kolom `is_active` untuk soft delete (arsip produk)
- Namun kolom `is_active` **TIDAK ADA** di tabel `products` dalam `schema.sql`
- Ini akan menyebabkan error SQL saat:
  - Menambah produk baru (line 29)
  - Mengarsip produk (line 47)
  - Mengaktifkan produk (line 60)
  - Query menampilkan produk (line 99)

**Error yang akan muncul:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'is_active' in 'field list'
```

**Solusi:**
Tambahkan kolom `is_active` ke tabel products:
```sql
ALTER TABLE products 
ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER image;
```

---

### 2. **Validasi Upload Gambar Tidak Optimal**
**Severity:** 🟡 **MEDIUM**

**Lokasi:** Lines 26, 85

**Masalah:**
- Menggunakan `@move_uploaded_file()` dengan error suppression (@)
- Tidak ada validasi tipe file (MIME type)
- Tidak ada validasi ukuran file
- Tidak ada handling jika upload gagal
- Potensi security risk (file upload vulnerability)

**Solusi:**
Gunakan fungsi `validate_image_upload()` yang sudah ada di `helpers.php`:
```php
// Ganti baris 20-26 dengan:
if ($name !== '' && $price !== '') {
    // Validasi gambar
    $validation = validate_image_upload($_FILES['image']);
    if (!empty($validation)) {
        $message = "⚠️ " . implode(", ", $validation);
    } else {
        $targetDir = "../public/assets/images/";
        $uniqueImageName = generate_unique_filename($_FILES['image']['name']);
        $targetFile = $targetDir . $uniqueImageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Insert query...
        } else {
            $message = "⚠️ Gagal mengupload gambar!";
        }
    }
}
```

---

### 3. **CSRF Protection Tidak Diimplementasi**
**Severity:** 🟡 **MEDIUM**

**Lokasi:** Semua form (lines 188, 303)

**Masalah:**
- Form tidak memiliki CSRF token
- Vulnerable terhadap CSRF attack
- Functions CSRF sudah tersedia di `helpers.php` tapi tidak digunakan

**Solusi:**
Tambahkan CSRF token di semua form:
```php
// Di dalam <head>
<?= csrf_meta() ?>

// Di setiap form, setelah <form>
<?= csrf_field() ?>

// Di handler POST, sebelum proses data
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $message = get_error_message('invalid_csrf');
    // Stop processing
}
```

---

### 4. **Error Handling Tidak Konsisten**
**Severity:** 🟡 **MEDIUM**

**Lokasi:** Lines 12-95

**Masalah:**
- Tidak ada try-catch untuk query database
- Jika query gagal, tidak ada error message yang jelas
- Tidak menggunakan fungsi `log_error()` yang sudah tersedia

**Solusi:**
Wrap semua database operations dengan try-catch:
```php
try {
    $stmt = $pdo->prepare("...");
    $stmt->execute([...]);
    $message = "✅ Berhasil!";
} catch (PDOException $e) {
    log_error('Failed to add product: ' . $e->getMessage(), [
        'user_id' => $_SESSION['user_id'] ?? null,
        'product_name' => $name
    ]);
    $message = get_error_message('db_error');
}
```

---

### 5. **Path Traversal Vulnerability**
**Severity:** 🔴 **HIGH**

**Lokasi:** Lines 22, 79

**Masalah:**
- `basename()` saja tidak cukup untuk mencegah path traversal
- Nama file tidak di-sanitize dengan benar
- Bisa dieksploitasi untuk menulis file di lokasi yang tidak diinginkan

**Solusi:**
Gunakan fungsi `sanitize_filename()` dan `generate_unique_filename()`:
```php
$uniqueImageName = generate_unique_filename($_FILES['image']['name']);
```

---

### 6. **Tidak Ada Pengecekan Direktori Upload**
**Severity:** 🟡 **MEDIUM**

**Lokasi:** Lines 22, 79

**Masalah:**
- Tidak ada pengecekan apakah direktori exists
- Tidak ada pengecekan permission write
- Upload akan gagal silent jika direktori tidak ada

**Solusi:**
```php
$targetDir = "../public/assets/images/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}
if (!is_writable($targetDir)) {
    $message = "⚠️ Direktori upload tidak bisa ditulis!";
    // stop processing
}
```

---

### 7. **SQL Injection di Parameter GET**
**Severity:** 🔴 **HIGH** (Partially Fixed)

**Lokasi:** Lines 38-39, 58-59

**Status:** ✅ Sudah menggunakan prepared statement (AMAN)

**Note:** Sudah aman karena menggunakan `(int)` casting dan prepared statements.

---

### 8. **Default Value Handling Tidak Konsisten**
**Severity:** 🟢 **LOW**

**Lokasi:** Lines 17-18, 70-71

**Masalah:**
- Line 17: `$stock = (int)($_POST['stock'] ?? 100);` ✅ Correct
- Line 70: `$stock = (int)($_POST['stock'] ?? 0);` ⚠️ Different default

**Rekomendasi:**
Gunakan default value yang konsisten (100).

---

### 9. **Missing Image Cleanup saat Edit**
**Severity:** 🟢 **LOW**

**Lokasi:** Lines 77-86

**Masalah:**
- Saat upload gambar baru, gambar lama tidak dihapus
- Akan menumpuk file sampah di server

**Solusi:**
```php
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Hapus gambar lama
    $oldImagePath = $targetDir . $currentImage;
    if (file_exists($oldImagePath) && $currentImage !== '') {
        @unlink($oldImagePath);
    }
    
    // Upload gambar baru
    // ...
}
```

---

### 10. **Textarea tidak di-sanitize**
**Severity:** 🟢 **LOW**

**Lokasi:** Lines 18, 71

**Masalah:**
- `trim()` saja tidak cukup
- Bisa menyimpan HTML/script berbahaya
- Output di frontend menggunakan `e()` jadi aman, tapi tetap better practice

**Rekomendasi:**
```php
$description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
```

---

## 🔧 PRIORITAS PERBAIKAN

### ✅ Must Fix (Segera):
1. **Tambahkan kolom `is_active` ke database** - Aplikasi akan error total
2. **Implementasi validasi upload gambar** - Security risk
3. **Implementasi CSRF protection** - Security risk
4. **Fix path traversal vulnerability** - Security risk

### ⚠️ Should Fix (Prioritas Tinggi):
5. Error handling dengan try-catch
6. Pengecekan direktori upload
7. Cleanup gambar lama saat edit

### 📝 Nice to Have:
8. Konsistensi default value
9. Sanitasi textarea lebih baik
10. Logging untuk debugging

---

## 🛠️ SCRIPT SQL PERBAIKAN

```sql
-- 1. Tambahkan kolom is_active
ALTER TABLE products 
ADD COLUMN is_active TINYINT(1) DEFAULT 1 
COMMENT 'Status aktif produk (1=aktif, 0=arsip)' 
AFTER image;

-- 2. Update produk yang sudah ada menjadi aktif
UPDATE products SET is_active = 1 WHERE is_active IS NULL;

-- 3. Tambahkan index untuk performa
CREATE INDEX idx_products_active ON products(is_active);
```

---

## ✅ CHECKLIST PERBAIKAN

- [ ] Jalankan script SQL untuk tambah kolom `is_active`
- [ ] Implementasi `validate_image_upload()` di form tambah & edit
- [ ] Implementasi CSRF protection di semua form
- [ ] Wrap database operations dengan try-catch
- [ ] Gunakan `generate_unique_filename()` untuk sanitasi nama file
- [ ] Tambahkan pengecekan direktori upload exists & writable
- [ ] Implementasi cleanup gambar lama saat edit
- [ ] Konsistensi default value stock
- [ ] Test semua fitur:
  - [ ] Tambah produk baru
  - [ ] Edit produk
  - [ ] Arsip produk
  - [ ] Aktifkan produk
  - [ ] Upload gambar
  - [ ] Validasi error handling

---

## 📊 KESIMPULAN

**Status Keseluruhan:** 🔴 **MEMERLUKAN PERBAIKAN SEGERA**

File `product.php` memiliki beberapa masalah kritis yang perlu diperbaiki:
- **Database schema tidak sinkron** dengan kode
- **Security vulnerabilities** yang perlu segera ditangani
- **Error handling** yang kurang memadai

Namun, **struktur kode sudah cukup baik** dan menggunakan prepared statements untuk mencegah SQL injection di sebagian besar tempat.

Dengan perbaikan yang telah disebutkan di atas, sistem akan menjadi lebih **aman, robust, dan maintainable**.

---

**Generated by:** GitHub Copilot CLI  
**Date:** 14 November 2025
