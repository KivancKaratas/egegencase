<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    // İlgili model
    protected $model = News::class;

    /**
     * Haber sahte verilerini oluşturur.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),  // 3 kelimelik başlık
            'content' => $this->faker->sentence(20),  // 20 kelimelik içerik
            'image' => null, // Varsayılan olarak resim null olabilir
        ];
    }
    
}
