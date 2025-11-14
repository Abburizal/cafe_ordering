<?php
/**
 * Validator - Fungsi untuk validasi dan sanitasi input
 */

class Validator {
    
    /**
     * Sanitasi string untuk mencegah XSS
     */
    public static function sanitize_string($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validasi email
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Validasi password (minimal 6 karakter)
     */
    public static function validate_password($password, $min_length = 6) {
        return strlen($password) >= $min_length;
    }
    
    /**
     * Validasi angka positif
     */
    public static function validate_positive_number($number) {
        return is_numeric($number) && $number > 0;
    }
    
    /**
     * Validasi format nomor meja (contoh: A1, B2)
     */
    public static function validate_table_number($table_number) {
        return preg_match('/^[A-Z]\d+$/', $table_number);
    }
    
    /**
     * Validasi format telepon (Indonesia)
     */
    public static function validate_phone($phone) {
        // Format: 08xxxxxxxxxx atau +628xxxxxxxxxx (min 10 digit)
        return preg_match('/^(\+62|62|0)[0-9]{9,13}$/', $phone);
    }
    
    /**
     * Validasi required field
     */
    public static function validate_required($value) {
        return !empty(trim($value));
    }
    
    /**
     * Validasi panjang string
     */
    public static function validate_length($string, $min, $max) {
        $length = strlen($string);
        return $length >= $min && $length <= $max;
    }
    
    /**
     * Validasi tipe file upload (gambar)
     */
    public static function validate_image_type($file) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($file['type'], $allowed_types);
    }
    
    /**
     * Validasi ukuran file (dalam bytes)
     */
    public static function validate_file_size($file, $max_size = 2097152) { // default 2MB
        return $file['size'] <= $max_size;
    }
    
    /**
     * Validasi status order
     */
    public static function validate_order_status($status) {
        $allowed_statuses = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];
        return in_array($status, $allowed_statuses);
    }
    
    /**
     * Validasi payment method
     */
    public static function validate_payment_method($method) {
        $allowed_methods = ['cash', 'qris', 'transfer'];
        return in_array($method, $allowed_methods);
    }
    
    /**
     * Sanitasi dan validasi input multiple
     */
    public static function validate_inputs($rules, $data) {
        $errors = [];
        
        foreach ($rules as $field => $rule_set) {
            $value = isset($data[$field]) ? $data[$field] : '';
            
            foreach ($rule_set as $rule) {
                switch ($rule) {
                    case 'required':
                        if (!self::validate_required($value)) {
                            $errors[$field] = ucfirst($field) . " wajib diisi.";
                        }
                        break;
                    case 'email':
                        if (!empty($value) && !self::validate_email($value)) {
                            $errors[$field] = "Format email tidak valid.";
                        }
                        break;
                    case 'phone':
                        if (!empty($value) && !self::validate_phone($value)) {
                            $errors[$field] = "Format nomor telepon tidak valid.";
                        }
                        break;
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field] = ucfirst($field) . " harus berupa angka.";
                        }
                        break;
                    case 'positive':
                        if (!empty($value) && !self::validate_positive_number($value)) {
                            $errors[$field] = ucfirst($field) . " harus berupa angka positif.";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Generate CSRF Token
     */
    public static function generate_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validasi CSRF Token
     */
    public static function validate_csrf_token($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
