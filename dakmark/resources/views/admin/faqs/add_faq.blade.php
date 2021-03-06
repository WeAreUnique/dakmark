
@extends('layouts.admin')
@section('title', 'FAQs')
@section('description', 'FAQs')
@section('content')
<header class="header">
    <a href="{{ route('admin.faq') }}" class="btn btn-primary btn-sm pull-right" title="Danh sách FAQ">Quay lại danh sách</a>
</header>
<div class="container-fluid">
    <div class="block-header">
        <h3>Thêm FAQ</h3>
    </div>
    </br>
    <div class="row clearfix" >
        <section class="panel panel-default">
            {!! Form::open(array('route' => 'admin.faq.insert')) !!}
                @if(count($errors))
                <div class="alert alert-danger">
                    <strong>Chú ý !</strong> Đã xảy ra một số lỗi !
                </div>
                @endif
                <table class="table table-responsive">
                    <tr>
                        <td>
                            Câu hỏi - Tiếng Việt
                            <span class="text-danger">*</span>
                        </td>
                        <td>
                            <textarea class="form-control" name="question">{!! old('question') !!}</textarea>
                            <span class="text-danger">{{ $errors->first('question') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Câu hỏi - Tiếng Anh
                        </td>
                        <td>
                            <textarea class="form-control" name="en_question">{!! old('en_question') !!}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Câu trả lời - Tiếng Việt
                            <span class="text-danger">*</span>
                        </td>
                        <td>
                            <textarea class="form-control" name="answer">{!! old('answer') !!}</textarea>
                            <span class="text-danger">{{ $errors->first('answer') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Câu trả lời - Tiếng Anh
                        </td>
                        <td>
                            <textarea class="form-control" name="en_answer">{!! old('en_answer') !!}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Sắp xếp</td>
                        <td>
                            <input type="text" class="form-control" name="sort_order" value="{!! old('sort_order') !!}" />
                        </td>
                    </tr>
                    <tr>
                        <td>Cho phép hiển thị</td>
                        <td>
                            <select name="is_show" class="form-control">
                                <option value="0">Không</option>
                                <option value="1" selected >Có</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button type="submit" class="btn btn-info" style="margin-left: 15px; margin-top: 10px">Thêm</button></td>
                    </tr>
                </table>
            </form>
        </section>
    </div>
</div>

@endsection


