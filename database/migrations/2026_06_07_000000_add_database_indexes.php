<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds critical indexes for performance optimization.
     * Optimized for SQLite (FK constraints do NOT auto-create indexes).
     *
     * Strategy:
     * - Single-column FK indexes are ONLY added when no composite index
     *   covers them as a left-prefix.
     * - Composite indexes are preferred for common multi-column query patterns.
     * - Unique index on offering_token for critical token lookup queries.
     */
    public function up(): void
    {
        // ================================================================
        // CANDIDATES TABLE - MOST CRITICAL (High query volume)
        // ================================================================
        Schema::table('candidates', function (Blueprint $table) {
            // FK index (no composite covers this column as prefix)
            $table->index('current_stage_id');

            // High-frequency filter columns
            $table->index('status');           // 37+ WHERE clauses on status

            // Composite indexes for common query patterns
            // Note: vacancy_id and user_id single indexes are omitted because
            // they are left-prefixes of the composites below.
            $table->index(['vacancy_id', 'current_stage_id']);  // Pipeline filtering
            $table->index(['user_id', 'status']);                // Applicant candidates by status
            $table->index(['status', 'created_at']);             // Listing sort by date with status filter

            // Unique index for offering token lookup (OfferingResponse.php, ExpireOfferings.php)
            $table->unique('offering_token');

            // Email index for application history lookup
            $table->index('email');
        });

        // ================================================================
        // CANDIDATE_MOVEMENTS TABLE - Audit trail queries
        // ================================================================
        Schema::table('candidate_movements', function (Blueprint $table) {
            // FK indexes (no composite covers these as prefix)
            $table->index('from_stage_id');
            $table->index('to_stage_id');

            // Timeline queries (AtsPipeline history)
            $table->index('moved_at');

            // Composite for efficient history retrieval
            // Note: candidate_id single index omitted (left-prefix of this composite)
            $table->index(['candidate_id', 'moved_at']);
        });

        // ================================================================
        // INTERVIEW_SCHEDULES TABLE - Scheduling & calendar operations
        // ================================================================
        Schema::table('interview_schedules', function (Blueprint $table) {
            // FK index (no composite covers this as prefix)
            $table->index('stage_id');      // Stage-specific schedule queries

            // Date-based queries (conflict detection, calendar views)
            $table->index('date');

            // Composite for efficient schedule lookup
            // Note: candidate_id single index omitted (left-prefix of this composite)
            $table->index(['candidate_id', 'date']);
        });

        // ================================================================
        // SCORECARDS TABLE - Performance evaluation tracking
        // ================================================================
        Schema::table('scorecards', function (Blueprint $table) {
            // FK index (no composite covers this as prefix)
            $table->index('stage_id');      // Stage scoring queries

            // Composite for efficient scorecard retrieval
            // Note: candidate_id single index omitted (left-prefix of this composite)
            $table->index(['candidate_id', 'stage_id']);
        });

        // ================================================================
        // RRS TABLE - HR internal queries
        // ================================================================
        Schema::table('rrs', function (Blueprint $table) {
            // Title search index
            $table->index('title');

            // Status filtering (Draft, Ready to Publish, Published, Closed)
            $table->index('status');

            // Composite for MPP-based RR queries
            // Note: mpp_id single index omitted (left-prefix of this composite)
            $table->index(['mpp_id', 'status']);
        });

        // ================================================================
        // VACANCIES TABLE - Public job listings
        // ================================================================
        Schema::table('vacancies', function (Blueprint $table) {
            // Title search index
            $table->index('title');

            // Composite for public careers page: status + application_deadline
            // Replaces single index('status') — status is covered as prefix
            $table->index(['status', 'application_deadline']);

            // Composite for RR-based job queries
            // Note: rr_id single index omitted (left-prefix of this composite)
            $table->index(['rr_id', 'status']);
        });

        // ================================================================
        // MPPS TABLE - Manpower planning queries
        // ================================================================
        Schema::table('mpps', function (Blueprint $table) {
            // Status filtering (Draft, Approved, Completed, Closed)
            $table->index('status');
        });

        // ================================================================
        // STAGES TABLE - Recruitment stage management
        // ================================================================
        Schema::table('stages', function (Blueprint $table) {
            // Ordering queries (AtsPipeline, stage selection)
            $table->index('sequence');
        });

        // ================================================================
        // NOTIFICATIONS TABLE - User notifications & activity feed
        // ================================================================
        Schema::table('notifications', function (Blueprint $table) {
            // Composite for pagination with status filtering
            // Note: user_id and is_read single indexes omitted:
            // - user_id is left-prefix of this composite
            // - is_read has very low selectivity (only true/false)
            $table->index(['user_id', 'is_read', 'created_at']);
        });

        // ================================================================
        // BLACKLIST TABLE - Duplicate prevention & blocking
        // ================================================================
        Schema::table('blacklist', function (Blueprint $table) {
            // Email-based duplicate detection
            $table->index('email');

            // Phone-based duplicate detection
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex(['current_stage_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['vacancy_id', 'current_stage_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropUnique(['offering_token']);
            $table->dropIndex(['email']);
        });

        Schema::table('candidate_movements', function (Blueprint $table) {
            $table->dropIndex(['from_stage_id']);
            $table->dropIndex(['to_stage_id']);
            $table->dropIndex(['moved_at']);
            $table->dropIndex(['candidate_id', 'moved_at']);
        });

        Schema::table('interview_schedules', function (Blueprint $table) {
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['candidate_id', 'date']);
        });

        Schema::table('scorecards', function (Blueprint $table) {
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['candidate_id', 'stage_id']);
        });

        Schema::table('rrs', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['status']);
            $table->dropIndex(['mpp_id', 'status']);
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['status', 'application_deadline']);
            $table->dropIndex(['rr_id', 'status']);
        });

        Schema::table('mpps', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->dropIndex(['sequence']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read', 'created_at']);
        });

        Schema::table('blacklist', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['phone']);
        });
    }
};
