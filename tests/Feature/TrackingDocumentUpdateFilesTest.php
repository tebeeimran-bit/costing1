<?php

namespace Tests\Feature;

use App\Models\DocumentProject;
use App\Models\DocumentRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrackingDocumentUpdateFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_files_creates_new_revision_and_increments_version(): void
    {
        Storage::fake();

        $oldPartlistPath = 'tracking-documents/partlist/old-partlist.xlsx';
        $oldUmhPath = 'tracking-documents/umh/old-umh.xlsx';

        Storage::put($oldPartlistPath, 'old partlist content');
        Storage::put($oldUmhPath, 'old umh content');

        $project = DocumentProject::create([
            'customer' => 'AHM',
            'model' => 'K4MA',
            'part_number' => '32100-K4MA-W203',
            'part_name' => 'WIRE HARNESS',
            'project_key' => hash('sha256', 'ahm|k4ma|32100-k4ma-w203|wire harness'),
        ]);

        $revision = DocumentRevision::create([
            'document_project_id' => $project->id,
            'version_number' => 1,
            'received_date' => now()->toDateString(),
            'pic_engineering' => 'Imran',
            'status' => DocumentRevision::STATUS_SUBMITTED_TO_MARKETING,
            'partlist_original_name' => 'old-partlist.xlsx',
            'partlist_file_path' => $oldPartlistPath,
            'umh_original_name' => 'old-umh.xlsx',
            'umh_file_path' => $oldUmhPath,
            'notes' => 'Initial revision',
            'change_remark' => 'Dokumen awal diterima (baseline V0).',
        ]);

        $response = $this->post(route('tracking-documents.update-files', ['revision' => $revision->id]), [
            'partlist_file' => UploadedFile::fake()->create('new-partlist.xlsx', 120),
            'change_remark' => 'Update partlist dari engineering',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('document_revisions', 2);

        $newRevision = DocumentRevision::where('document_project_id', $project->id)
            ->orderByDesc('version_number')
            ->first();

        $this->assertNotNull($newRevision);
        $this->assertSame(2, $newRevision->version_number);
        $this->assertSame(DocumentRevision::STATUS_PENDING_FORM_INPUT, $newRevision->status);
        $this->assertNull($newRevision->pic_marketing);
        $this->assertSame('new-partlist.xlsx', $newRevision->partlist_original_name);
        $this->assertNotSame($oldPartlistPath, $newRevision->partlist_file_path);
        $this->assertSame($oldUmhPath, $newRevision->umh_file_path);
        $this->assertSame('old-umh.xlsx', $newRevision->umh_original_name);
        $this->assertSame('Update partlist dari engineering', $newRevision->change_remark);

        // Existing files are preserved for historical revisions.
        $this->assertTrue(Storage::exists($oldPartlistPath));
        $this->assertTrue(Storage::exists($oldUmhPath));
        $this->assertTrue(Storage::exists($newRevision->partlist_file_path));
    }
}
