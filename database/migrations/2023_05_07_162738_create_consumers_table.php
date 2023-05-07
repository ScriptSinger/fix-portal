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
        Schema::create('consumers', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->integer('task_id', false, true);
            $table->string('photo')->nullable();
            $table->string('link_pu')->nullable();
            $table->string('rayon_goroda')->nullable();
            $table->string('adres')->nullable();
            $table->string('ulica')->nullable();
            $table->string('dom_korpus1')->nullable();
            $table->string('kvartira_komnata')->nullable();
            $table->string('ls')->nullable();
            $table->string('zavodskoy_nomer_ipu')->nullable();
            $table->string('model_ipu')->nullable();
            $table->string('tip_ipu')->nullable();
            $table->string('data_dopuska_pu_v_komuchet')->nullable();
            $table->string('data_okonchaniya_dopuska_pu_v_komuchet')->nullable();
            $table->string('mesto_ustanovki_pu')->nullable();
            $table->string('nomer_plastikovoy_plomby')->nullable();
            $table->string('nomer_antimagnitnoy_plomby')->nullable();
            $table->string('sostoyanie_pu')->nullable();
            $table->string('debitorskaya_zadoljennost')->nullable();
            $table->string('data_snyatiya_tekuschego_pokazaniya_ispolnitel')->nullable();
            $table->string('kolichestvo_projivauschih_chel_ispolnitel')->nullable();
            $table->string('zavodskoy_nomer_ipu_ispolnitel')->nullable();
            $table->string('marka_model_seriya_ispolnitel')->nullable();
            $table->string('god_vypuska_ispolnitel')->nullable();
            $table->string('tip_ipu_ispolnitel')->nullable();
            $table->string('kontrolnye_pokazaniya_ispolnitel')->nullable();
            $table->string('nomer_plomby_ispolnitel')->nullable();
            $table->string('nomer_amp_ispolnitel')->nullable();
            $table->string('nalichie_dokumentacii_ispolnitel')->nullable();
            $table->string('data_posledney_poverki_ispolnitel')->nullable();
            $table->string('data_okonchaniya_poverki_ispolnitel')->nullable();
            $table->string('tehnicheskoe_sostoyanie_ispolnitel')->nullable();
            $table->string('mesto_ustanovki_ispolnitel')->nullable();
            $table->string('nomer_plomby_ustanovlennye_ispolnitel')->nullable();
            $table->string('nomer_amp_ustanovlennye_ispolnitel')->nullable();
            $table->string('kolichestvo_ipu_v_kvartire_ispolnitel')->nullable();
            $table->string('nalichie_podpisi_potrebitelya')->nullable();
            $table->string('zamechanie_k_priboru_ucheta_ispolnitel')->nullable();
            $table->string('kommentariy_k_aktu_ispolnitel')->nullable();
            $table->string('podrobnoe_vnosimoe_poyasnenie')->nullable();
            $table->string('fio_agenta_ispolnitel')->nullable();
            $table->string('primechanie')->nullable();
            $table->string('delta')->nullable();
            $table->string('brak')->nullable();
            $table->string('primechanie_dlya_tsb')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumers');
    }
};
