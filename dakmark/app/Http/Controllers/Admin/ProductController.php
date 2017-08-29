<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ProductCat;
use App\Models\Product;
use App\Models\Seo;
use App\Models\Navigator;
use DB;

class ProductController extends Controller
{
   	public function productCatList(){
        $productCats = ProductCat::where("parent_id",0)->orderBy('sort_order', 'asc')->get();
        return view('admin.products.product_cat_list')->with(["productCats" => $productCats]);
    }
    public function addProductCat(){
        $productCats = ProductCat::all();
        return view('admin.products.add_product_cat')->with(["productCats" => $productCats]);
    } 
    public function insertProductCat(Request $request){
        $this->validate($request,['name' => 'required',
                                  'slug' => 'required|unique:seo',
                                  'seo_title' => 'required'
                                ]);
        $max_id = $this->get_max_id('product_cat');
        $system_id = 'PCAT'.$max_id;
        $icon = '' ;
        $icon_file = $request->file('icon');
        if($icon_file != NULL){
            $path = './public/assets/img/product_cat/';
            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }   
            $icon = $this->upload_file($request->name, $icon_file, $path);
        }

        // Insert data vào bảng product_cat
        $productCat = new ProductCat;
        $productCat->name = $request->name;
        $productCat->en_name = $request->en_name;
        $productCat->system_id = $system_id;
        $productCat->parent_id = $request->parent_id;
        $productCat->icon = $icon;
        $productCat->sort_order = $request->sort_order;
        $productCat->is_show = $request->is_show;
        $productCat->is_show_nav = $request->is_show_nav;
        $productCat->save();

        // Insert data vào bảng seo
        $seo = new Seo;
        $seo->system_id = $system_id;
        $seo->name = $request->name;
        $seo->slug = $request->slug;
        $seo->seo_title = $request->seo_title;
        $seo->en_seo_title = $request->en_seo_title;
        $seo->keyword = $request->keyword;
        $seo->description = $request->description;
        $seo->type = "PCAT";
        $seo->save();

        // Insert data vào bảng navigator (menu)
        if($request->is_show_nav==1){
            $navigator = new Navigator;
            $navigator->system_id = $system_id;
            $navigator->name = $request->name;
            $navigator->en_name = $request->en_name;
            $navigator->type = 2;   // Menu được tạo gián tiếp (qua danh mục sản phẩm hoặc danh mục bài viết ) : type = 2
            $navigator->save();
        }

        session()->flash('success_message', "Thêm danh mục thành công !");
        return redirect()->route('admin.product-cat');
    }  
    public function editProductCat($cat_id)
    {
        $productCatList = ProductCat::where('parent_id',0)->orderBy('sort_order', 'asc')->get();
        $productCatDetail = ProductCat::where('id',$cat_id)->first();
        $productCatSeo = Seo::where('system_id',$productCatDetail->system_id)->first();
        return view('admin.products.edit_product_cat')->with(["productCatList" => $productCatList, 
                                                              "productCatDetail" => $productCatDetail, 
                                                              "productCatSeo" => $productCatSeo]); 
    }
    public function updateProductCat($cat_id, Request $request)
    {
        $productCat = ProductCat::find($cat_id);
        $this->validate($request,['name' => 'required',
                                  'slug' => 'required',  // thiếu check_exist
                                  'seo_title' => 'required'
                                ]);
        $icon = '' ;
        $icon_file = $request->file('icon');
        if($icon_file != NULL){
            $path = './public/assets/img/product_cat/';
            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }   
            $icon = $this->upload_file($request->name, $icon_file, $path);
        }

        

        // Update data bảng product_cat
        $productCat->name = $request->name;
        $productCat->en_name = $request->en_name;
        $productCat->parent_id = $request->parent_id;
        $productCat->icon = $icon;
        $productCat->sort_order = $request->sort_order;
        $productCat->is_show = $request->is_show;
        $productCat->is_show_nav = $request->is_show_nav;
        $productCat->save();

        // Update data bảng seo
        $seo = Seo::where('system_id',$productCat->system_id)->first();
        $seo->name = $request->name;
        $seo->slug = $request->slug;
        $seo->seo_title = $request->seo_title;
        $seo->en_seo_title = $request->en_seo_title;
        $seo->keyword = $request->keyword;
        $seo->description = $request->description;
        $seo->save();

        if($productCat->is_show_nav != $request->is_show_nav){
            if($request->is_show_nav==1){  // Insert data vào bảng navigator (menu)
                $navigator = new Navigator;
                $navigator->system_id = $productCat->system_id;
                $navigator->name = $request->name;
                $navigator->en_name = $request->en_name;
                $navigator->type = 2;   // Menu được tạo gián tiếp (qua danh mục sản phẩm hoặc danh mục bào viết ) : type = 2
                $navigator->save();
            }
            else{  // Xóa menu
                $navigator = Navigator::where('system_id',$productCat->system_id)->first();
                $navigator->delete();
            }
        }

        session()->flash('success_message', "Cập nhật thành công !");
        return redirect()->route('admin.product-cat'); 
    }
    public function deleteProductCat($cat_id){
        $productCat = ProductCat::find($cat_id);
        if($productCat->icon != ''){
            $icon_file = './public/assets/img/product_cat/'.$productCat->icon;
            if(file_exists($icon_file))
                unlink($icon_file);
        }
        if($productCat->is_show_nav==1){
            $navigator = Navigator::where('system_id',$productCat->system_id)->first();
            $navigator->delete();
        }
        $seo = Seo::where('system_id',$productCat->system_id)->first();
        $seo->delete();
        $productCat->delete();

        session()->flash('success_message', "Xóa danh mục thành công !");
        return redirect()->route('admin.product-cat'); 
    }
    public function productList(){
        $products = Product::orderBy('last_update', 'desc')->get();
        return view('admin.products.product_list')->with(["products" => $products]);
    }
    public function addProduct(){
        $productCats = ProductCat::all();
        return view('admin.products.add_product')->with(["productCats" => $productCats]);
    } 
    public function insertProduct(Request $request){
        $this->validate($request,['name' => 'required',
                                  'slug' => 'required|unique:seo',
                                  'seo_title' => 'required',
                                  // còn thiếu cat_id, thumb, promote_end
                                ]);
        $max_id = $this->get_max_id('products');
        $system_id = 'PRD'.$max_id;
        $thumb = '' ;
        $thumb_file = $request->file('thumb');
        if($thumb_file != NULL){
            $path = './public/assets/img/product/';
            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }   
            $thumb = $this->upload_file($request->name, $thumb_file, $path);
        }

        $default_price = str_replace(",", "", $request->default_price);  
        $promote_price = str_replace(",", "",  $request->promote_price);  

        // Insert data vào bảng product
        $product = new Product;
        $product->system_id = $system_id;
        $product->product_code = $request->product_code;
        $product->name = $request->name;
        $product->en_name = $request->en_name;
        $product->cat_id = $request->cat_id;
        $product->default_price = $default_price;
        $product->promote_price = $promote_price;
        $product->promote_begin = $request->promote_begin;
        $product->promote_end = $request->promote_end;
        $product->is_show = $request->is_show;
        $product->is_hot = $request->is_hot;
        $product->is_new = $request->is_new;
        $product->is_promote = $request->is_promote;
        $product->thumb = $thumb;
        $product->description = $request->description;
        $product->en_description = $request->en_description;
        $product->create_time = date("Y-m-d H:i:s",time());
        $product->save();

        // Insert data vào bảng seo
        $seo = new Seo;
        $seo->system_id = $system_id;
        $seo->name = $request->name;
        $seo->slug = $request->slug;
        $seo->seo_title = $request->seo_title;
        $seo->en_seo_title = $request->en_seo_title;
        $seo->keyword = $request->keyword;
        $seo->description = $request->seo_description;
        $seo->type = "PRODUCT";  
        $seo->save();

        session()->flash('success_message', "Thêm sản phẩm thành công !");
        return redirect()->route('admin.product');
    }  
    public function editProduct($product_id)
    {
        $productCats = ProductCat::all();
        $productDetail = Product::find($product_id);
        $productSeo = Seo::where('system_id',$productDetail->system_id)->first();
        return view('admin.products.edit_product')->with(["productCats" => $productCats, 
                                                          "productDetail" => $productDetail, 
                                                          "productSeo" => $productSeo]); 
    }
    public function updateProduct($product_id, Request $request)
    {
        $product = Product::find($product_id);
        $this->validate($request,['name' => 'required',
                                  'slug' => 'required',  // thiếu check_exist
                                  'seo_title' => 'required'
                                  // còn thiếu cat_id, thumb, promote_end
                                ]);
        $thumb = '' ;
        $thumb_file = $request->file('thumb');
        if($thumb_file != NULL){
            $path = './public/assets/img/product/';
            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }   
            $thumb = $this->upload_file($request->name, $thumb_file, $path);
        }

        $default_price = str_replace(",", "", $request->default_price);  
        $promote_price = str_replace(",", "",  $request->promote_price);  

        // Update data bảng product
        $product->product_code = $request->product_code;
        $product->name = $request->name;
        $product->en_name = $request->en_name;
        $product->cat_id = $request->cat_id;
        $product->default_price = $default_price;
        $product->promote_price = $promote_price;
        $product->promote_begin = $request->promote_begin;
        $product->promote_end = $request->promote_end;
        $product->is_show = $request->is_show;
        $product->is_hot = $request->is_hot;
        $product->is_new = $request->is_new;
        $product->is_promote = $request->is_promote;
        $product->thumb = $thumb != '' ? $thumb : $product->thumb;
        $product->description = $request->description;
        $product->en_description = $request->en_description;
        $product->save();

        // Update data bảng seo
        $seo = Seo::where('system_id',$product->system_id)->first();
        $seo->name = $request->name;
        $seo->slug = $request->slug;
        $seo->seo_title = $request->seo_title;
        $seo->en_seo_title = $request->en_seo_title;
        $seo->keyword = $request->keyword;
        $seo->description = $request->seo_description;
        $seo->save();

        session()->flash('success_message', "Cập nhật thành công !");
        return redirect()->route('admin.product'); 
    }
    public function deleteProduct($product_id){
        $product = Product::find($product_id);
        if($product->thumb != ''){
            $thumb_file = './public/assets/img/product/'.$product->thumb;
            if(file_exists($thumb_file))
                unlink($thumb_file);
        }
        $seo = Seo::where('system_id',$product->system_id)->first();
        $seo->delete();
        $product->delete();

        session()->flash('success_message', "Xóa sản phẩm thành công !");
        return redirect()->route('admin.product'); 
    }

    // Hàm ajax lấy slug theo tên
    public function generate_slug(Request $request){
        $system_id = $request->system_id;
        $system_id = isset($system_id) ? $system_id : '';
        $name = $request->name;
        $slug = url_format($name);
        $i=0;
        $check_slug =  $this->check_slug_exist($slug, $system_id);
        while ($check_slug){
            $i++;
            $slug.= '-'.$i;
            $check_slug =  $this->check_slug_exist($slug, $system_id);
        }

        echo $slug;
    }

    // Kiểm tra slug đã tồn tại hay chưa
    function check_slug_exist($slug, $system_id){
        $slugCount = DB::table('seo')->where([['slug', '=', $slug],['system_id', '<>', $system_id]])->count(); 
        if($slugCount > 0)
            return TRUE;
        return FALSE;
    }

    //Lấy id lớn nhất trong table
    function get_max_id($table){
        $max_id = DB::table($table)->max('id');
        if($max_id != NULL)
            return $max_id + 1;
        else
            return 1;
    }

    // Upload 1 file
    function upload_file($object_name, $file, $path){
        $ext = $file->getClientOriginalExtension();
        $org_name = url_format($object_name);   // Định dạng lại phần tên gốc (remove utf8)
        $file_name = $org_name.".".$ext;
        $i=0;
        $check_file = file_exists($path.$file_name); // Kiểm tra file đã tồn tại trong thư mục hay chưa
        while ($check_file){  // Nếu đã tồn tại thì thêm số đếm vào sau tên file
            $i++;
            $tmp_org_name = $org_name.'-'.$i;
            $file_name = $tmp_org_name.".".$ext;
            $check_file = file_exists($path.$file_name);
        }
      
        if($file->move($path,$file_name))
            return $file_name;
        else
            return '';
    }
}