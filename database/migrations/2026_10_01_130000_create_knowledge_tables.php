<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('status')->default('processing');
            $table->string('error')->nullable();
            $table->timestamps();
        });

        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page')->nullable();
            $table->unsignedInteger('position');
            $table->text('body');
            $table->timestamps();
        });

        DB::statement("CREATE VIRTUAL TABLE document_chunks_fts USING fts5(body, tokenize='porter unicode61')");

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->longText('answer');
            $table->timestamps();
        });

        Schema::create('answer_citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_chunk_id')->constrained()->cascadeOnDelete();
            $table->unique(['answer_id', 'document_chunk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_citations');
        Schema::dropIfExists('answers');
        DB::statement('DROP TABLE IF EXISTS document_chunks_fts');
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('knowledge_documents');
    }
};
