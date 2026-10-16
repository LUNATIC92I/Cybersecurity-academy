<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UploadService;
use Tests\TestCase;

final class UploadTest extends TestCase
{
    private function fakeUpload(string $name, string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($tmp, $content);
        return ['name' => $name, 'tmp_name' => $tmp, 'size' => strlen($content), 'error' => UPLOAD_ERR_OK, 'type' => 'application/octet-stream'];
    }

    public function test_allowed_file_is_stored_outside_web_root(): void
    {
        $file = $this->fakeUpload('sample.csv', "a,b,c\n1,2,3\n");
        $stored = UploadService::store($file, 'resources');
        $this->assertStringStartsWith('resources/', $stored['path']);
        $abs = UploadService::absolutePath($stored['path']);
        $this->assertNotNull($abs);
        $this->assertStringContainsString('/storage/uploads/', $abs);
        $this->assertStringNotContainsString('/public/', $abs);
        @unlink($abs);
    }

    public function test_disallowed_extension_is_rejected(): void
    {
        $this->expectException(\DomainException::class);
        UploadService::store($this->fakeUpload('evil.php', '<?php echo "x";'), 'resources');
    }

    public function test_mismatched_mime_is_rejected(): void
    {
        // .png extension but PHP content -> finfo detects text, not image/png.
        $this->expectException(\DomainException::class);
        UploadService::store($this->fakeUpload('fake.png', '<?php echo 1;'), 'resources');
    }

    public function test_oversized_file_is_rejected(): void
    {
        $file = $this->fakeUpload('big.csv', 'x');
        $file['size'] = (int) config('security.upload_max_bytes') + 1;
        $this->expectException(\DomainException::class);
        UploadService::store($file, 'resources');
    }

    public function test_path_traversal_is_not_resolvable(): void
    {
        $this->assertNull(UploadService::absolutePath('../../etc/passwd'));
        $this->assertNull(UploadService::absolutePath('resources/../../../etc/passwd'));
    }
}
