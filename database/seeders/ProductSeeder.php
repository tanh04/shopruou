<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;  
use App\Models\Category; 
use App\Models\Brand;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mảng dữ liệu cho các sản phẩm
        $products = [
            // Sản phẩm 1
            [
                'product_name' => 'Speedo Fastskin LZR Racer X',
                'product_description' => 'Đồ bơi thi đấu cao cấp cho nam, thiết kế tiên tiến giúp cải thiện tốc độ.',
                'product_price' => 300000,
                'product_stock' => 10,
                'category_id' => Category::where('category_name', 'Đồ bơi nam')->first()->category_id,  
                'brand_id' => Brand::where('brand_name', 'Speedo')->first()->brand_id,  
                'product_image' => 'Speedo_Fastskin_LZR_Racer_X.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 2
            [
                'product_name' => 'Arena Powerskin Carbon Flex',
                'product_description' => 'Đồ bơi thi đấu cho nam, mang lại sự thoải mái và linh hoạt tối ưu.',
                'product_price' => 280000,
                'product_stock' => 15,
                'category_id' => Category::where('category_name', 'Đồ bơi nam')->first()->category_id,  
                'brand_id' => Brand::where('brand_name', 'Arena')->first()->brand_id,  
                'product_image' => 'Arena_Powerskin_Carbon_Flex.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 3
            [
                'product_name' => 'TYR Durafast Elite Swimsuit',
                'product_description' => 'Bộ đồ bơi cao cấp dành cho vận động viên bơi lội chuyên nghiệp, thiết kế giúp tối ưu hóa hiệu suất.',
                'product_price' => 350000,
                'product_stock' => 20,
                'category_id' => Category::where('category_name', 'Đồ bơi nữ')->first()->category_id,  
                'brand_id' => Brand::where('brand_name', 'TYR')->first()->brand_id,  
                'product_image' => 'TYR_Durafast_Elite_Swimsuit.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 4
            [
                'product_name' => 'Nike Swim Aqua Zone',
                'product_description' => 'Đồ bơi dành cho nữ, thiết kế năng động và chất liệu chống nước tốt.',
                'product_price' => 220000,
                'product_stock' => 12,
                'category_id' => Category::where('category_name', 'Đồ bơi nữ')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'Nike Swim')->first()->brand_id,
                'product_image' => 'Nike_Swim_Aqua_Zone.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 5
            [
                'product_name' => 'Adidas Swim AdiPower',
                'product_description' => 'Đồ bơi nữ với chất liệu chống nước, thiết kế hiện đại phù hợp cho vận động viên bơi lội.',
                'product_price' => 250000,
                'product_stock' => 30,
                'category_id' => Category::where('category_name', 'Đồ bơi nữ')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'Adidas Swim')->first()->brand_id,
                'product_image' => 'Adidas_Swim_AdiPower.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 6
            [
                'product_name' => 'Speedo Endurance',
                'product_description' => 'Quần bơi nam chất liệu chống nước, bền bỉ cho các vận động viên.',
                'product_price' => 150000,
                'product_stock' => 25,
                'category_id' => Category::where('category_name', 'Đồ bơi nam')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'Speedo')->first()->brand_id,
                'product_image' => 'Speedo_Endurance.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 7
            [
                'product_name' => 'Arena Classic Swimsuit',
                'product_description' => 'Bộ đồ bơi nữ cổ điển, thiết kế đơn giản và tiện dụng cho mọi người.',
                'product_price' => 200000,
                'product_stock' => 18,
                'category_id' => Category::where('category_name', 'Đồ bơi nữ')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'Arena')->first()->brand_id,
                'product_image' => 'Arena_Classic_Swimsuit.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 8
            [
                'product_name' => 'TYR Tracer Light',
                'product_description' => 'Đồ bơi thi đấu nhẹ, giúp tối ưu tốc độ cho các vận động viên.',
                'product_price' => 350000,
                'product_stock' => 10,
                'category_id' => Category::where('category_name', 'Đồ bơi nam')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'TYR')->first()->brand_id,
                'product_image' => 'TYR_Tracer_Light.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 9
            [
                'product_name' => 'Nike Swim Vapor',
                'product_description' => 'Đồ bơi nam có thiết kế thời trang và công nghệ vải cao cấp.',
                'product_price' => 280000,
                'product_stock' => 20,
                'category_id' => Category::where('category_name', 'Đồ bơi nam')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'Nike Swim')->first()->brand_id,
                'product_image' => 'Nike_Swim_Vapor.jpg',
                'product_status' => 1
            ],
            // Sản phẩm 10
            [
                'product_name' => 'Adidas Swim Performance Suit',
                'product_description' => 'Bộ đồ bơi cho nữ, tối ưu cho các vận động viên với chất liệu vải chống nước tốt.',
                'product_price' => 270000,
                'product_stock' => 22,
                'category_id' => Category::where('category_name', 'Đồ bơi nữ')->first()->category_id,
                'brand_id' => Brand::where('brand_name', 'Adidas Swim')->first()->brand_id,
                'product_image' => 'Adidas_Swim_Performance_Suit.jpg',
                'product_status' => 1
            ],
        ];

        // Thêm tất cả sản phẩm vào cơ sở dữ liệu
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
