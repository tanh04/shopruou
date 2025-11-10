<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;  // Import Model Category

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mảng dữ liệu thể loại
        $categories = [
            [
                'category_name' => 'Đồ bơi nam',
                'category_description' => 'Các sản phẩm đồ bơi dành cho nam giới, bao gồm quần bơi, áo bơi và các phụ kiện khác.',
                'category_status' => 1
            ],
            [
                'category_name' => 'Đồ bơi nữ',
                'category_description' => 'Sản phẩm đồ bơi dành cho nữ giới, bao gồm bikini, áo bơi, và các phụ kiện.',
                'category_status' => 1
            ],
            [
                'category_name' => 'Đồ bơi trẻ em',
                'category_description' => 'Đồ bơi dành cho trẻ em, với các sản phẩm an toàn và thoải mái.',
                'category_status' => 1
            ],
            [
                'category_name' => 'Phụ kiện bơi lội',
                'category_description' => 'Các phụ kiện bơi lội như kính bơi, mũ bơi, và bao tay bơi.',
                'category_status' => 1
            ],
            [
                'category_name' => 'Đồ bơi chuyên nghiệp',
                'category_description' => 'Sản phẩm đồ bơi cao cấp dành cho các vận động viên bơi lội chuyên nghiệp.',
                'category_status' => 1
            ]
        ];

        // Sử dụng Eloquent để thêm dữ liệu vào bảng 'categories'
        foreach ($categories as $category) {
            Category::create($category);  // Thêm từng thể loại vào bảng 'categories'
        }
    }
}
