<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_documents', function (Blueprint $table): void {
            $table->string('processing_stage')->default('queued')->after('status');
            $table->timestamp('processing_started_at')->nullable()->after('processing_stage');
            $table->timestamp('stage_updated_at')->nullable()->after('processing_started_at');
        });
        DB::table('knowledge_documents')->where('status', 'ready')->update(['processing_stage' => 'ready']);
        DB::table('knowledge_documents')->where('status', 'failed')->update(['processing_stage' => 'failed']);
        DB::table('knowledge_documents')->where('status', 'processing')->update([
            'processing_stage' => 'extracting',
            'processing_started_at' => DB::raw('created_at'),
            'stage_updated_at' => DB::raw('updated_at'),
        ]);

        Schema::table('answers', function (Blueprint $table): void {
            $table->boolean('is_favorite')->default(false);
            $table->text('private_note')->nullable();
            $table->timestamp('viewed_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('onboarding_dismissed_at')->nullable();
        });
        DB::table('users')->whereNull('onboarding_dismissed_at')->update(['onboarding_dismissed_at' => now()]);

        Schema::table('plan_accesses', function (Blueprint $table): void {
            $table->timestamp('period_started_at')->nullable();
        });
        DB::table('plan_accesses')->whereNotNull('trial_started_at')->update(['period_started_at' => DB::raw('trial_started_at')]);

        Schema::create('daily_answer_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->unsignedInteger('answer_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'usage_date']);
        });

        DB::table('answers')->selectRaw('user_id, DATE(created_at) as usage_date, COUNT(*) as answer_count')
            ->groupBy('user_id', DB::raw('DATE(created_at)'))
            ->orderBy('user_id')->orderBy('usage_date')->chunk(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('daily_answer_usages')->insert([
                        'user_id' => $row->user_id,
                        'usage_date' => $row->usage_date,
                        'answer_count' => $row->answer_count,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_answer_usages');
        Schema::table('plan_accesses', fn (Blueprint $table) => $table->dropColumn('period_started_at'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('onboarding_dismissed_at'));
        Schema::table('answers', fn (Blueprint $table) => $table->dropColumn(['is_favorite', 'private_note', 'viewed_at']));
        Schema::table('knowledge_documents', fn (Blueprint $table) => $table->dropColumn(['processing_stage', 'processing_started_at', 'stage_updated_at']));
    }
};
