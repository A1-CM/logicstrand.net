<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('INSERT INTO document_chunks_fts (rowid, body) SELECT id, body FROM document_chunks');
        DB::statement('CREATE TRIGGER document_chunks_fts_insert AFTER INSERT ON document_chunks BEGIN INSERT INTO document_chunks_fts (rowid, body) VALUES (new.id, new.body); END');
        DB::statement('CREATE TRIGGER document_chunks_fts_delete AFTER DELETE ON document_chunks BEGIN DELETE FROM document_chunks_fts WHERE rowid = old.id; END');
        DB::statement('CREATE TRIGGER document_chunks_fts_update AFTER UPDATE OF body ON document_chunks BEGIN DELETE FROM document_chunks_fts WHERE rowid = old.id; INSERT INTO document_chunks_fts (rowid, body) VALUES (new.id, new.body); END');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS document_chunks_fts_update');
        DB::statement('DROP TRIGGER IF EXISTS document_chunks_fts_delete');
        DB::statement('DROP TRIGGER IF EXISTS document_chunks_fts_insert');
    }
};
