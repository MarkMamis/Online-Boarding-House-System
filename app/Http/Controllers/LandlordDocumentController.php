<?php

namespace App\Http\Controllers;

use App\Models\LandlordDocument;
use App\Models\User;
use App\Services\DocumentExpirationService;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LandlordDocumentController extends Controller
{
    public function __construct(
        protected FileStorageService $files,
        protected DocumentExpirationService $expiration,
    ) {
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('landlordProfile');

        $types = LandlordDocument::types();
        $records = $user->landlordDocuments()
            ->current()
            ->orderByDesc('id')
            ->get()
            ->keyBy('document_type');

        $documents = [];

        foreach ($types as $type => $label) {
            $record = $records->get($type);
            $legacyPath = null;
            $legacyStatus = null;

            // Fall back to legacy profile columns when no centralized record exists yet.
            if (!$record) {
                if ($type === LandlordDocument::TYPE_BUSINESS_PERMIT) {
                    $legacyPath = optional($user->landlordProfile)->business_permit_path;
                    $legacyStatus = optional($user->landlordProfile)->business_permit_status;
                } elseif ($type === LandlordDocument::TYPE_SAFETY_CERTIFICATE) {
                    $legacyPath = optional($user->landlordProfile)->safety_certificate_path;
                    $legacyStatus = optional($user->landlordProfile)->safety_certificate_status;
                }
            }

            $documents[$type] = [
                'type' => $type,
                'label' => $label,
                'record' => $record,
                'legacy_path' => $legacyPath,
                'legacy_status' => $legacyStatus,
                'has_file' => $record
                    ? filled($record->file_path)
                    : filled($legacyPath),
                'verification_status' => $record
                    ? $record->verification_status
                    : (filled($legacyStatus) ? $legacyStatus : 'not_submitted'),
                'expiration' => $record && $record->expiration_date
                    ? $this->expiration->expirationInfo($record->expiration_date)
                    : null,
            ];
        }

        return view('landlord.documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $this->validateSubmission($request, requireFile: true);

        // A landlord may only have one current record per document type. Older
        // versions remain available to admins for audit/history purposes.
        $existing = LandlordDocument::where('landlord_id', $user->id)
            ->where('document_type', $data['document_type'])
            ->current()
            ->latest('id')
            ->first();

        if ($existing) {
            // Use the record's own type so the path always matches the row.
            $directory = $this->documentDirectory($user->id, $existing->document_type);
            $newPath = $this->files->upload($request->file('file'), $directory);
            $submittedAt = Carbon::now();

            DB::transaction(function () use ($existing, $data, $newPath, $submittedAt) {
                $existing->update([
                    'is_current' => false,
                    'superseded_at' => $submittedAt,
                ]);

                LandlordDocument::create([
                    'landlord_id' => $existing->landlord_id,
                    'document_type' => $existing->document_type,
                    'document_number' => $data['document_number'],
                    'file_path' => $newPath,
                    'date_issued' => $data['date_issued'],
                    'expiration_date' => $data['expiration_date'],
                    'verification_status' => LandlordDocument::STATUS_PENDING,
                    'submitted_at' => $submittedAt,
                    'is_current' => true,
                ]);
            });

            return back()->with('success', 'Your ' . strtolower(LandlordDocument::typeLabel($data['document_type'])) . ' was replaced and submitted for review again. The previous version was retained in document history.');
        }

        $directory = $this->documentDirectory($user->id, $data['document_type']);
        $path = $this->files->upload($request->file('file'), $directory);

        LandlordDocument::create([
            'landlord_id' => $user->id,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'file_path' => $path,
            'date_issued' => $data['date_issued'],
            'expiration_date' => $data['expiration_date'],
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'submitted_at' => Carbon::now(),
            'is_current' => true,
        ]);

        return back()->with('success', 'Document submitted for verification.');
    }

    /**
     * Replace / resubmit an existing document (rejected or renewing an approved one).
     */
    public function resubmit(Request $request, LandlordDocument $document)
    {
        /** @var User $user */
        $user = Auth::user();

        if ((int) $document->landlord_id !== (int) $user->id) {
            abort(404);
        }

        if (!$document->is_current) {
            abort(404);
        }

        $data = $this->validateSubmission($request, requireFile: true);
        $directory = $this->documentDirectory($user->id, $document->document_type);

        $newPath = $this->files->upload($request->file('file'), $directory);
        $submittedAt = Carbon::now();

        DB::transaction(function () use ($document, $data, $newPath, $submittedAt) {
            $document->update([
                'is_current' => false,
                'superseded_at' => $submittedAt,
            ]);

            LandlordDocument::create([
                'landlord_id' => $document->landlord_id,
                'document_type' => $document->document_type,
                'document_number' => $data['document_number'],
                'file_path' => $newPath,
                'date_issued' => $data['date_issued'],
                'expiration_date' => $data['expiration_date'],
                'verification_status' => LandlordDocument::STATUS_PENDING,
                'submitted_at' => $submittedAt,
                'is_current' => true,
            ]);
        });

        return back()->with('success', 'Document resubmitted for review. The previous version was retained in document history.');
    }

    protected function validateSubmission(Request $request, bool $requireFile): array
    {
        $rules = [
            'document_type' => ['required', 'in:' . implode(',', array_keys(LandlordDocument::types()))],
            'document_number' => ['nullable', 'string', 'max:100'],
            'date_issued' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:date_issued'],
            'file' => [
                $requireFile ? 'required' : 'nullable',
                'file',
                'mimes:' . (string) config('landlord_documents.allowed_mimes', 'pdf,jpg,jpeg,png'),
                'max:' . (int) config('landlord_documents.max_size_kb', 2048),
            ],
        ];

        $messages = [
            'expiration_date.after_or_equal' => 'The expiration date must be on or after the date issued.',
            'file.mimes' => 'The document must be a PDF, JPG, JPEG, or PNG file.',
            'file.max' => 'The document must not be larger than 2 MB.',
        ];

        return Validator::make($request->all(), $rules, $messages)->validate();
    }

    protected function documentDirectory(int $landlordId, string $type): string
    {
        $prefix = (string) config('landlord_documents.storage_prefix', 'landlords/{landlord_id}/documents');
        $prefix = str_replace('{landlord_id}', (string) $landlordId, $prefix);

        return trim($prefix . '/' . LandlordDocument::typeDirectory($type), '/');
    }
}
