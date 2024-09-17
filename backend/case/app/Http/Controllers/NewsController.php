<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    /**
     * Haber ekleme işlemi
     */
    public function store(Request $request)
    {
        // Validasyon kuralları ve hata mesajları
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:webp|max:2048',
        ], [
            'title.required' => 'Başlık alanı zorunludur.',
            'title.string' => 'Başlık metinsel olmalıdır.',
            'title.max' => 'Başlık en fazla 255 karakter olabilir.',
            'content.required' => 'İçerik alanı zorunludur.',
            'content.string' => 'İçerik metinsel olmalıdır.',
            'image.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'image.mimes' => 'Görsel yalnızca webp formatında olmalıdır.',
            'image.max' => 'Görsel boyutu en fazla 2MB olabilir.',
        ]);

        // Validasyon hatası varsa, hataları JSON olarak döndür
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Yeni haber oluştur
        $news = new News();
        $news->title = $request->input('title');
        $news->content = $request->input('content');

        // Eğer bir resim yüklendiyse, resmi işle
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->getRealPath();
            $imageName = time() . '.webp';

            // Resmi boyutlandır ve kaydet
            $this->resizeImage($imagePath, public_path('images/' . $imageName));

            // Resim adını veritabanında sakla
            $news->image = $imageName;
        }

        // Haberi veritabanına kaydet
        $news->save();

        // Başarılı yanıtı döndür
        return response()->json(['message' => 'Haber başarıyla kaydedildi!', 'news' => $news], 201);
    }

    /**
     * Haber güncelleme işlemi
     */
    public function update(Request $request, $id)
    {
        // Validasyon kuralları ve hata mesajları
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:webp|max:2048',
        ], [
            'title.required' => 'Başlık alanı zorunludur.',
            'title.string' => 'Başlık metinsel olmalıdır.',
            'title.max' => 'Başlık en fazla 255 karakter olabilir.',
            'content.required' => 'İçerik alanı zorunludur.',
            'content.string' => 'İçerik metinsel olmalıdır.',
            'image.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'image.mimes' => 'Görsel yalnızca webp formatında olmalıdır.',
            'image.max' => 'Görsel boyutu en fazla 2MB olabilir.',
        ]);

        // Validasyon hatası varsa, hataları JSON olarak döndür
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Haberi bul ve güncelle
        $news = News::findOrFail($id);
        $news->title = $request->input('title');
        $news->content = $request->input('content');

        // Eğer yeni bir resim yüklendiyse, resmi güncelle
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->getRealPath();
            $imageName = time() . '.webp';

            // Resmi boyutlandır ve kaydet
            $this->resizeImage($imagePath, public_path('images/' . $imageName));

            // Resim yolunu veritabanında güncelle
            $news->image = $imageName;
        }

        // Güncellenen haberi kaydet
        $news->save();

        // Başarılı yanıtı döndür
        return response()->json(['message' => 'Haber başarıyla güncellendi!', 'news' => $news], 200);
    }

    /**
     * Haber silme işlemi
     */
    public function destroy($id)
    {
        // Haberi bul ve sil
        $news = News::findOrFail($id);
        $news->delete();

        // Başarılı silme yanıtı döndür
        return response()->json(['message' => 'Haber başarıyla silindi!'], 200);
    }

    /**
     * Haber arama işlemi
     */
    public function search(Request $request)
    {
        ini_set('memory_limit', '1024M'); // Bellek sınırını geçici olarak 1024MB yap

        // Gelen sorgu kelimesini al
        $query = $request->input('query');

        // Başlık veya içerikte arama yap
        $news = News::where('title', 'LIKE', "%{$query}%")
        ->orWhere('content', 'LIKE', "%{$query}%")
        ->paginate(50); // 50 sonuç birden getir

        // Arama sonuçlarını JSON olarak döndür
        return response()->json($news);
    }

    /**
     * Resmi yeniden boyutlandırma işlemi
     * 
     * @param string $sourcePath Kaynak resim yolu
     * @param string $destinationPath Hedef resim yolu
     */
    private function resizeImage($sourcePath, $destinationPath)
    {
        // Resmin orijinal genişlik ve yüksekliğini al
        list($width, $height) = getimagesize($sourcePath);

        // Maksimum boyutlar
        $newWidth = 800;
        $newHeight = 800;

        // Oranlı boyutlandırma
        if ($width > $height) {
            $newHeight = ($newWidth / $width) * $height;
        } else {
            $newWidth = ($newHeight / $height) * $width;
        }

        // Yeni boş bir resim oluştur
        $imageResized = imagecreatetruecolor($newWidth, $newHeight);

        // Kaynaktan resmi al (webp formatında)
        $image = imagecreatefromwebp($sourcePath);

        // Resmi yeniden boyutlandır ve kopyala
        imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Yeniden boyutlandırılan resmi kaydet
        imagewebp($imageResized, $destinationPath, 80);

        // Belleği serbest bırak
        imagedestroy($imageResized);
        imagedestroy($image);
    }
}
