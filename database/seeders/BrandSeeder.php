<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;  // Import Model Brand

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mảng dữ liệu thương hiệu
        $brands = [
            [
                'brand_name' => 'Speedo',
                'brand_description' => 'Một trong những thương hiệu đồ bơi nổi tiếng nhất trên thế giới, được biết đến với các sản phẩm dành cho vận động viên và người bơi chuyên nghiệp.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Arena',
                'brand_description' => 'Thương hiệu đồ bơi có nguồn gốc từ Ý, Arena chuyên cung cấp các sản phẩm cho các vận động viên bơi lội với chất lượng cao và hiệu suất tốt.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'TYR',
                'brand_description' => 'TYR nổi tiếng với đồ bơi chuyên nghiệp và các phụ kiện bơi lội, bao gồm kính bơi, quần bơi, và đồ bơi cho cả nam và nữ.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Nike Swim',
                'brand_description' => 'Nike Swim là dòng sản phẩm đồ bơi của Nike, nổi bật với thiết kế năng động và công nghệ vải chất lượng cao, phù hợp cho cả bơi tự do và thi đấu.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Adidas Swim',
                'brand_description' => 'Adidas cũng có một dòng sản phẩm đồ bơi với thiết kế hiện đại và chất liệu chống nước tốt, phù hợp cho các vận động viên bơi lội.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Zoggs',
                'brand_description' => 'Zoggs là một thương hiệu nổi tiếng chuyên cung cấp các sản phẩm đồ bơi cho người tiêu dùng bình dân, đặc biệt là các sản phẩm bơi dành cho trẻ em và người mới bắt đầu.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Baleaf',
                'brand_description' => 'Thương hiệu chuyên cung cấp đồ bơi giá rẻ nhưng vẫn đảm bảo chất lượng, với các mẫu mã thời trang và phù hợp với nhiều đối tượng sử dụng.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Rip Curl',
                'brand_description' => 'Rip Curl nổi bật với các bộ đồ bơi dành cho các hoạt động thể thao dưới nước như lướt sóng, bơi lội và các môn thể thao biển khác.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Billabong',
                'brand_description' => 'Cũng chuyên về các đồ bơi và đồ thể thao dưới nước, Billabong được biết đến với các mẫu mã thời trang, năng động và chất lượng.',
                'brand_status' => 1
            ],
            [
                'brand_name' => 'Pentland',
                'brand_description' => 'Pentland là thương hiệu đến từ Anh, nổi tiếng với các sản phẩm đồ bơi và thiết bị thể thao chất lượng cao.',
                'brand_status' => 1
            ]
        ];

        // Sử dụng Eloquent để thêm dữ liệu vào bảng 'brands'
        foreach ($brands as $brand) {
            Brand::create($brand);  // Thêm từng thương hiệu vào bảng 'brands'
        }
    }
}
