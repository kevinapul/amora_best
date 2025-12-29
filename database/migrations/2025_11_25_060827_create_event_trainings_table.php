    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up()
        {
            Schema::create('event_trainings', function (Blueprint $table) {
                $table->id();

                $table->foreignId('training_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->enum('jenis_event', ['reguler', 'inhouse'])
                    ->default('reguler');

                $table->integer('harga_paket')->nullable();

                $table->string('job_number')->unique();

                $table->date('tanggal_start');
                $table->date('tanggal_end');

                $table->string('tempat');

                $table->string('jenis_sertifikasi')->nullable();
                $table->string('sertifikasi')->nullable();

                // 🔑 FLOW CORE
                $table->enum('status', [
                    'pending',
                    'active',
                    'on_progress',
                    'done'
                ])->default('pending');

                // 🔐 KEUANGAN
                $table->boolean('finance_approved')->default(false);
                $table->timestamp('finance_approved_at')->nullable();

                $table->timestamps();
            });
        }

        public function down()
        {
            Schema::dropIfExists('event_trainings');
        }
    };
