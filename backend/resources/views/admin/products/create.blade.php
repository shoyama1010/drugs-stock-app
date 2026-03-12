@extends('admin.layout.app')

@section('content')

<h1>商品登録</h1>

<form method="POST" action="/admin/products">

@csrf

商品コード
<input type="text" name="code">

商品名
<input type="text" name="name">

SKU
<input type="text" name="sku">

カテゴリ
<select name="category_id">

@foreach($categories as $category)

<option value="{{$category->id}}">
{{$category->name}}
</option>

@endforeach

</select>

価格
<input type="number" name="unit_price">

最低在庫
<input type="number" name="min_stock">

<button type="submit">登録</button>

</form>

@endsection
