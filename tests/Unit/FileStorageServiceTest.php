<?php

namespace Tests\Unit;

use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class FileStorageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use complete dummy disk configuration so the service exercises the
        // R2 branch without making any network calls in automated tests.
        config([
            'filesystems.disks.r2.key' => 'test-r2-key',
            'filesystems.disks.r2.secret' => 'test-r2-secret',
            'filesystems.disks.r2.bucket' => 'test-r2-bucket',
            'filesystems.disks.r2.endpoint' => 'https://r2.test.invalid',
            'filesystems.disks.supabase.key' => 'test-supabase-key',
            'filesystems.disks.supabase.endpoint' => 'https://supabase.test.invalid',
        ]);

        Storage::fake('r2');
        Storage::fake('supabase');
        Storage::fake('public');
    }

    public function test_r2_is_preferred_for_new_uploads_when_fully_configured(): void
    {
        $service = new FileStorageService();

        $this->assertTrue($service->isR2Configured());
        $this->assertSame('r2', $service->disk());

        $path = $service->upload(
            UploadedFile::fake()->create('safety-certificate.pdf', 20, 'application/pdf'),
            'landlords/25/documents/safety-certificates',
        );

        $this->assertStringStartsWith('landlords/25/documents/safety-certificates/', $path);
        $this->assertStringNotContainsString('://', $path);
        Storage::disk('r2')->assertExists($path);
        Storage::disk('supabase')->assertMissing($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_supabase_remains_the_write_fallback_when_r2_is_not_configured(): void
    {
        config([
            'filesystems.disks.r2.key' => '',
            'filesystems.disks.r2.secret' => '',
            'filesystems.disks.r2.bucket' => '',
            'filesystems.disks.r2.endpoint' => '',
        ]);

        $service = new FileStorageService();

        $this->assertFalse($service->isR2Configured());
        $this->assertSame('supabase', $service->disk());

        $path = $service->upload(
            UploadedFile::fake()->create('legacy-compatible.pdf', 20, 'application/pdf'),
            'landlords/25/documents/business-permits',
        );

        Storage::disk('supabase')->assertExists($path);
        Storage::disk('r2')->assertMissing($path);
    }

    public function test_read_order_is_r2_then_supabase_then_public(): void
    {
        $path = 'landlords/25/documents/business-permits/shared.pdf';

        Storage::disk('r2')->put($path, 'r2 contents');
        Storage::disk('supabase')->put($path, 'supabase contents');
        Storage::disk('public')->put($path, 'public contents');

        $service = new FileStorageService();

        $this->assertSame('r2', $service->holdingDisk($path));
        $this->assertSame('r2 contents', $service->get($path));
    }

    public function test_supabase_is_used_when_r2_does_not_have_the_legacy_key(): void
    {
        $path = 'landlords/25/documents/business-permits/legacy-supabase.pdf';
        Storage::disk('supabase')->put($path, 'supabase contents');

        $service = new FileStorageService();

        $this->assertTrue($service->exists($path));
        $this->assertSame('supabase', $service->holdingDisk($path));
        $this->assertSame('supabase contents', $service->get($path));
    }

    public function test_public_is_the_final_read_fallback_for_old_local_files(): void
    {
        $path = 'landlords/25/documents/safety-certificates/legacy-local.pdf';
        Storage::disk('public')->put($path, 'public contents');

        $service = new FileStorageService();

        $this->assertTrue($service->exists($path));
        $this->assertSame('public', $service->holdingDisk($path));
        $this->assertSame('public contents', $service->get($path));
    }

    public function test_delete_only_removes_the_first_disk_that_holds_a_key(): void
    {
        $path = 'landlords/25/documents/business-permits/duplicated.pdf';
        Storage::disk('r2')->put($path, 'new copy');
        Storage::disk('supabase')->put($path, 'legacy copy');

        $this->assertTrue((new FileStorageService())->delete($path));
        Storage::disk('r2')->assertMissing($path);
        Storage::disk('supabase')->assertExists($path);
    }

    public function test_failed_replacement_does_not_delete_the_old_file(): void
    {
        $oldPath = 'landlords/25/documents/business-permits/old.pdf';
        Storage::disk('r2')->put($oldPath, 'old contents');

        $service = new class extends FileStorageService
        {
            public function upload(UploadedFile $file, string $directory): string
            {
                throw new RuntimeException('simulated R2 write failure');
            }
        };

        try {
            $service->replace(
                $oldPath,
                UploadedFile::fake()->create('new.pdf', 20, 'application/pdf'),
                'landlords/25/documents/business-permits',
            );
            $this->fail('The replacement should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('simulated R2 write failure', $exception->getMessage());
        }

        Storage::disk('r2')->assertExists($oldPath);
        $this->assertSame('old contents', Storage::disk('r2')->get($oldPath));
    }

    public function test_configured_r2_write_failure_is_not_silently_redirected_to_another_disk(): void
    {
        $service = new FileStorageService();

        Storage::shouldReceive('disk')
            ->once()
            ->with('r2')
            ->andThrow(new RuntimeException('simulated R2 failure'));
        Storage::shouldReceive('disk')->never()->with('supabase');
        Storage::shouldReceive('disk')->never()->with('public');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File upload failed. Please try again.');

        $service->upload(
            UploadedFile::fake()->create('permit.pdf', 20, 'application/pdf'),
            'landlords/25/documents/business-permits',
        );
    }

    public function test_generated_paths_and_urls_do_not_contain_storage_credentials(): void
    {
        $path = 'landlords/25/documents/business-permits/private.pdf';
        Storage::disk('r2')->put($path, 'private contents');

        $url = (string) (new FileStorageService())->url($path);

        $this->assertStringNotContainsString('test-r2-key', $url);
        $this->assertStringNotContainsString('test-r2-secret', $url);
        $this->assertStringNotContainsString('test-supabase-key', $url);
    }
}
