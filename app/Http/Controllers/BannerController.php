<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;
class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banner=Banner::orderBy('id','DESC')->paginate(10);
        return view('backend.banner.index')->with('banners',$banner);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.banner.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title'=>'string|required|max:50',
            'description'=>'string|nullable',
            'photo'=>'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'=>'required|in:active,inactive',
        ]);
		$data=$request->all();
		$filePath = '';
		if ($request->hasFile('photo')) {
			$file = $request->file('photo');

			$folderPath = public_path('image/banner');
			
			if (!file_exists($folderPath)) {
				mkdir($folderPath, 0777, true);
			}

			$fileName = time() . '_' . $file->getClientOriginalName();

			$file->move($folderPath, $fileName);
			
			$filePath = 'image/banner/' . $fileName;
		}
        $slug=Str::slug($request->title);
        $count=Banner::where('slug',$slug)->count();
        if($count>0){
            $slug=$slug.'-'.date('ymdis').'-'.rand(0,999);
        }
		
        $data['slug']=$slug;
        $data['photo']=$filePath;
        $status=Banner::create($data);
        if($status){
            request()->session()->flash('success','Banner successfully added');
        }
        else{
            request()->session()->flash('error','Error occurred while adding banner');
        }
        return redirect()->route('banner.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $banner=Banner::findOrFail($id);
        return view('backend.banner.edit')->with('banner',$banner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $banner=Banner::findOrFail($id);
        $this->validate($request,[
            'title'=>'string|required|max:50',
            'description'=>'string|nullable',
			'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'=>'required|in:active,inactive',
        ]);
        $data=$request->all();
		$filePath = $banner->photo;
		if ($request->hasFile('photo')) {
			if ($banner->photo && file_exists(public_path($banner->photo))) {
				unlink(public_path($banner->photo));
			}

			$file = $request->file('photo');
			$folderPath = public_path('image/banner');

			if (!file_exists($folderPath)) {
				mkdir($folderPath, 0777, true);
			}

			$fileName = time() . '_' . $file->getClientOriginalName();
			$file->move($folderPath, $fileName);
			$filePath = 'image/banner/' . $fileName;
		}
		$data['photo']=$filePath;
        $status=$banner->fill($data)->save();
        if($status){
            request()->session()->flash('success','Banner successfully updated');
        }
        else{
            request()->session()->flash('error','Error occurred while updating banner');
        }
        return redirect()->route('banner.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $banner=Banner::findOrFail($id);
        $status=$banner->delete();
        if($status){
            request()->session()->flash('success','Banner successfully deleted');
        }
        else{
            request()->session()->flash('error','Error occurred while deleting banner');
        }
        return redirect()->route('banner.index');
    }
}
