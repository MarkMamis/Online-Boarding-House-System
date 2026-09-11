<?php

namespace App\Services\AI\Tools;

use App\Models\User;
use App\Services\LandlordDocumentStatusService;

class DocumentTools
{
    /**
     * Get aggregate compliance-document statistics for all landlords (Admin only).
     */
    public function getLandlordDocumentStatistics(User $user, array $args): array
    {
        $service = app(LandlordDocumentStatusService::class);
        $stats = $service->getAggregateStatistics();

        $stats['action_url'] = '/admin/approvals/landlords?tab=permits';
        $stats['action_label'] = 'Review Landlord Documents';

        return $stats;
    }

    /**
     * Get list of landlords who have missing compliance documents (Admin only).
     */
    public function getLandlordsWithMissingDocuments(User $user, array $args): array
    {
        $service = app(LandlordDocumentStatusService::class);
        $type = !empty($args['document_type']) ? (string) $args['document_type'] : null;
        $limit = isset($args['limit']) ? (int) $args['limit'] : 15;

        $result = $service->getLandlordsWithMissingDocuments($type, $limit);
        $result['action_url'] = '/admin/approvals/landlords?tab=permits';
        $result['action_label'] = 'Review Landlord Documents';

        return $result;
    }

    /**
     * Get list of landlords with submitted compliance documents awaiting admin review (Admin only).
     */
    public function getPendingLandlordDocuments(User $user, array $args): array
    {
        $service = app(LandlordDocumentStatusService::class);
        $type = !empty($args['document_type']) ? (string) $args['document_type'] : null;
        $limit = isset($args['limit']) ? (int) $args['limit'] : 15;

        $result = $service->getLandlordsWithPendingDocuments($type, $limit);
        $result['action_url'] = '/admin/approvals/landlords?tab=permits';
        $result['action_label'] = 'Review Landlord Documents';

        return $result;
    }

    /**
     * Get document verification queue statistics (Admin only).
     */
    public function getDocumentVerificationStatistics(User $user, array $args): array
    {
        $pendingStudentVerifications = User::where('role', 'student')
            ->where('school_id_verification_status', 'pending')
            ->count();

        $approvedStudentVerifications = User::where('role', 'student')
            ->where('school_id_verification_status', 'approved')
            ->count();

        $service = app(LandlordDocumentStatusService::class);
        $landlordStats = $service->getAggregateStatistics();

        return [
            'pending_student_id_verifications' => $pendingStudentVerifications,
            'approved_student_id_verifications' => $approvedStudentVerifications,
            'landlord_documents' => [
                'business_permit' => $landlordStats['business_permit'],
                'safety_certificate' => $landlordStats['safety_certificate'],
                'total_missing_document_instances' => $landlordStats['total_missing_document_instances'],
                'landlords_with_missing_documents' => $landlordStats['landlords_with_missing_documents'],
                'landlords_with_pending_documents' => $landlordStats['landlords_with_pending_documents'],
            ],
            'action_urls' => [
                'student_verifications' => '/admin/student-verifications',
                'landlord_permits' => '/admin/approvals/landlords?tab=permits',
            ],
        ];
    }

    /**
     * Get document status for the authenticated user (Student or Landlord).
     */
    public function getMyDocumentStatus(User $user, array $args): array
    {
        if ($user->role === 'student') {
            $missing = $user->missingStudentSetupFields();
            return [
                'role' => 'student',
                'school_id_verification_status' => $user->school_id_verification_status ?: 'not_uploaded',
                'school_id_verified_at' => $user->school_id_verified_at?->toFormattedDateString(),
                'rejection_reason' => $user->school_id_rejection_reason,
                'setup_complete' => empty($missing),
                'missing_fields' => $missing,
                'action_url' => '/student/setup',
            ];
        }

        if ($user->role === 'landlord') {
            $service = app(LandlordDocumentStatusService::class);
            $summary = $service->getLandlordDocumentSummary($user);

            $profile = $user->landlordProfile;
            return [
                'role' => 'landlord',
                'landlord_id' => $user->id,
                'name' => $user->full_name,
                'business_permit_status' => $summary['business_permit_status'],
                'business_permit_rejection_reason' => $summary['business_permit_rejection_reason'],
                'safety_certificate_status' => $summary['safety_certificate_status'],
                'safety_certificate_rejection_reason' => $summary['safety_certificate_rejection_reason'],
                'missing_documents' => $summary['missing_documents'],
                'pending_documents' => $summary['pending_documents'],
                'is_fully_compliant' => $summary['is_fully_compliant'],
                'profile_completed' => (bool) ($profile?->profile_completed ?? false),
                'billing_completed' => (bool) ($profile?->billing_completed ?? false),
                'action_url' => '/landlord/setup',
            ];
        }

        return ['message' => 'Admin accounts do not require personal document verifications.'];
    }
}
