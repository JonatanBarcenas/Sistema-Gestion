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
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            
            $table->foreignId('project_id')->after('id')->constrained()->onDelete('cascade');
            $table->enum('type', ['design', 'printing', 'finishing', 'packaging', 'delivery', 'other'])->after('status')->default('other');
            $table->decimal('estimated_hours', 5, 2)->nullable()->after('type');
            $table->decimal('actual_hours', 5, 2)->nullable()->after('estimated_hours');
            $table->json('materials_needed')->nullable()->after('actual_hours');
            $table->text('notes')->nullable()->after('materials_needed');
            $table->json('attachments')->nullable()->after('notes');
            $table->json('checklist')->nullable()->after('attachments');
            $table->string('color')->nullable()->after('checklist');
            $table->integer('position')->default(0)->after('color');
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('dependency_id')->constrained('tasks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_comments');
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'project_id',
                'type',
                'estimated_hours',
                'actual_hours',
                'materials_needed',
                'notes',
                'attachments',
                'checklist',
                'color',
                'position'
            ]);
            
            $table->foreignId('order_id')->after('id')->constrained()->onDelete('cascade');
        });
    }
};
