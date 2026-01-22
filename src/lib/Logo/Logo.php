<?php
require_once 'config/config.php';

class Logo
{
    private const TABLE = 'logos';
    private const REQUIRED_WIDTH = 600;
    private const REQUIRED_HEIGHT = 193;
    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    
    public function __construct()
    {
    }

    public function __destruct()
    {
    }
    
    /**
     * Set friendly columns' names to order tables' entries
     */
    public function setOrderingValues(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'filename' => 'Image',
            'state' => 'State',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        ];
    }

    /**
     * Get all active logos for select dropdowns
     */
    public function getAllLogos(): array
    {
        $db = getDbInstance();
        $db->where('state', 'enable');
        $db->orderBy('name', 'ASC');
        return $db->get(self::TABLE) ?? [];
    }

    /**
     * Get a single logo by ID
     */
    public function getLogo(int $id): ?array
    {
        $db = getDbInstance();
        $db->where('id', $id);
        $result = $db->getOne(self::TABLE);

        if ($result !== null) {
            return $result;
        }
        
        $this->failure("Logo not found");
        return null;
    }

    /**
     * Validate image dimensions
     */
    public function validateImageDimensions(string $tmpPath): array
    {
        $imageInfo = getimagesize($tmpPath);
        
        if ($imageInfo === false) {
            return ['valid' => false, 'message' => 'Invalid image file'];
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width !== self::REQUIRED_WIDTH || $height !== self::REQUIRED_HEIGHT) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Image dimensions must be %dx%d pixels. Uploaded image is %dx%d pixels.',
                    self::REQUIRED_WIDTH,
                    self::REQUIRED_HEIGHT,
                    $width,
                    $height
                )
            ];
        }

        return ['valid' => true, 'width' => $width, 'height' => $height];
    }

    /**
     * Validate file extension
     */
    public function validateExtension(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, self::ALLOWED_EXTENSIONS);
    }

    /**
     * Generate unique filename with timestamp for cache busting
     */
    private function generateFilename(string $originalName): string
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $timestamp = time();
        
        return "{$safeName}_{$timestamp}.{$ext}";
    }

    /**
     * Get logo file path
     */
    public function getLogoPath(string $filename): string
    {
        return LOGOS_DIRECTORY . $filename;
    }

    /**
     * Get logo URL with cache busting
     */
    public function getLogoUrl(string $filename): string
    {
        $filePath = $this->getLogoPath($filename);
        $timestamp = file_exists($filePath) ? filemtime($filePath) : time();
        return LOGOS_PATH . $filename . '?v=' . $timestamp;
    }

    /**
     * Add new logo
     */
    public function addLogo(array $inputData, array $fileData): void
    {
        if (!$this->validateExtension($fileData['name'])) {
            $this->failure('Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        $validation = $this->validateImageDimensions($fileData['tmp_name']);
        if (!$validation['valid']) {
            $this->failure($validation['message']);
        }

        $filename = $this->generateFilename($fileData['name']);
        $destination = $this->getLogoPath($filename);

        if (!move_uploaded_file($fileData['tmp_name'], $destination)) {
            $this->failure('Failed to upload image');
        }

        $db = getDbInstance();
        
        $dataToDb = [
            'name' => htmlspecialchars($inputData['name'], ENT_QUOTES, 'UTF-8'),
            'filename' => $filename,
            'original_filename' => $fileData['name'],
            'width' => $validation['width'],
            'height' => $validation['height'],
            'state' => $inputData['state'] ?? 'enable',
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $lastId = $db->insert(self::TABLE, $dataToDb);

        if ($lastId) {
            $this->success('Logo added successfully');
        } else {
            unlink($destination);
            $this->failure('Failed to save logo: ' . $db->getLastError());
        }
    }

    /**
     * Edit existing logo
     */
    public function editLogo(array $inputData, ?array $fileData = null): void
    {
        $db = getDbInstance();
        $oldLogo = $this->getLogo((int)$inputData['id']);

        $dataToDb = [
            'name' => htmlspecialchars($inputData['name'], ENT_QUOTES, 'UTF-8'),
            'state' => $inputData['state'] ?? 'enable',
            'updated_by' => $_SESSION['user_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($fileData !== null && !empty($fileData['tmp_name'])) {
            if (!$this->validateExtension($fileData['name'])) {
                $this->failure('Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS));
            }

            $validation = $this->validateImageDimensions($fileData['tmp_name']);
            if (!$validation['valid']) {
                $this->failure($validation['message']);
            }

            $filename = $this->generateFilename($fileData['name']);
            $destination = $this->getLogoPath($filename);

            if (!move_uploaded_file($fileData['tmp_name'], $destination)) {
                $this->failure('Failed to upload image');
            }

            $oldFilePath = $this->getLogoPath($oldLogo['filename']);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            $dataToDb['filename'] = $filename;
            $dataToDb['original_filename'] = $fileData['name'];
            $dataToDb['width'] = $validation['width'];
            $dataToDb['height'] = $validation['height'];
        }

        $db->where('id', $inputData['id']);
        $stat = $db->update(self::TABLE, $dataToDb);

        if ($stat) {
            $this->success('Logo updated successfully');
        } else {
            $this->failure('Failed to update logo: ' . $db->getLastError());
        }
    }

    /**
     * Delete logo
     */
    public function deleteLogo(int $id): void
    {
        if ($_SESSION['type'] !== 'super') {
            $this->failure('Only super admin can delete logos');
        }

        $db = getDbInstance();
        $logo = $this->getLogo($id);

        $filePath = $this->getLogoPath($logo['filename']);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $db->where('id', $id);
        $stat = $db->delete(self::TABLE);

        if ($stat) {
            $this->info('Logo deleted successfully');
        } else {
            $this->failure('Unable to delete logo');
        }
    }

    /**
     * Flash message Failure process
     */
    public function failure(string $message, string $location = 'Location: logos_crud.php'): void
    {
        $_SESSION['failure'] = $message;
        header($location);
        exit();
    }

    /**
     * Flash message Success process
     */
    public function success(string $message): void
    {
        $_SESSION['success'] = $message;
        header('Location: logos_crud.php');
        exit();
    }

    /**
     * Flash message Info process
     */
    public function info(string $message): void
    {
        $_SESSION['info'] = $message;
        header('Location: logos_crud.php');
        exit();
    }

    /**
     * Get required dimensions
     */
    public static function getRequiredDimensions(): array
    {
        return [
            'width' => self::REQUIRED_WIDTH,
            'height' => self::REQUIRED_HEIGHT
        ];
    }
}
?>
