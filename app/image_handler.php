<?php
/**
 * Image Handler - Utility untuk handle upload dan manipulasi gambar
 */

class ImageHandler {
    
    private $upload_path;
    private $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $max_file_size = 2097152; // 2MB
    private $thumbnail_width = 300;
    private $thumbnail_height = 300;
    
    public function __construct($upload_path = '../public/assets/images/products/') {
        $this->upload_path = rtrim($upload_path, '/') . '/';
        
        // Buat folder jika belum ada
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
        
        // Buat folder thumbnails
        if (!file_exists($this->upload_path . 'thumbnails/')) {
            mkdir($this->upload_path . 'thumbnails/', 0755, true);
        }
    }
    
    /**
     * Upload gambar dengan validasi dan resize
     */
    public function upload($file, $old_image = null) {
        // Validasi file
        $validation = $this->validate_image($file);
        if ($validation['error']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Generate nama file unik
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->generate_filename() . '.' . $extension;
        $filepath = $this->upload_path . $filename;
        
        // Upload file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Gagal upload file'];
        }
        
        // Resize image untuk optimasi
        $this->resize_image($filepath, 800, 800);
        
        // Buat thumbnail
        $this->create_thumbnail($filepath, $filename);
        
        // Hapus gambar lama jika ada
        if ($old_image && $old_image !== 'default.jpg') {
            $this->delete_image($old_image);
        }
        
        return ['success' => true, 'filename' => $filename];
    }
    
    /**
     * Validasi gambar
     */
    private function validate_image($file) {
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => true, 'message' => 'Error saat upload file'];
        }
        
        // Cek ukuran file
        if ($file['size'] > $this->max_file_size) {
            return ['error' => true, 'message' => 'Ukuran file maksimal 2MB'];
        }
        
        // Cek ekstensi file
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_extensions)) {
            return ['error' => true, 'message' => 'Format file tidak didukung. Gunakan: ' . implode(', ', $this->allowed_extensions)];
        }
        
        // Cek apakah benar-benar gambar
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            return ['error' => true, 'message' => 'File bukan gambar yang valid'];
        }
        
        return ['error' => false];
    }
    
    /**
     * Generate nama file unik
     */
    private function generate_filename() {
        return 'product_' . time() . '_' . uniqid();
    }
    
    /**
     * Resize gambar
     */
    private function resize_image($filepath, $max_width, $max_height) {
        list($orig_width, $orig_height, $image_type) = getimagesize($filepath);
        
        // Jika gambar sudah kecil, skip resize
        if ($orig_width <= $max_width && $orig_height <= $max_height) {
            return;
        }
        
        // Hitung ukuran baru (maintain aspect ratio)
        $ratio = min($max_width / $orig_width, $max_height / $orig_height);
        $new_width = round($orig_width * $ratio);
        $new_height = round($orig_height * $ratio);
        
        // Load gambar sesuai tipe
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }
        
        // Buat gambar baru
        $destination = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency untuk PNG dan GIF
        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Resize
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
        
        // Save sesuai tipe
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $filepath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $filepath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $filepath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($destination, $filepath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($destination);
    }
    
    /**
     * Buat thumbnail
     */
    private function create_thumbnail($filepath, $filename) {
        $thumb_path = $this->upload_path . 'thumbnails/' . $filename;
        
        list($orig_width, $orig_height, $image_type) = getimagesize($filepath);
        
        // Load gambar
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }
        
        // Buat thumbnail (crop to square)
        $size = min($orig_width, $orig_height);
        $x = ($orig_width - $size) / 2;
        $y = ($orig_height - $size) / 2;
        
        $thumb = imagecreatetruecolor($this->thumbnail_width, $this->thumbnail_height);
        
        // Preserve transparency
        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        
        imagecopyresampled($thumb, $source, 0, 0, $x, $y, $this->thumbnail_width, $this->thumbnail_height, $size, $size);
        
        // Save thumbnail
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $thumb_path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $thumb_path, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $thumb_path);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumb, $thumb_path, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
    }
    
    /**
     * Hapus gambar dan thumbnail
     */
    public function delete_image($filename) {
        if ($filename && $filename !== 'default.jpg') {
            $filepath = $this->upload_path . $filename;
            $thumb_path = $this->upload_path . 'thumbnails/' . $filename;
            
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }
        }
    }
    
    /**
     * Get URL gambar
     */
    public function get_image_url($filename, $thumbnail = false) {
        if (!$filename || $filename === 'default.jpg') {
            return BASE_URL . 'public/assets/images/default-product.jpg';
        }
        
        $path = $thumbnail ? 'thumbnails/' . $filename : $filename;
        return BASE_URL . 'public/assets/images/products/' . $path;
    }
}
