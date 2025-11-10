<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function all_banners(Request $request)
    {
        $q = Banner::query()
            ->when($request->keyword, fn($x)=>$x->where('title','like','%'.$request->keyword.'%'))
            ->when($request->position, fn($x)=>$x->where('position',$request->position))
            ->orderBy('position')
            ->orderBy('sort_order');

        $banners = $q->paginate(7)->appends($request->query());

        // truyền luôn POSITIONS xuống view
        $positions = Banner::POSITIONS;

        return view('admin.manage_banners.all_banners', compact('banners','positions'));
    }



    public function create_banner()
    {
        $positions = ['home_top','home_mid','sidebar_right'];
        return view('admin.manage_banners.create_banner', compact('positions'));
    }

    public function save_banner(Request $request)
    {
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'link_url'   => 'nullable|url|max:500',
            'position'   => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
            'status'     => 'required|in:0,1',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // upload ảnh
        $name = null;
        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $name = Str::slug(pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME))
                    .'_'.time().'.'.$img->getClientOriginalExtension();
            $img->move(public_path('uploads/banners'), $name);
        }

        Banner::create([
            'title'      => $request->title,
            'image_path' => $name,
            'link_url'   => $request->link_url,
            'position'   => $request->position,
            'sort_order' => $request->sort_order,
            'status'     => $request->status,
            'starts_at'  => $request->starts_at,
            'ends_at'    => $request->ends_at,
        ]);

        return redirect('create-banner')->with('message', 'Thêm banner thành công!');
    }

    public function edit_banner($id)
    {
        $banner = Banner::findOrFail($id);
        $positions = ['home_top','home_mid','sidebar_right'];
        return view('admin.manage_banners.edit_banner', compact('banner','positions'));
    }

    public function update_banner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title'      => 'nullable|string|max:255',
            'link_url'   => 'nullable|url|max:500',
            'position'   => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
            'status'     => 'required|in:0,1',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $name = Str::slug(pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME))
                    .'_'.time().'.'.$img->getClientOriginalExtension();
            $img->move(public_path('uploads/banners'), $name);

            // xóa file cũ
            if ($banner->image_path && file_exists(public_path('uploads/banners/'.$banner->image_path))) {
                @unlink(public_path('uploads/banners/'.$banner->image_path));
            }
            $banner->image_path = $name;
        }

        $banner->title      = $request->title;
        $banner->link_url   = $request->link_url;
        $banner->position   = $request->position;
        $banner->sort_order = $request->sort_order;
        $banner->status     = $request->status;
        $banner->starts_at  = $request->starts_at;
        $banner->ends_at    = $request->ends_at;
        $banner->save();

        return redirect('edit-banners/' . $banner->id)->with('message', 'Cập nhật banner thành công!');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->image_path && file_exists(public_path('uploads/banners/'.$banner->image_path))) {
            @unlink(public_path('uploads/banners/'.$banner->image_path));
        }
        $banner->delete();

        return redirect('all-banners')->with('message', 'Đã xóa banner!');
    }

    public function toggle($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = $banner->status ? 0 : 1;
        $banner->save();
        return back()->with('message', 'Đã đổi trạng thái banner!');
    }

    public function somePage() {
    return view('partials.some_page', [
        'hideSlider' => true
    ]);
}
}
