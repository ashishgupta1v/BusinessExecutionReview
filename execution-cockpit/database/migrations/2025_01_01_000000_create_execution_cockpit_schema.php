<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Execution Cockpit — full toolkit schema (single-DB, workspace_id-scoped).
 * Run AFTER the Breeze `users` migration and the spatie permission migration.
 * PostgreSQL 16 target (jsonb columns).
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- tenancy core ---
        Schema::create('workspaces', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('business_type')->nullable();
            $t->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $t->string('plan')->default('free');            // free | pro | team
            $t->timestamp('trial_ends_at')->nullable();
            $t->jsonb('settings')->nullable();              // dcr_reminder_time, weekly_review_day, stale_task_days
            $t->timestamps();
        });

        Schema::create('memberships', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('role')->default('member');          // owner | admin | member | partner
            $t->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('joined_at')->nullable();
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id']);
        });

        // users gains a current workspace pointer + timezone
        Schema::table('users', function (Blueprint $t) {
            $t->string('timezone')->default('Asia/Kolkata');
            $t->foreignId('current_workspace_id')->nullable()->constrained('workspaces')->nullOnDelete();
        });

        // --- DCR ---
        Schema::create('dcr_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->date('entry_date');
            $t->text('reflection_note')->nullable();
            $t->boolean('moved_needle')->default(false);
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id', 'entry_date']);
        });

        Schema::create('dcr_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dcr_entry_id')->constrained()->cascadeOnDelete();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->string('type');                             // completed | pending | priority
            $t->string('title');
            $t->boolean('is_measurable')->default(false);
            $t->date('new_deadline')->nullable();
            $t->foreignId('kanban_card_id')->nullable();
            $t->integer('position')->default(0);
            $t->timestamps();
        });

        // --- Weekly / Monthly reviews ---
        Schema::create('weekly_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->integer('iso_year');
            $t->integer('iso_week');
            $t->jsonb('planned_goals')->nullable();
            $t->jsonb('achieved')->nullable();
            $t->text('major_wins')->nullable();
            $t->text('challenges')->nullable();
            $t->text('moved_needle_answer')->nullable();
            $t->jsonb('next_week_focus')->nullable();       // top 3
            $t->string('status')->default('draft');         // draft | complete
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id', 'iso_year', 'iso_week']);
        });

        Schema::create('monthly_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->integer('year');
            $t->integer('month');
            $t->text('summary')->nullable();
            $t->text('wins')->nullable();
            $t->text('challenges')->nullable();
            $t->jsonb('kpi_snapshot')->nullable();
            $t->jsonb('actions')->nullable();
            $t->string('status')->default('draft');
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id', 'year', 'month']);
        });

        // --- KPIs ---
        Schema::create('kpis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('unit')->nullable();
            $t->string('direction')->default('higher_better');
            $t->decimal('target_default', 14, 2)->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('kpi_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kpi_id')->constrained()->cascadeOnDelete();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->integer('iso_year');
            $t->integer('iso_week');
            $t->decimal('target', 14, 2)->nullable();
            $t->decimal('actual', 14, 2)->nullable();
            $t->decimal('variance', 14, 2)->nullable();
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->unique(['kpi_id', 'iso_year', 'iso_week']);
        });

        // --- Feedback ---
        Schema::create('feedback_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->date('feedback_date');
            $t->string('type');                             // positive | negative | suggestion
            $t->text('body');
            $t->text('action_taken')->nullable();
            $t->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('assignee_name')->nullable();         // free-text owner when not a linked user
            $t->string('status')->default('pending');       // pending | done
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });

        // --- Kanban ---
        Schema::create('kanban_boards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->string('name')->default('Execution Board');
            $t->timestamps();
        });

        Schema::create('kanban_columns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->string('name');                             // To Do | In Progress | Done
            $t->integer('position')->default(0);
            $t->integer('wip_limit')->nullable();
            $t->timestamps();
        });

        Schema::create('kanban_cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('column_id')->constrained('kanban_columns')->cascadeOnDelete();
            $t->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->integer('position')->default(0);            // gapped for cheap reordering
            $t->timestamp('entered_column_at')->nullable(); // stale detection
            $t->date('due_date')->nullable();
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
        });

        // link dcr_tasks -> kanban_cards now that the table exists
        Schema::table('dcr_tasks', function (Blueprint $t) {
            $t->foreign('kanban_card_id')->references('id')->on('kanban_cards')->nullOnDelete();
        });

        // --- Discipline / streaks ---
        Schema::create('discipline_checklists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->date('check_date');
            $t->unsignedTinyInteger('rule_of_5_count')->default(0);
            $t->boolean('two_minute_done')->default(false);
            $t->boolean('time_blocked')->default(false);
            $t->jsonb('items')->nullable();
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id', 'check_date']);
        });

        Schema::create('streaks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('kind');                             // dcr | discipline
            $t->integer('current_len')->default(0);
            $t->integer('longest_len')->default(0);
            $t->date('last_completed_date')->nullable();
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id', 'kind']);
        });

        // --- Cadence engine ---
        Schema::create('review_periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('period_type');                      // day | week | month | quarter
            $t->date('period_start');
            $t->date('period_end');
            $t->string('status')->default('open');          // open | completed | overdue
            $t->timestamp('completed_at')->nullable();
            $t->nullableMorphs('reference');                // dcr_entry | weekly_review | monthly_review
            $t->timestamps();
            $t->unique(['workspace_id', 'user_id', 'period_type', 'period_start']);
        });

        // --- Accountability (v1.1) ---
        Schema::create('accountability_partnerships', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('partner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('partner_email')->nullable();
            $t->string('status')->default('invited');       // invited | active
            $t->boolean('share_weekly')->default(true);
            $t->string('token')->nullable();                // hashed, for public snapshot
            $t->timestamps();
        });

        // --- Notification prefs ---
        Schema::create('notification_preferences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $t->time('dcr_reminder_time')->default('18:00');
            $t->unsignedTinyInteger('weekly_reminder_dow')->default(5); // Friday
            $t->time('weekly_reminder_time')->default('18:00');
            $t->unsignedTinyInteger('monthly_reminder_dom')->default(1);
            $t->time('monthly_reminder_time')->default('10:00');
            $t->jsonb('channels')->nullable();              // ['webpush','mail']
            $t->boolean('enabled')->default(true);
            $t->timestamps();
            $t->unique(['user_id', 'workspace_id']);
        });
    }

    public function down(): void
    {
        Schema::table('dcr_tasks', fn (Blueprint $t) => $t->dropForeign(['kanban_card_id']));
        foreach ([
            'notification_preferences', 'accountability_partnerships', 'review_periods',
            'streaks', 'discipline_checklists', 'kanban_cards', 'kanban_columns',
            'kanban_boards', 'feedback_logs', 'kpi_entries', 'kpis', 'monthly_reviews',
            'weekly_reviews', 'dcr_tasks', 'dcr_entries',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('current_workspace_id');
            $t->dropColumn('timezone');
        });
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('workspaces');
    }
};
