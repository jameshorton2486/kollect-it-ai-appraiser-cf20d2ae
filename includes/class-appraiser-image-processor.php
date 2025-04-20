
<?php
class Appraiser_Image_Processor {
    const MAX_IMAGE_SIZE = 20971520; // 20MB
    const MAX_WIDTH = 2048;
    const MAX_HEIGHT = 2048;
    
    public function clean_image_data($image_data) {
        // Remove data URL prefix if present
        if (strpos($image_data, 'data:image/jpeg;base64,') === 0) {
            return substr($image_data, strlen('data:image/jpeg;base64,'));
        } elseif (strpos($image_data, 'data:image/png;base64,') === 0) {
            return substr($image_data, strlen('data:image/png;base64,'));
        } elseif (strpos($image_data, 'data:image/') === 0) {
            $start = strpos($image_data, 'base64,');
            if ($start !== false) {
                return substr($image_data, $start + 7);
            }
        }
        
        return $image_data;
    }
    
    public function save_image_attachment($post_id, $image_data) {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];
        
        // Process image data
        $image_data = $this->clean_image_data($image_data);
        $image_data_decoded = base64_decode($image_data);
        
        if (!$image_data_decoded) {
            return new WP_Error('invalid_image', 'Invalid image data');
        }
        
        // Check file size
        if (strlen($image_data_decoded) > self::MAX_IMAGE_SIZE) {
            return new WP_Error('file_too_large', 'Image file size exceeds maximum limit');
        }
        
        // Create temporary file to process image
        $temp_file = wp_tempnam();
        file_put_contents($temp_file, $image_data_decoded);
        
        // Process and optimize the image
        $editor = wp_get_image_editor($temp_file);
        if (is_wp_error($editor)) {
            unlink($temp_file);
            return $editor;
        }
        
        // Resize if needed
        $size = $editor->get_size();
        if ($size['width'] > self::MAX_WIDTH || $size['height'] > self::MAX_HEIGHT) {
            $editor->resize(self::MAX_WIDTH, self::MAX_HEIGHT, false);
        }
        
        // Set quality
        $editor->set_quality(90);
        
        // Create unique filename
        $filename = 'appraisal-' . $post_id . '-' . time() . '.jpg';
        $file_path = $upload_path . '/' . $filename;
        
        // Save optimized image
        $result = $editor->save($file_path, 'image/jpeg');
        unlink($temp_file);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Prepare and insert attachment
        $attachment = array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $upload_url . '/' . $filename
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        set_post_thumbnail($post_id, $attachment_id);
        
        return $attachment_id;
    }
}
