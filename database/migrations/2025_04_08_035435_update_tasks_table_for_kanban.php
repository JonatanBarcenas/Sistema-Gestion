<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop order_id if it exists
            if (Schema::hasColumn('tasks', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }

            // Add new columns if they don't exist
            if (!Schema::hasColumn('tasks', 'type')) {
                $table->string('type')->after('priority')->default('design');
            }
            if (!Schema::hasColumn('tasks', 'estimated_hours')) {
                $table->decimal('estimated_hours', 8, 2)->nullable()->after('type');
            }
            if (!Schema::hasColumn('tasks', 'actual_hours')) {
                $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
            }
            if (!Schema::hasColumn('tasks', 'materials_needed')) {
                $table->text('materials_needed')->nullable()->after('actual_hours');
            }
            if (!Schema::hasColumn('tasks', 'notes')) {
                $table->text('notes')->nullable()->after('materials_needed');
            }
            if (!Schema::hasColumn('tasks', 'attachments')) {
                $table->json('attachments')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('tasks', 'checklist')) {
                $table->json('checklist')->nullable()->after('attachments');
            }
            if (!Schema::hasColumn('tasks', 'color')) {
                $table->string('color')->nullable()->after('checklist');
            }
            if (!Schema::hasColumn('tasks', 'position')) {
                $table->integer('position')->default(0)->after('color');
            }

            // Modify existing columns
            if (Schema::hasColumn('tasks', 'status')) {
                $table->string('status')->default('pending')->change();
            }
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->default('medium')->change();
            }
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dateTime('due_date')->nullable()->change();
            }
            if (Schema::hasColumn('tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop new columns if they exist
            $columns = [
                'type',
                'estimated_hours',
                'actual_hours',
                'materials_needed',
                'notes',
                'attachments',
                'checklist',
                'color',
                'position'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Restore original columns
            if (!Schema::hasColumn('tasks', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('id');
            }
            if (Schema::hasColumn('tasks', 'status')) {
                $table->string('status')->change();
            }
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->change();
            }
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dateTime('due_date')->change();
            }
            if (Schema::hasColumn('tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->change();
            }
        });
    }
};
