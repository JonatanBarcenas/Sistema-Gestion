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
        // Crear tabla pivote para la relación muchos a muchos entre tareas y usuarios
        Schema::create('task_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('assignee'); // Puede ser 'assignee', 'reviewer', etc.
            $table->timestamps();

            // Asegurar que no haya duplicados de tarea-usuario
            $table->unique(['task_id', 'user_id']);
        });

        // Modificar la tabla de tareas
        Schema::table('tasks', function (Blueprint $table) {
            // Eliminar la columna assigned_to si existe
            if (Schema::hasColumn('tasks', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }

            // Agregar campos adicionales para el flujo de trabajo
            if (!Schema::hasColumn('tasks', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('id')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('tasks', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('tasks', 'completed_at')) {
                $table->dateTime('completed_at')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('tasks', 'progress')) {
                $table->integer('progress')->default(0)->after('completed_at');
            }
            if (!Schema::hasColumn('tasks', 'blocked_by')) {
                $table->json('blocked_by')->nullable()->after('progress');
            }
            if (!Schema::hasColumn('tasks', 'dependencies')) {
                $table->json('dependencies')->nullable()->after('blocked_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la tabla pivote
        Schema::dropIfExists('task_users');

        // Revertir cambios en la tabla de tareas
        Schema::table('tasks', function (Blueprint $table) {
            // Restaurar la columna assigned_to
            $table->foreignId('assigned_to')->nullable()->after('id');

            // Eliminar las columnas adicionales
            $columns = [
                'order_id',
                'start_date',
                'completed_at',
                'progress',
                'blocked_by',
                'dependencies'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
