<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSystemNameToFileUploadsTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('file_uploads', function (Blueprint $table) {
      $table->string('system_name')->nullable()->after('folder');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('file_uploads', function (Blueprint $table) {
      $table->dropColumn('system_name');
    });
  }
}
