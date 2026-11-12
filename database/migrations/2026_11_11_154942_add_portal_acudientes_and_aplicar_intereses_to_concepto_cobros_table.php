
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concepto_cobros', function (Blueprint $table) {
            $table->boolean('portal_acudientes')
                ->default(false)
                ->after('obligatorio');

            $table->boolean('aplicar_intereses')
                ->default(false)
                ->after('portal_acudientes');
        });
    }

    public function down(): void
    {
        Schema::table('concepto_cobros', function (Blueprint $table) {
            $table->dropColumn([
                'portal_acudientes',
                'aplicar_intereses',
            ]);
        });
    }
};
