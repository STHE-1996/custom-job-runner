<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Creating the 'jobs' table
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index(); // Queue name (indexed for fast searching)
            $table->longText('payload'); // Payload for job data
            $table->unsignedTinyInteger('attempts')->default(0); // Number of attempts made
            $table->unsignedInteger('reserved_at')->nullable(); // When the job was reserved
            $table->unsignedInteger('available_at'); // When the job is available to be processed
            $table->string('status')->default('pending'); // Job status: default is 'pending'
            $table->timestamps(0); // Auto manage created_at and updated_at (0 for no fractional seconds)
        });

        // Creating the 'job_batches' table
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // Unique identifier for the job batch
            $table->string('name'); // Batch name
            $table->integer('total_jobs'); // Total number of jobs in the batch
            $table->integer('pending_jobs'); // Number of jobs still pending in the batch
            $table->integer('failed_jobs'); // Number of failed jobs in the batch
            $table->longText('failed_job_ids'); // List of failed job IDs
            $table->mediumText('options')->nullable(); // Additional options for the batch
            $table->integer('cancelled_at')->nullable(); // Timestamp when the batch was cancelled
            $table->integer('created_at'); // Timestamp of when the batch was created
            $table->integer('finished_at')->nullable(); // Timestamp when the batch finished
        });

        // Creating the 'failed_jobs' table
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('uuid')->unique(); // Unique identifier for each failed job
            $table->text('connection'); // Connection name the job was processed on
            $table->text('queue'); // Queue name the job was on
            $table->longText('payload'); // Payload data for the failed job
            $table->longText('exception'); // The exception message or stack trace
            $table->timestamp('failed_at')->useCurrent(); // Timestamp when the job failed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Drop the created tables if this migration is rolled back
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
