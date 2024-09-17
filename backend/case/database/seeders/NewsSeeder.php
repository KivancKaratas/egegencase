<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Haber verilerini veritabanına ekler.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512M'); // Bellek sınırını 512 MB'ye çıkarın
        
        $batchSize = 10000; // Her partide oluşturulacak haber sayısı
        $totalRecords = 250000; // Toplam haber sayısı

        for ($i = 0; $i < $totalRecords / $batchSize; $i++) {
            News::factory($batchSize)->create();
        }
    }
}
